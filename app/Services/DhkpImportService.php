<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\TaxObjectComponent;
use App\Models\Taxpayer;
use App\Models\Village;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

class DhkpImportService
{
    /**
     * Import SPPT / DHKP from Excel (.xlsx) or CSV file.
     *
     * @return array{total_rows: int, imported: int, skipped: int, errors: array<string>}
     */
    public function import(string $filePath, ?int $defaultTaxYear = null): array
    {
        $defaultTaxYear = $defaultTaxYear ?? Village::current()->active_tax_year;

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'xlsx') {
            $reader = new XlsxReader();
        } else {
            $reader = new CsvReader();
        }

        $reader->open($filePath);

        $headerMap = [];
        $totalRows = 0;
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cells = $row->toArray();

                // Detect and normalize header row (Row 1)
                if (empty($headerMap)) {
                    foreach ($cells as $colIndex => $val) {
                        $norm = strtolower(trim((string)$val));
                        $norm = str_replace([' ', '_', '-', '.', '/'], '', $norm);
                        $headerMap[$norm] = $colIndex;
                    }
                    continue;
                }

                $totalRows++;

                try {
                    // Extract values using normalized column headers
                    $rawNop = $this->getValue($cells, $headerMap, ['nop', 'nomorobjekpajak', 'nomorop']);
                    if (empty($rawNop)) {
                        $skipped++;
                        continue;
                    }

                    $cleanNop = preg_replace('/[^0-9]/', '', (string)$rawNop);
                    if (strlen($cleanNop) < 10) {
                        $errors[] = "Baris #{$rowIndex}: Format NOP '{$rawNop}' tidak valid.";
                        $skipped++;
                        continue;
                    }

                    $namaWp = trim((string)$this->getValue($cells, $headerMap, ['namawp', 'nama', 'namawajibpajak', 'wajibpajak']));
                    if (empty($namaWp)) {
                        $namaWp = 'Wajib Pajak (Tanpa Nama)';
                    }

                    $alamatWp = trim((string)$this->getValue($cells, $headerMap, ['alamatwp', 'alamat']));
                    $alamatOp = trim((string)$this->getValue($cells, $headerMap, ['alamatop', 'lokasiop', 'letakop', 'alamatobjek']));
                    if (empty($alamatOp)) {
                        $alamatOp = $alamatWp;
                    }

                    $rt = trim((string)$this->getValue($cells, $headerMap, ['rt']));
                    $rw = trim((string)$this->getValue($cells, $headerMap, ['rw']));
                    $dusun = trim((string)$this->getValue($cells, $headerMap, ['dusun', 'kampung', 'wilayah']));

                    $luasBumi = (float)$this->cleanNumber($this->getValue($cells, $headerMap, ['luasbumi', 'luastanah', 'bumi']));
                    $luasBangunan = (float)$this->cleanNumber($this->getValue($cells, $headerMap, ['luasbangunan', 'bangunan']));

                    $rawPbb = $this->getValue($cells, $headerMap, ['pbbterutang', 'tagihan', 'pbb', 'nominal']);
                    $pbbTerutang = (float)$this->cleanNumber($rawPbb);

                    $rawYear = $this->getValue($cells, $headerMap, ['tahun', 'tahunpajak']);
                    $taxYear = ! empty($rawYear) ? (int)$rawYear : $defaultTaxYear;

                    DB::transaction(function () use (
                        $cleanNop,
                        $namaWp,
                        $alamatWp,
                        $alamatOp,
                        $rt,
                        $rw,
                        $dusun,
                        $luasBumi,
                        $luasBangunan,
                        $pbbTerutang,
                        $taxYear,
                        $rowIndex
                    ) {
                        // 1. Taxpayer
                        $taxpayer = Taxpayer::firstOrCreate(
                            ['name' => $namaWp],
                            [
                                'address' => $alamatWp,
                                'rt' => $rt,
                                'rw' => $rw,
                                'dusun' => $dusun,
                                'village_name' => Village::current()->name,
                            ]
                        );

                        // 2. Tax Object (NOP)
                        $taxObject = TaxObject::updateOrCreate(
                            ['nop' => $cleanNop],
                            [
                                'taxpayer_id' => $taxpayer->id,
                                'address' => $alamatOp,
                                'rt' => $rt,
                                'rw' => $rw,
                                'dusun' => $dusun,
                                'status' => 'normal',
                            ]
                        );

                        // 3. Components
                        if ($luasBumi > 0) {
                            TaxObjectComponent::updateOrCreate(
                                ['tax_object_id' => $taxObject->id, 'type' => 'bumi', 'tax_year' => $taxYear],
                                ['area_m2' => $luasBumi]
                            );
                        }

                        if ($luasBangunan > 0) {
                            TaxObjectComponent::updateOrCreate(
                                ['tax_object_id' => $taxObject->id, 'type' => 'bangunan', 'tax_year' => $taxYear],
                                ['area_m2' => $luasBangunan]
                            );
                        }

                        // 4. SPPT (Official assessed bill from Bapenda)
                        Sppt::updateOrCreate(
                            [
                                'tax_object_id' => $taxObject->id,
                                'tax_year' => $taxYear,
                            ],
                            [
                                'pbb_terutang' => $pbbTerutang,
                                'total_bill' => $pbbTerutang,
                                'dhkp_row_ref' => "Row #{$rowIndex}",
                            ]
                        );
                    });

                    $imported++;
                } catch (\Throwable $e) {
                    $errors[] = "Baris #{$rowIndex}: " . $e->getMessage();
                    $skipped++;
                }
            }

            break; // Process primary sheet
        }

        $reader->close();

        AuditLog::log('create', "Import DHKP SPPT: Berhasil {$imported}, Dilewati {$skipped} dari total {$totalRows} baris.");

        return [
            'total_rows' => $totalRows,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 10), // return first 10 errors if any
        ];
    }

    private function getValue(array $cells, array $headerMap, array $candidates): mixed
    {
        foreach ($candidates as $cand) {
            if (isset($headerMap[$cand]) && isset($cells[$headerMap[$cand]])) {
                return $cells[$headerMap[$cand]];
            }
        }
        return null;
    }

    private function cleanNumber(mixed $val): float
    {
        if (is_numeric($val)) {
            return (float)$val;
        }

        $str = preg_replace('/[^0-9]/', '', (string)$val);
        return (float)($str ?: 0);
    }
}
