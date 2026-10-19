<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dusun;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\TaxObjectComponent;
use App\Models\Taxpayer;
use App\Models\Village;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaxObjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = TaxObject::with(['taxpayer', 'sppts', 'dusunRelation']);

        if ($request->filled('search')) {
            $rawSearch = trim((string)$request->search);
            $searchNop = preg_replace('/[^0-9]/', '', $rawSearch);
            $query->where(function ($q) use ($rawSearch, $searchNop) {
                if (! empty($searchNop)) {
                    $q->where('nop', 'like', "%{$searchNop}%");
                }
                $q->orWhere('address', 'like', "%{$rawSearch}%")
                  ->orWhere('dusun', 'like', "%{$rawSearch}%")
                  ->orWhereHas('taxpayer', function ($tq) use ($rawSearch, $searchNop) {
                      $tq->where('name', 'like', "%{$rawSearch}%");
                      if (! empty($searchNop)) {
                          $tq->orWhere('nik', 'like', "%{$searchNop}%");
                      }
                  });
            });
        }

        if ($request->filled('dusun_id')) {
            $query->where('dusun_id', $request->dusun_id);
        } elseif ($request->filled('dusun')) {
            $query->where('dusun', $request->dusun);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $taxObjects = $query->latest('id')->paginate(15)->withQueryString();

        $dusunList = Dusun::orderBy('name')->get();

        return view('tax-objects.index', compact('taxObjects', 'dusunList'));
    }

    public function create(): View
    {
        $taxpayers = Taxpayer::orderBy('name')->get();
        $dusuns = Dusun::orderBy('name')->get();
        $village = Village::current();
        return view('tax-objects.create', compact('taxpayers', 'dusuns', 'village'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'nop' => preg_replace('/[^0-9]/', '', (string)$request->nop),
        ]);

        $validated = $request->validate([
            'nop' => ['required', 'string', 'min:10', 'max:25', 'unique:tax_objects,nop'],
            'taxpayer_id' => ['required', 'exists:taxpayers,id'],
            'address' => ['nullable', 'string'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'dusun_id' => ['nullable', 'exists:dusuns,id'],
            'dusun' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['normal', 'to'])],
            'notes' => ['nullable', 'string'],

            // Optional 4 components & SPPT creation
            'include_components' => ['nullable', 'boolean'],
            'tax_year' => ['nullable', 'integer', 'min:2000', 'max:2099'],
            'due_date' => ['nullable', 'date'],
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
            'njoptkp' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            if (! empty($validated['dusun_id'])) {
                $dusunObj = Dusun::find($validated['dusun_id']);
                $validated['dusun'] = $dusunObj?->name ?? $validated['dusun'] ?? null;
            }

            $taxObject = TaxObject::create([
                'nop' => $validated['nop'],
                'taxpayer_id' => $validated['taxpayer_id'],
                'address' => $validated['address'] ?? null,
                'rt' => $validated['rt'] ?? null,
                'rw' => $validated['rw'] ?? null,
                'dusun_id' => $validated['dusun_id'] ?? null,
                'dusun' => $validated['dusun'] ?? null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            AuditLog::log('create', "Menambahkan Objek Pajak NOP: {$taxObject->formatted_nop}", $taxObject);

            if ($request->boolean('include_components')) {
                $village = Village::current();
                $taxYear = (int)($validated['tax_year'] ?? $village->active_tax_year);

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

                    if ($comp['area'] > 0 || $comp['njop_m2'] > 0) {
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
                }

                $njoptkp = isset($validated['njoptkp']) && $validated['njoptkp'] !== null ? (float)$validated['njoptkp'] : 10000000;
                $njopPerhitungan = max(0, $totalNjopDasar - $njoptkp);
                $tarifPercent = (float)($village->tax_rate_percent ?? 0.100);
                $pbbTerutang = round($njopPerhitungan * ($tarifPercent / 100));

                $sppt = Sppt::create([
                    'tax_object_id' => $taxObject->id,
                    'tax_year' => $taxYear,
                    'due_date' => $validated['due_date'] ?? "{$taxYear}-09-30",
                    'njop_dasar' => $totalNjopDasar,
                    'njoptkp' => $njoptkp,
                    'njop_perhitungan' => $njopPerhitungan,
                    'tarif_percent' => $tarifPercent,
                    'pbb_terutang' => $pbbTerutang,
                    'fine_amount' => 0,
                    'total_bill' => $pbbTerutang,
                    'paid_amount' => 0,
                    'status' => 'unpaid',
                    'is_to_blocked' => $taxObject->isTo(),
                ]);

                AuditLog::log('create', "Menerbitkan SPPT NOP: {$taxObject->formatted_nop} Tahun {$taxYear} sebesar Rp " . number_format($pbbTerutang, 0, ',', '.'), $sppt);

                return redirect()->route('tax-objects.index')->with('success', "Objek Pajak NOP {$taxObject->formatted_nop} dan SPPT Tahun {$taxYear} (Tagihan: Rp " . number_format($pbbTerutang, 0, ',', '.') . ") berhasil disimpan.");
            }

            return redirect()->route('tax-objects.index')->with('success', "Objek Pajak NOP {$taxObject->formatted_nop} berhasil ditambahkan.");
        });
    }

    public function edit(TaxObject $taxObject): View
    {
        $taxpayers = Taxpayer::orderBy('name')->get();
        $dusuns = Dusun::orderBy('name')->get();
        return view('tax-objects.edit', compact('taxObject', 'taxpayers', 'dusuns'));
    }

    public function update(Request $request, TaxObject $taxObject): RedirectResponse
    {
        $request->merge([
            'nop' => preg_replace('/[^0-9]/', '', (string)$request->nop),
        ]);

        $validated = $request->validate([
            'nop' => ['required', 'string', 'min:10', 'max:25', Rule::unique('tax_objects', 'nop')->ignore($taxObject->id)],
            'taxpayer_id' => ['required', 'exists:taxpayers,id'],
            'address' => ['nullable', 'string'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'dusun_id' => ['nullable', 'exists:dusuns,id'],
            'dusun' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['normal', 'to'])],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($validated['dusun_id'])) {
            $dusunObj = Dusun::find($validated['dusun_id']);
            $validated['dusun'] = $dusunObj?->name ?? $validated['dusun'] ?? null;
        }

        $old = $taxObject->only(array_keys($validated));
        $taxObject->update($validated);

        AuditLog::log('update', "Memperbarui Objek Pajak NOP: {$taxObject->formatted_nop}", $taxObject, $old, $validated);

        return redirect()->route('tax-objects.index')->with('success', "Data Objek Pajak NOP {$taxObject->formatted_nop} berhasil diperbarui.");
    }

    public function show(TaxObject $taxObject): View
    {
        $taxObject->load([
            'taxpayer',
            'dusunRelation',
            'components' => function ($q) {
                $q->orderBy('type');
            },
            'sppts' => function ($q) {
                $q->with(['payments.cashier'])->orderByDesc('tax_year');
            },
            'toCases' => function ($q) {
                $q->with('reporter')->latest();
            },
            'mutations' => function ($q) {
                $q->with('proposer')->latest();
            },
        ]);

        $hasPayments = \App\Models\Payment::whereIn('sppt_id', $taxObject->sppts->pluck('id'))->exists();

        return view('tax-objects.show', compact('taxObject', 'hasPayments'));
    }

    public function destroy(TaxObject $taxObject): RedirectResponse
    {
        $hasPayments = \App\Models\Payment::whereIn('sppt_id', $taxObject->sppts->pluck('id'))->exists();

        if ($hasPayments) {
            return back()->with('error', "Objek Pajak NOP {$taxObject->formatted_nop} tidak dapat dihapus permanen karena sudah memiliki riwayat transaksi pembayaran. Silakan gunakan opsi Nonaktifkan / Arsipkan Objek.");
        }

        $formattedNop = $taxObject->formatted_nop;

        \Illuminate\Support\Facades\DB::transaction(function () use ($taxObject) {
            $taxObject->components()->delete();
            $taxObject->sppts()->delete();
            $taxObject->delete();
        });

        AuditLog::log('delete', "Menghapus permanen Objek Pajak NOP: {$formattedNop}", null);

        return redirect()->route('tax-objects.index')->with('success', "Objek Pajak NOP {$formattedNop} beserta komponen dan SPPT-nya berhasil dihapus permanen.");
    }

    public function deactivate(Request $request, TaxObject $taxObject): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', Rule::in(['mutasi', 'pecah', 'to', 'hilang', 'lainnya'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $taxObject->update([
            'status' => 'inactive',
            'deactivation_reason' => $validated['reason'],
            'deactivation_notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::log('update', "Menonaktifkan/mengarsipkan Objek Pajak NOP: {$taxObject->formatted_nop}. Alasan: {$taxObject->deactivation_reason_label}", $taxObject);

        return back()->with('success', "Objek Pajak NOP {$taxObject->formatted_nop} berhasil dinonaktifkan/diarsipkan.");
    }

    public function activate(TaxObject $taxObject): RedirectResponse
    {
        $taxObject->update([
            'status' => 'normal',
            'deactivation_reason' => null,
            'deactivation_notes' => null,
        ]);

        AuditLog::log('update', "Mengaktifkan kembali Objek Pajak NOP: {$taxObject->formatted_nop}", $taxObject);

        return back()->with('success', "Objek Pajak NOP {$taxObject->formatted_nop} berhasil diaktifkan kembali.");
    }

    public function apiSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = trim((string)$request->input('q', ''));
        $clean = preg_replace('/[^0-9]/', '', $query);

        if (strlen($query) < 2 && strlen($clean) < 2) {
            return response()->json([]);
        }

        $activeYear = Village::current()->active_tax_year;

        $objects = TaxObject::with(['taxpayer', 'dusunRelation', 'spptForYear'])
        ->where(function ($q) use ($query, $clean) {
            if (! empty($clean)) {
                $q->where('nop', 'like', "%{$clean}%");
            }
            $q->orWhereHas('taxpayer', function ($tq) use ($query, $clean) {
                $tq->where('name', 'like', "%{$query}%");
                if (! empty($clean)) {
                    $tq->orWhere('nik', 'like', "%{$clean}%");
                }
            });
        })
        ->take(10)
        ->get();

        $result = $objects->map(function ($op) {
            $sppt = $op->spptForYear;
            return [
                'id' => $op->id,
                'nop' => $op->nop,
                'formatted_nop' => $op->formatted_nop,
                'taxpayer_name' => $op->taxpayer?->name ?? 'Tanpa Nama',
                'taxpayer_nik' => $op->taxpayer?->nik ?? '',
                'address' => $op->formatted_address,
                'dusun' => $op->dusunRelation?->name ?? $op->dusun ?? '-',
                'status' => $op->status,
                'is_to' => $op->isTo(),
                'is_inactive' => $op->isInactive(),
                'active_tax_year' => $sppt?->tax_year,
                'active_bill' => $sppt ? (float)$sppt->total_bill : 0,
                'remaining_bill' => $sppt ? (float)$sppt->remaining_bill : 0,
            ];
        });

        return response()->json($result);
    }
}
