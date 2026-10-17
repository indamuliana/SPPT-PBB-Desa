<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dusun;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\TaxObjectComponent;
use App\Models\Taxpayer;
use App\Models\Village;
use App\Services\DhkpImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SpptController extends Controller
{
    public function index(Request $request): View
    {
        $village = Village::current();
        $selectedYear = $request->input('year', $village->active_tax_year);

        $query = Sppt::with(['taxObject.taxpayer'])
            ->where('tax_year', $selectedYear);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $rawSearch = $request->search;
            $cleanSearch = preg_replace('/[^0-9]/', '', $rawSearch);
            $query->where(function ($q) use ($rawSearch, $cleanSearch) {
                if (! empty($cleanSearch)) {
                    $q->whereHas('taxObject', function ($oq) use ($cleanSearch) {
                        $oq->where('nop', 'like', "%{$cleanSearch}%");
                    });
                }
                $q->orWhereHas('taxObject.taxpayer', function ($tq) use ($rawSearch) {
                    $tq->where('name', 'like', "%{$rawSearch}%");
                });
            });
        }

        if ($request->filled('dusun')) {
            $dusun = $request->dusun;
            $query->whereHas('taxObject', function ($oq) use ($dusun) {
                $oq->where('dusun', $dusun);
            });
        }

        // Summary metrics for the selected year
        $baseQuery = Sppt::where('tax_year', $selectedYear);
        $summary = [
            'total_bills' => (clone $baseQuery)->sum('total_bill'),
            'total_paid' => (clone $baseQuery)->sum('paid_amount'),
            'total_count' => (clone $baseQuery)->count(),
            'paid_count' => (clone $baseQuery)->where('status', 'paid')->count(),
            'unpaid_count' => (clone $baseQuery)->where('status', 'unpaid')->count(),
            'partial_count' => (clone $baseQuery)->where('status', 'partial')->count(),
        ];
        $summary['total_remaining'] = max(0, $summary['total_bills'] - $summary['total_paid']);

        $sppts = $query->latest('id')->paginate(20)->withQueryString();

        $years = Sppt::distinct()->orderByDesc('tax_year')->pluck('tax_year')->toArray();
        if (! in_array($village->active_tax_year, $years)) {
            array_unshift($years, $village->active_tax_year);
        }

        $dusunList = TaxObject::whereNotNull('dusun')->distinct()->pluck('dusun');

        return view('sppts.index', compact('sppts', 'summary', 'selectedYear', 'years', 'dusunList'));
    }

    public function create(): View
    {
        $taxObjects = TaxObject::with('taxpayer')->orderBy('nop')->get();
        $defaultYear = Village::current()->active_tax_year;

        return view('sppts.create', compact('taxObjects', 'defaultYear'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tax_object_id' => ['required', 'exists:tax_objects,id'],
            'tax_year' => ['required', 'integer', 'min:2000', 'max:2099'],
            'pbb_terutang' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
        ]);

        $exists = Sppt::where('tax_object_id', $validated['tax_object_id'])
            ->where('tax_year', $validated['tax_year'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['tax_object_id' => 'Ketetapan SPPT untuk NOP dan tahun pajak ini sudah terdaftar.']);
        }

        $validated['total_bill'] = $validated['pbb_terutang'];
        $validated['status'] = 'unpaid';

        $sppt = Sppt::create($validated);

        AuditLog::log('create', "Membuat ketetapan SPPT NOP {$sppt->taxObject->formatted_nop} Tahun {$sppt->tax_year} sebesar Rp " . number_format($sppt->total_bill, 0, ',', '.'), $sppt);

        return redirect()->route('sppts.index', ['year' => $sppt->tax_year])->with('success', 'Data SPPT berhasil disimpan.');
    }

    public function importView(Request $request): View
    {
        $village = Village::current();
        $dusuns = Dusun::orderBy('name')->get();
        $initialMode = $request->query('mode', 'file');
        $availableSourceYears = Sppt::select('tax_year', DB::raw('count(*) as sppt_count'))
            ->groupBy('tax_year')
            ->orderByDesc('tax_year')
            ->get();

        return view('sppts.import', compact('village', 'dusuns', 'initialMode', 'availableSourceYears'));
    }

    public function downloadTemplate(Request $request, DhkpImportService $importService): BinaryFileResponse
    {
        $format = strtolower($request->query('format', 'xlsx')) === 'csv' ? 'csv' : 'xlsx';
        $taxYear = (int)$request->query('tax_year', Village::current()?->active_tax_year ?? (int)date('Y'));

        $filePath = $importService->generateTemplate($format, $taxYear);
        $villageName = preg_replace('/[^A-Za-z0-9]/', '_', Village::current()?->name ?? 'Desa');
        $fileName = "Template_DHKP_SPPT_{$villageName}_{$taxYear}.{$format}";

        $contentType = $format === 'csv'
            ? 'text/csv'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        return response()->download($filePath, $fileName, [
            'Content-Type' => $contentType,
        ])->deleteFileAfterSend(true);
    }

    public function importProcess(Request $request, DhkpImportService $importService): RedirectResponse
    {
        $request->validate([
            'dhkp_file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:20480'], // max 20MB
            'tax_year' => ['required', 'integer', 'min:2000', 'max:2099'],
        ], [
            'dhkp_file.required' => 'Pilih berkas Excel (.xlsx) atau CSV DHKP terlebih dahulu.',
            'dhkp_file.mimes' => 'Format berkas harus berupa Excel (.xlsx) atau CSV (.csv).',
            'dhkp_file.max' => 'Ukuran berkas maksimal 20 MB.',
        ]);

        $path = $request->file('dhkp_file')->getRealPath();
        $taxYear = (int)$request->input('tax_year');

        $result = $importService->import($path, $taxYear);

        $msg = "Import DHKP Selesai: {$result['imported']} data berhasil diimpor, {$result['skipped']} baris dilewati dari total {$result['total_rows']} baris.";

        if (! empty($result['errors'])) {
            return redirect()->route('sppts.index', ['year' => $taxYear])
                ->with('success', $msg)
                ->with('import_warnings', $result['errors']);
        }

        return redirect()->route('sppts.index', ['year' => $taxYear])->with('success', $msg);
    }

    public function importFromPreviousYear(Request $request, DhkpImportService $importService): RedirectResponse
    {
        $validated = $request->validate([
            'source_year' => ['required', 'integer', 'min:2000', 'max:2099'],
            'target_year' => ['required', 'integer', 'min:2000', 'max:2099', 'different:source_year'],
            'dusun_id' => ['nullable', 'exists:dusuns,id'],
            'on_duplicate' => ['required', 'in:skip,update'],
            'copy_components' => ['nullable', 'boolean'],
            'pbb_adjustment_percent' => ['nullable', 'numeric', 'min:-100', 'max:500'],
        ], [
            'source_year.required' => 'Pilih tahun pajak asal / sumber data.',
            'target_year.required' => 'Tentukan tahun pajak tujuan salinan data.',
            'target_year.different' => 'Tahun pajak tujuan tidak boleh sama dengan tahun pajak asal.',
            'on_duplicate.required' => 'Pilih tindakan jika data SPPT sudah ada di tahun tujuan.',
        ]);

        $sourceYear = (int)$validated['source_year'];
        $targetYear = (int)$validated['target_year'];
        $options = [
            'dusun_id' => ! empty($validated['dusun_id']) ? (int)$validated['dusun_id'] : null,
            'on_duplicate' => $validated['on_duplicate'] ?? 'skip',
            'copy_components' => $request->boolean('copy_components', true),
            'pbb_adjustment_percent' => (float)($validated['pbb_adjustment_percent'] ?? 0),
        ];

        $result = $importService->importFromPreviousYear($sourceYear, $targetYear, $options);

        $msg = "Salin Data SPPT Selesai: {$result['imported']} data berhasil disalin ke tahun {$targetYear}, {$result['skipped']} data dilewati dari total {$result['total_rows']} SPPT di tahun {$sourceYear}.";

        if (! empty($result['errors'])) {
            return redirect()->route('sppts.index', ['year' => $targetYear])
                ->with('success', $msg)
                ->with('import_warnings', $result['errors']);
        }

        return redirect()->route('sppts.index', ['year' => $targetYear])->with('success', $msg);
    }

    public function createUnified(): View
    {
        $village = Village::current();
        $taxpayers = Taxpayer::orderBy('name')->get();
        $dusuns = Dusun::orderBy('name')->get();
        $defaultYear = $village->active_tax_year;
        $taxRate = (float)($village->tax_rate_percent ?? 0.100);

        return view('sppts.create-unified', compact('village', 'taxpayers', 'dusuns', 'defaultYear', 'taxRate'));
    }

    public function storeUnified(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // WP
            'taxpayer_mode' => ['required', 'in:existing,new'],
            'taxpayer_id' => ['required_if:taxpayer_mode,existing', 'nullable', 'exists:taxpayers,id'],
            'new_taxpayer_name' => ['required_if:taxpayer_mode,new', 'nullable', 'string', 'max:255'],
            'new_taxpayer_nik' => ['nullable', 'string', 'max:20'],
            'new_taxpayer_phone' => ['nullable', 'string', 'max:50'],
            'new_taxpayer_address' => ['nullable', 'string'],
            'new_taxpayer_rt' => ['nullable', 'string', 'max:5'],
            'new_taxpayer_rw' => ['nullable', 'string', 'max:5'],

            // NOP
            'nop' => ['required', 'string'],
            'object_address' => ['nullable', 'string'],
            'object_rt' => ['nullable', 'string', 'max:5'],
            'object_rw' => ['nullable', 'string', 'max:5'],
            'object_dusun_id' => ['nullable', 'exists:dusuns,id'],
            'object_dusun' => ['nullable', 'string', 'max:50'],

            // 4 Components
            'bumi_area_m2' => ['nullable', 'numeric', 'min:0'],
            'bumi_class_code' => ['nullable', 'string', 'max:10'],
            'bumi_njop_per_m2' => ['nullable', 'numeric', 'min:0'],

            'bangunan_area_m2' => ['nullable', 'numeric', 'min:0'],
            'bangunan_class_code' => ['nullable', 'string', 'max:10'],
            'bangunan_njop_per_m2' => ['nullable', 'numeric', 'min:0'],

            'bumi_bersama_area_m2' => ['nullable', 'numeric', 'min:0'],
            'bumi_bersama_class_code' => ['nullable', 'string', 'max:10'],
            'bumi_bersama_njop_per_m2' => ['nullable', 'numeric', 'min:0'],

            'bangunan_bersama_area_m2' => ['nullable', 'numeric', 'min:0'],
            'bangunan_bersama_class_code' => ['nullable', 'string', 'max:10'],
            'bangunan_bersama_njop_per_m2' => ['nullable', 'numeric', 'min:0'],

            // SPPT
            'tax_year' => ['required', 'integer', 'min:2000', 'max:2099'],
            'njoptkp' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
        ]);

        $cleanNop = preg_replace('/[^0-9]/', '', $validated['nop']);
        if (strlen($cleanNop) < 10) {
            return back()->withInput()->withErrors(['nop' => 'Format NOP tidak valid (minimal 10 digit).']);
        }

        $village = Village::current();
        $taxYear = (int)$validated['tax_year'];

        $sppt = DB::transaction(function () use ($validated, $cleanNop, $village, $taxYear) {
            // 1. Resolve Taxpayer
            if ($validated['taxpayer_mode'] === 'new') {
                $taxpayer = Taxpayer::create([
                    'name' => $validated['new_taxpayer_name'],
                    'nik' => $validated['new_taxpayer_nik'] ?? null,
                    'phone' => $validated['new_taxpayer_phone'] ?? null,
                    'address' => $validated['new_taxpayer_address'] ?? null,
                    'rt' => $validated['new_taxpayer_rt'] ?? null,
                    'rw' => $validated['new_taxpayer_rw'] ?? null,
                ]);
            } else {
                $taxpayer = Taxpayer::findOrFail($validated['taxpayer_id']);
            }

            // 2. Resolve TaxObject
            $dusunId = $validated['object_dusun_id'] ?? null;
            $dusunName = $validated['object_dusun'] ?? null;
            if ($dusunId && ! $dusunName) {
                $dusunName = Dusun::find($dusunId)?->name;
            }

            $taxObject = TaxObject::firstOrCreate(
                ['nop' => $cleanNop],
                [
                    'taxpayer_id' => $taxpayer->id,
                    'address' => $validated['object_address'] ?? null,
                    'rt' => $validated['object_rt'] ?? null,
                    'rw' => $validated['object_rw'] ?? null,
                    'dusun_id' => $dusunId,
                    'dusun' => $dusunName,
                    'status' => 'normal',
                ]
            );

            $updatePayload = [];
            if ($taxObject->taxpayer_id !== $taxpayer->id) {
                $updatePayload['taxpayer_id'] = $taxpayer->id;
            }
            if ($dusunId && $taxObject->dusun_id !== (int)$dusunId) {
                $updatePayload['dusun_id'] = $dusunId;
                $updatePayload['dusun'] = $dusunName;
            }
            if (! empty($updatePayload)) {
                $taxObject->update($updatePayload);
            }

            // 3. Components
            $componentsData = [
                'bumi' => [
                    'area' => (float)($validated['bumi_area_m2'] ?? 0),
                    'class' => $validated['bumi_class_code'] ?? null,
                    'njop_m2' => (float)($validated['bumi_njop_per_m2'] ?? 0),
                ],
                'bangunan' => [
                    'area' => (float)($validated['bangunan_area_m2'] ?? 0),
                    'class' => $validated['bangunan_class_code'] ?? null,
                    'njop_m2' => (float)($validated['bangunan_njop_per_m2'] ?? 0),
                ],
                'bumi_bersama' => [
                    'area' => (float)($validated['bumi_bersama_area_m2'] ?? 0),
                    'class' => $validated['bumi_bersama_class_code'] ?? null,
                    'njop_m2' => (float)($validated['bumi_bersama_njop_per_m2'] ?? 0),
                ],
                'bangunan_bersama' => [
                    'area' => (float)($validated['bangunan_bersama_area_m2'] ?? 0),
                    'class' => $validated['bangunan_bersama_class_code'] ?? null,
                    'njop_m2' => (float)($validated['bangunan_bersama_njop_per_m2'] ?? 0),
                ],
            ];

            $totalNjopDasar = 0;
            foreach ($componentsData as $type => $comp) {
                $totalNjopComp = $comp['area'] * $comp['njop_m2'];
                $totalNjopDasar += $totalNjopComp;

                TaxObjectComponent::updateOrCreate(
                    [
                        'tax_object_id' => $taxObject->id,
                        'type' => $type,
                        'tax_year' => $taxYear,
                    ],
                    [
                        'area_m2' => $comp['area'],
                        'class_code' => $comp['class'],
                        'njop_per_m2' => $comp['njop_m2'],
                        'total_njop' => $totalNjopComp,
                    ]
                );
            }

            // 4. Calculate SPPT
            $njoptkp = isset($validated['njoptkp']) && $validated['njoptkp'] !== null ? (float)$validated['njoptkp'] : 10000000;
            $njopPerhitungan = max(0, $totalNjopDasar - $njoptkp);
            $tarifPercent = (float)($village->tax_rate_percent ?? 0.100);
            $pbbTerutang = round($njopPerhitungan * ($tarifPercent / 100));

            $sppt = Sppt::updateOrCreate(
                [
                    'tax_object_id' => $taxObject->id,
                    'tax_year' => $taxYear,
                ],
                [
                    'due_date' => $validated['due_date'] ?? "{$taxYear}-09-30",
                    'njop_dasar' => $totalNjopDasar,
                    'njoptkp' => $njoptkp,
                    'njop_perhitungan' => $njopPerhitungan,
                    'tarif_percent' => $tarifPercent,
                    'pbb_terutang' => $pbbTerutang,
                    'total_bill' => $pbbTerutang,
                    'status' => 'unpaid',
                ]
            );

            AuditLog::log('create', "Perekaman SPPT Terpadu NOP {$taxObject->formatted_nop} Tahun {$taxYear} sebesar Rp " . number_format($pbbTerutang, 0, ',', '.'), $sppt);

            return $sppt;
        });

        return redirect()->route('sppts.index', ['year' => $sppt->tax_year])
            ->with('success', "Perekaman SPPT Terpadu NOP {$sppt->taxObject->formatted_nop} berhasil disimpan! Tagihan terhitung: Rp " . number_format($sppt->total_bill, 0, ',', '.'));
    }

    public function edit(Sppt $sppt): View
    {
        $sppt->load(['taxObject.taxpayer', 'taxObject.dusunRelation', 'payments']);
        return view('sppts.edit', compact('sppt'));
    }

    public function update(Request $request, Sppt $sppt): RedirectResponse
    {
        $validated = $request->validate([
            'pbb_terutang' => ['required', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'dhkp_row_ref' => ['nullable', 'string', 'max:50'],
            'njoptkp' => ['nullable', 'numeric', 'min:0'],
        ]);

        $newBill = (float)$validated['pbb_terutang'];

        if ((float)$sppt->paid_amount > $newBill) {
            return back()->withInput()->withErrors([
                'pbb_terutang' => 'Nominal tagihan baru (Rp ' . number_format($newBill, 0, ',', '.') . ') tidak boleh lebih kecil dari jumlah cicilan yang telah dibayar (Rp ' . number_format($sppt->paid_amount, 0, ',', '.') . ').'
            ]);
        }

        $oldValues = $sppt->only(['pbb_terutang', 'total_bill', 'due_date', 'dhkp_row_ref', 'njoptkp']);

        $sppt->pbb_terutang = $newBill;
        $sppt->total_bill = $newBill;
        $sppt->due_date = $validated['due_date'] ?? $sppt->due_date;
        $sppt->dhkp_row_ref = $validated['dhkp_row_ref'] ?? null;
        if (isset($validated['njoptkp'])) {
            $sppt->njoptkp = (float)$validated['njoptkp'];
        }

        $sppt->recalculateStatus();
        $sppt->save();

        AuditLog::log(
            'update',
            "Memperbarui SPPT NOP {$sppt->taxObject->formatted_nop} Tahun {$sppt->tax_year} (Tagihan: Rp " . number_format($newBill, 0, ',', '.') . ")",
            $sppt,
            $oldValues,
            $sppt->only(['pbb_terutang', 'total_bill', 'due_date', 'dhkp_row_ref', 'njoptkp'])
        );

        return redirect()->route('sppts.index', ['year' => $sppt->tax_year])
            ->with('success', "Data SPPT NOP {$sppt->taxObject->formatted_nop} Tahun {$sppt->tax_year} berhasil diperbarui.");
    }

    public function destroy(Sppt $sppt): RedirectResponse
    {
        if ($sppt->payments()->exists()) {
            return back()->with('error', "SPPT NOP {$sppt->taxObject->formatted_nop} Tahun {$sppt->tax_year} tidak dapat dihapus karena sudah memiliki riwayat pembayaran di kasir.");
        }

        $formattedNop = $sppt->taxObject->formatted_nop;
        $taxYear = $sppt->tax_year;

        $sppt->delete();

        AuditLog::log('delete', "Menghapus ketetapan SPPT NOP {$formattedNop} Tahun {$taxYear}", null);

        return redirect()->route('sppts.index', ['year' => $taxYear])
            ->with('success', "Ketetapan SPPT NOP {$formattedNop} Tahun {$taxYear} berhasil dihapus permanen.");
    }
}
