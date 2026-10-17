<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dusun;
use App\Models\TaxObject;
use App\Models\Taxpayer;
use App\Models\Village;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaxObjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = TaxObject::with(['taxpayer', 'sppts', 'dusunRelation']);

        if ($request->filled('search')) {
            $search = preg_replace('/[^0-9a-zA-Z]/', '', $request->search);
            $rawSearch = $request->search;
            $query->where(function ($q) use ($search, $rawSearch) {
                $q->where('nop', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$rawSearch}%")
                  ->orWhere('dusun', 'like', "%{$rawSearch}%")
                  ->orWhereHas('taxpayer', function ($tq) use ($rawSearch) {
                      $tq->where('name', 'like', "%{$rawSearch}%");
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
        return view('tax-objects.create', compact('taxpayers', 'dusuns'));
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
        ]);

        if (! empty($validated['dusun_id'])) {
            $dusunObj = Dusun::find($validated['dusun_id']);
            $validated['dusun'] = $dusunObj?->name ?? $validated['dusun'] ?? null;
        }

        $taxObject = TaxObject::create($validated);

        AuditLog::log('create', "Menambahkan Objek Pajak NOP: {$taxObject->formatted_nop}", $taxObject);

        return redirect()->route('tax-objects.index')->with('success', "Objek Pajak NOP {$taxObject->formatted_nop} berhasil ditambahkan.");
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
            $q->orWhereHas('taxpayer', function ($tq) use ($query) {
                $tq->where('name', 'like', "%{$query}%");
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
