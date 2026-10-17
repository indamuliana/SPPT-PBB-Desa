<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\Village;
use App\Services\DhkpImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

    public function importView(): View
    {
        $village = Village::current();
        return view('sppts.import', compact('village'));
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
}
