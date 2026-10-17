<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Dusun;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\TaxObjectComponent;
use App\Models\Taxpayer;
use App\Models\Village;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class DhkpImportService
{
    /**
     * Standard column headers for DHKP / SPPT import and template generation.
     *
     * @return array<string>
     */
    public static function getTemplateHeaders(): array
    {
        return [
            'NOP',
            'NAMA_WP',
            'NIK_WP',
            'ALAMAT_WP',
            'ALAMAT_OP',
            'RT',
            'RW',
            'DUSUN',
            'LUAS_BUMI',
            'LUAS_BANGUNAN',
            'LUAS_BUMI_BERSAMA',
            'LUAS_BANGUNAN_BERSAMA',
            'PBB_TERUTANG',
            'NO_URUT_DHKP',
            'TAHUN_PAJAK',
        ];
    }

    /**
     * Sample rows based on Sukahaji schema for the template.
     *
     * @return array<int, array<int, mixed>>
     */
    public static function getSampleRows(?int $taxYear = null): array
    {
        $year = $taxYear ?? (Village::current()?->active_tax_year ?? (int)date('Y'));

        return [
            [
                '321114000100100010',
                'H. AHMAD SUBAGJA',
                '3211140105820001',
                'Dusun Babakan RT 01 RW 01',
                'Jl. Desa Sukahaji Blok Babakan No. 12',
                '01',
                '01',
                'Dusun I Babakan',
                350,
                90,
                0,
                0,
                135000,
                '0001',
                $year,
            ],
            [
                '321114000100200020',
                'HJ. SITI MARYAMAH',
                '3211144508860002',
                'Dusun Sukajaya RT 02 RW 02',
                'Blok Karang Tengah RT 02 RW 02',
                '02',
                '02',
                'Dusun II Sukajaya',
                520,
                0,
                0,
                0,
                95000,
                '0002',
                $year,
            ],
            [
                '321114000100300030',
                'DEDI KURNIAWAN, S.PD',
                '3211141212900003',
                'Dusun Mekarsari RT 03 RW 03',
                'Kompleks Griya Sukahaji Asri Blok B-5',
                '03',
                '03',
                'Dusun III Mekarsari',
                120,
                54,
                25,
                15,
                180000,
                '0003',
                $year,
            ],
        ];
    }

    /**
     * Generate an Excel (.xlsx) or CSV (.csv) template file and return its temporary path.
     */
    public function generateTemplate(string $format = 'xlsx', ?int $taxYear = null): string
    {
        $format = strtolower($format) === 'csv' ? 'csv' : 'xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'dhkp_tpl_') . '.' . $format;

        $headers = self::getTemplateHeaders();
        $rows = self::getSampleRows($taxYear);

        if ($format === 'xlsx') {
            $writer = new XlsxWriter();
            $headerStyle = (new Style())
                ->setFontBold()
                ->setBackgroundColor('059669') // Emerald 600
                ->setFontColor('FFFFFF');
        } else {
            $writer = new CsvWriter();
            $headerStyle = null;
        }

        $writer->openToFile($tempFile);

        if ($headerStyle) {
            $writer->addRow(Row::fromValues($headers, $headerStyle));
        } else {
            $writer->addRow(Row::fromValues($headers));
        }

        foreach ($rows as $rowData) {
            $writer->addRow(Row::fromValues($rowData));
        }

        $writer->close();

        return $tempFile;
    }

    /**
     * Import SPPT / DHKP from Excel (.xlsx) or CSV file.
     *
     * @return array{total_rows: int, imported: int, skipped: int, errors: array<string>}
     */
    public function import(string $filePath, ?int $defaultTaxYear = null): array
    {
        $defaultTaxYear = $defaultTaxYear ?? Village::current()?->active_tax_year ?? (int)date('Y');
        $allDusuns = Dusun::all();

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
                        $norm = str_replace([' ', '_', '-', '.', '/', '\\'], '', $norm);
                        $headerMap[$norm] = $colIndex;
                    }
                    continue;
                }

                $totalRows++;

                try {
                    // 1. NOP
                    $rawNop = $this->getValue($cells, $headerMap, ['nop', 'nomorobjekpajak', 'nomorop', 'nopbb', 'objekpajak']);
                    if (empty($rawNop)) {
                        $skipped++;
                        continue;
                    }

                    $cleanNop = $this->cleanNop($rawNop);
                    if (strlen($cleanNop) < 10) {
                        $errors[] = "Baris #{$rowIndex}: Format NOP '{$rawNop}' tidak valid (minimal 10 digit).";
                        $skipped++;
                        continue;
                    }

                    // 2. Taxpayer Data
                    $namaWp = trim((string)$this->getValue($cells, $headerMap, ['namawp', 'nama', 'namawajibpajak', 'wajibpajak', 'wp']));
                    if (empty($namaWp)) {
                        $namaWp = 'Wajib Pajak (Tanpa Nama)';
                    }

                    $rawNik = $this->getValue($cells, $headerMap, ['nikwp', 'nik', 'noktp', 'ktp', 'nikwajibpajak']);
                    $cleanNik = ! empty($rawNik) ? preg_replace('/[^0-9]/', '', (string)$rawNik) : null;
                    if ($cleanNik && strlen($cleanNik) < 10) {
                        $cleanNik = null;
                    }

                    $alamatWp = trim((string)$this->getValue($cells, $headerMap, ['alamatwp', 'alamatwajibpajak', 'alamatktp', 'domisiliwp']));
                    $alamatOp = trim((string)$this->getValue($cells, $headerMap, ['alamatop', 'lokasiop', 'letakop', 'alamatobjek', 'alamatobjekpajak', 'alamat', 'lokasi']));
                    if (empty($alamatOp)) {
                        $alamatOp = ! empty($alamatWp) ? $alamatWp : 'Desa Sukahaji';
                    }

                    $rt = trim((string)$this->getValue($cells, $headerMap, ['rt', 'rtop', 'rtwp']));
                    $rw = trim((string)$this->getValue($cells, $headerMap, ['rw', 'rwop', 'rwwp']));
                    $dusunRaw = trim((string)$this->getValue($cells, $headerMap, ['dusun', 'namadusun', 'kampung', 'wilayah', 'blok']));

                    // 3. Components Area (m2)
                    $luasBumi = (float)$this->cleanNumber($this->getValue($cells, $headerMap, ['luasbumi', 'luastanah', 'bumi', 'tanah', 'luasbumim2']));
                    $luasBangunan = (float)$this->cleanNumber($this->getValue($cells, $headerMap, ['luasbangunan', 'bangunan', 'luasbng', 'bng', 'luasbangunanm2']));
                    $luasBumiBersama = (float)$this->cleanNumber($this->getValue($cells, $headerMap, ['luasbumibersama', 'bumibersama', 'tanahbersama', 'luasbumibersamam2']));
                    $luasBangunanBersama = (float)$this->cleanNumber($this->getValue($cells, $headerMap, ['luasbangunanbersama', 'bangunanbersama', 'bngbersama', 'luasbangunanbersamam2']));

                    // 4. Financial & Reference
                    $rawPbb = $this->getValue($cells, $headerMap, ['pbbterutang', 'tagihan', 'pbb', 'nominal', 'ketetapan', 'pokokpbb', 'jumlahpbb', 'pbbpokok']);
                    $pbbTerutang = (float)$this->cleanNumber($rawPbb);

                    $rawNoUrut = $this->getValue($cells, $headerMap, ['nourutdhkp', 'nourut', 'dhkprowref', 'urut', 'refdhkp', 'buku', 'hal']);
                    $dhkpRowRef = ! empty($rawNoUrut) ? trim((string)$rawNoUrut) : "Row #{$rowIndex}";

                    $rawYear = $this->getValue($cells, $headerMap, ['tahunpajak', 'tahun', 'thnpajak', 'thn']);
                    $taxYear = ! empty($rawYear) ? (int)$rawYear : $defaultTaxYear;

                    // Resolve Dusun Model & Name
                    $dusunRecord = null;
                    if (! empty($dusunRaw)) {
                        $normDusun = strtolower($dusunRaw);
                        $dusunRecord = $allDusuns->first(function ($d) use ($normDusun) {
                            return strtolower($d->name) === $normDusun ||
                                (! empty($d->code) && strtolower($d->code) === $normDusun);
                        });

                        if (! $dusunRecord) {
                            $dusunRecord = $allDusuns->first(function ($d) use ($normDusun) {
                                $dName = strtolower($d->name);
                                return str_contains($normDusun, $dName) || str_contains($dName, $normDusun);
                            });
                        }
                    }

                    $finalDusunName = $dusunRecord?->name ?? (! empty($dusunRaw) ? $dusunRaw : null);
                    $finalDusunId = $dusunRecord?->id;

                    DB::transaction(function () use (
                        $cleanNop,
                        $cleanNik,
                        $namaWp,
                        $alamatWp,
                        $alamatOp,
                        $rt,
                        $rw,
                        $finalDusunName,
                        $finalDusunId,
                        $luasBumi,
                        $luasBangunan,
                        $luasBumiBersama,
                        $luasBangunanBersama,
                        $pbbTerutang,
                        $dhkpRowRef,
                        $taxYear,
                        $rowIndex,
                        &$errors
                    ) {
                        // 1. Taxpayer
                        $taxpayer = null;
                        if (! empty($cleanNik)) {
                            $taxpayer = Taxpayer::where('nik', $cleanNik)->first();
                        }
                        if (! $taxpayer && ! empty($namaWp)) {
                            $taxpayer = Taxpayer::where('name', $namaWp)->first();
                        }

                        if ($taxpayer) {
                            $updateWp = [];
                            if (empty($taxpayer->nik) && ! empty($cleanNik)) {
                                $updateWp['nik'] = $cleanNik;
                            }
                            if (empty($taxpayer->address) && ! empty($alamatWp)) {
                                $updateWp['address'] = $alamatWp;
                            }
                            if (empty($taxpayer->rt) && ! empty($rt)) {
                                $updateWp['rt'] = $rt;
                            }
                            if (empty($taxpayer->rw) && ! empty($rw)) {
                                $updateWp['rw'] = $rw;
                            }
                            if (empty($taxpayer->dusun) && ! empty($finalDusunName)) {
                                $updateWp['dusun'] = $finalDusunName;
                            }
                            if (! empty($updateWp)) {
                                $taxpayer->update($updateWp);
                            }
                        } else {
                            $taxpayer = Taxpayer::create([
                                'name' => $namaWp,
                                'nik' => $cleanNik,
                                'address' => $alamatWp ?: null,
                                'rt' => $rt ?: null,
                                'rw' => $rw ?: null,
                                'dusun' => $finalDusunName,
                                'village_name' => Village::current()?->name ?? 'Desa Sukahaji',
                            ]);
                        }

                        // 2. Tax Object (NOP)
                        $taxObject = TaxObject::where('nop', $cleanNop)->first();
                        if ($taxObject) {
                            $updateOp = [];
                            if ($taxObject->taxpayer_id !== $taxpayer->id) {
                                $updateOp['taxpayer_id'] = $taxpayer->id;
                            }
                            if (! empty($alamatOp) && $taxObject->address !== $alamatOp) {
                                $updateOp['address'] = $alamatOp;
                            }
                            if (! empty($rt) && $taxObject->rt !== $rt) {
                                $updateOp['rt'] = $rt;
                            }
                            if (! empty($rw) && $taxObject->rw !== $rw) {
                                $updateOp['rw'] = $rw;
                            }
                            if (! empty($finalDusunName) && $taxObject->dusun !== $finalDusunName) {
                                $updateOp['dusun'] = $finalDusunName;
                            }
                            if ($finalDusunId && $taxObject->dusun_id !== $finalDusunId) {
                                $updateOp['dusun_id'] = $finalDusunId;
                            }
                            if (! empty($updateOp)) {
                                $taxObject->update($updateOp);
                            }
                        } else {
                            $taxObject = TaxObject::create([
                                'nop' => $cleanNop,
                                'taxpayer_id' => $taxpayer->id,
                                'address' => $alamatOp,
                                'rt' => $rt ?: null,
                                'rw' => $rw ?: null,
                                'dusun' => $finalDusunName,
                                'dusun_id' => $finalDusunId,
                                'status' => 'normal',
                            ]);
                        }

                        // 3. Components (Bumi, Bangunan, Bumi Bersama, Bangunan Bersama)
                        $componentList = [
                            'bumi' => $luasBumi,
                            'bangunan' => $luasBangunan,
                            'bumi_bersama' => $luasBumiBersama,
                            'bangunan_bersama' => $luasBangunanBersama,
                        ];

                        foreach ($componentList as $cType => $cArea) {
                            if ($cArea > 0) {
                                TaxObjectComponent::updateOrCreate(
                                    [
                                        'tax_object_id' => $taxObject->id,
                                        'type' => $cType,
                                        'tax_year' => $taxYear,
                                    ],
                                    [
                                        'area_m2' => $cArea,
                                    ]
                                );
                            }
                        }

                        // 4. SPPT (Assessed bill from Bapenda) with financial safety invariant
                        $sppt = Sppt::where('tax_object_id', $taxObject->id)
                            ->where('tax_year', $taxYear)
                            ->first();

                        if ($sppt) {
                            $paidAmount = (float)$sppt->paid_amount;
                            if ($paidAmount > 0 && $pbbTerutang < $paidAmount) {
                                $errors[] = "Baris #{$rowIndex} (NOP {$cleanNop}): Tagihan berkas (Rp " . number_format($pbbTerutang, 0, ',', '.') . ") lebih kecil dari cicilan yang sudah diterima di kasir (Rp " . number_format($paidAmount, 0, ',', '.') . "). Nominal tagihan dipertahankan Rp " . number_format($sppt->total_bill, 0, ',', '.') . " untuk melindungi saldo kasir.";
                                $sppt->dhkp_row_ref = $dhkpRowRef;
                                $sppt->save();
                            } else {
                                $fineAmount = (float)($sppt->fine_amount ?? 0);
                                $sppt->pbb_terutang = $pbbTerutang;
                                $sppt->total_bill = $pbbTerutang + $fineAmount;
                                $sppt->dhkp_row_ref = $dhkpRowRef;
                                $sppt->recalculateStatus();
                                $sppt->save();
                            }
                        } else {
                            Sppt::create([
                                'tax_object_id' => $taxObject->id,
                                'tax_year' => $taxYear,
                                'due_date' => "{$taxYear}-09-30",
                                'pbb_terutang' => $pbbTerutang,
                                'fine_amount' => 0,
                                'total_bill' => $pbbTerutang,
                                'paid_amount' => 0,
                                'status' => 'unpaid',
                                'dhkp_row_ref' => $dhkpRowRef,
                            ]);
                        }
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
            'errors' => array_slice($errors, 0, 20),
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

    private function cleanNop(mixed $rawNop): string
    {
        $str = trim((string)$rawNop);
        if (stripos($str, 'e') !== false && is_numeric($rawNop)) {
            $str = sprintf('%.0f', (float)$rawNop);
        }
        return preg_replace('/[^0-9]/', '', $str);
    }

    public function cleanNumber(mixed $val): float
    {
        if (is_numeric($val)) {
            return (float)$val;
        }

        if (empty($val)) {
            return 0.0;
        }

        $str = trim((string)$val);
        $str = preg_replace('/[^\d.,]/', '', $str);

        // Format Indonesian currency: 125.000 or 125.000,00
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $str)) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
            return (float)$str;
        }

        // Format US currency: 125,000 or 125,000.00
        if (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $str)) {
            $str = str_replace(',', '', $str);
            return (float)$str;
        }

        // Comma decimal: 120,5
        if (preg_match('/^\d+,\d+$/', $str)) {
            $str = str_replace(',', '.', $str);
            return (float)$str;
        }

        $clean = preg_replace('/[^0-9.]/', '', $str);
        return is_numeric($clean) ? (float)$clean : 0.0;
    }

    /**
     * Import / roll-forward SPPT and components from a previous tax year.
     *
     * @param array{
     *     dusun_id?: ?int,
     *     on_duplicate?: string,
     *     copy_components?: bool,
     *     pbb_adjustment_percent?: float
     * } $options
     * @return array{total_rows: int, imported: int, skipped: int, errors: array<string>}
     */
    public function importFromPreviousYear(int $sourceYear, int $targetYear, array $options = []): array
    {
        if ($sourceYear === $targetYear) {
            throw new \InvalidArgumentException('Tahun pajak sumber dan tahun pajak tujuan tidak boleh sama.');
        }

        $dusunId = ! empty($options['dusun_id']) ? (int)$options['dusun_id'] : null;
        $onDuplicate = $options['on_duplicate'] ?? 'skip';
        $copyComponents = $options['copy_components'] ?? true;
        $adjustmentPercent = (float)($options['pbb_adjustment_percent'] ?? 0);
        $multiplier = 1 + ($adjustmentPercent / 100);

        $sourceSppts = Sppt::with(['taxObject.components' => function ($q) use ($sourceYear) {
                $q->where('tax_year', $sourceYear);
            }])
            ->where('tax_year', $sourceYear)
            ->whereHas('taxObject', function ($q) use ($dusunId) {
                $q->whereNotIn('status', ['inactive', 'deactivated']);
                if ($dusunId) {
                    $q->where('dusun_id', $dusunId);
                }
            })
            ->get();

        $totalSource = $sourceSppts->count();
        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use (
            $sourceSppts,
            $sourceYear,
            $targetYear,
            $onDuplicate,
            $copyComponents,
            $multiplier,
            &$imported,
            &$skipped,
            &$errors
        ) {
            foreach ($sourceSppts as $src) {
                $taxObject = $src->taxObject;
                if (! $taxObject || in_array($taxObject->status, ['inactive', 'deactivated'])) {
                    $skipped++;
                    continue;
                }

                $targetSppt = Sppt::where('tax_object_id', $taxObject->id)
                    ->where('tax_year', $targetYear)
                    ->first();

                $newPbb = max(0, round((float)$src->pbb_terutang * $multiplier));

                if ($targetSppt) {
                    if ($onDuplicate === 'skip') {
                        $skipped++;
                        continue;
                    }

                    if ((float)$targetSppt->paid_amount > 0) {
                        $errors[] = "NOP {$taxObject->formatted_nop}: Dilewati karena sudah ada pembayaran kasir Rp " . number_format($targetSppt->paid_amount, 0, ',', '.') . " di tahun {$targetYear}.";
                        $skipped++;
                        continue;
                    }

                    $targetSppt->update([
                        'njop_dasar' => $src->njop_dasar,
                        'njoptkp' => $src->njoptkp,
                        'njop_perhitungan' => $src->njop_perhitungan,
                        'tarif_percent' => $src->tarif_percent,
                        'pbb_terutang' => $newPbb,
                        'total_bill' => $newPbb,
                        'is_to_blocked' => $taxObject->isTo(),
                        'dhkp_row_ref' => $src->dhkp_row_ref ? "Salin {$sourceYear}: " . $src->dhkp_row_ref : null,
                    ]);

                    $imported++;
                } else {
                    Sppt::create([
                        'tax_object_id' => $taxObject->id,
                        'tax_year' => $targetYear,
                        'due_date' => "{$targetYear}-09-30",
                        'njop_dasar' => $src->njop_dasar,
                        'njoptkp' => $src->njoptkp,
                        'njop_perhitungan' => $src->njop_perhitungan,
                        'tarif_percent' => $src->tarif_percent,
                        'pbb_terutang' => $newPbb,
                        'fine_amount' => 0,
                        'total_bill' => $newPbb,
                        'paid_amount' => 0,
                        'status' => 'unpaid',
                        'is_to_blocked' => $taxObject->isTo(),
                        'dhkp_row_ref' => $src->dhkp_row_ref ? "Salin {$sourceYear}: " . $src->dhkp_row_ref : null,
                    ]);

                    $imported++;
                }

                if ($copyComponents && $taxObject->components->isNotEmpty()) {
                    foreach ($taxObject->components as $comp) {
                        TaxObjectComponent::firstOrCreate(
                            [
                                'tax_object_id' => $taxObject->id,
                                'type' => $comp->type,
                                'tax_year' => $targetYear,
                            ],
                            [
                                'area_m2' => $comp->area_m2,
                                'class_code' => $comp->class_code,
                                'njop_per_m2' => $comp->njop_per_m2,
                                'total_njop' => $comp->total_njop,
                            ]
                        );
                    }
                }
            }
        });

        AuditLog::log('create', "Import SPPT dari tahun {$sourceYear} ke {$targetYear}: Berhasil {$imported}, Dilewati {$skipped} dari total {$totalSource} data.");

        return [
            'total_rows' => $totalSource,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 20),
        ];
    }
}
