<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\TaxObject;
use App\Models\Taxpayer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaxObjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = TaxObject::with(['taxpayer', 'sppts']);

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

        if ($request->filled('dusun')) {
            $query->where('dusun', $request->dusun);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $taxObjects = $query->latest('id')->paginate(15)->withQueryString();

        $dusunList = TaxObject::whereNotNull('dusun')->distinct()->pluck('dusun');

        return view('tax-objects.index', compact('taxObjects', 'dusunList'));
    }

    public function create(): View
    {
        $taxpayers = Taxpayer::orderBy('name')->get();
        return view('tax-objects.create', compact('taxpayers'));
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
            'dusun' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['normal', 'to'])],
            'notes' => ['nullable', 'string'],
        ]);

        $taxObject = TaxObject::create($validated);

        AuditLog::log('create', "Menambahkan Objek Pajak NOP: {$taxObject->formatted_nop}", $taxObject);

        return redirect()->route('tax-objects.index')->with('success', "Objek Pajak NOP {$taxObject->formatted_nop} berhasil ditambahkan.");
    }

    public function edit(TaxObject $taxObject): View
    {
        $taxpayers = Taxpayer::orderBy('name')->get();
        return view('tax-objects.edit', compact('taxObject', 'taxpayers'));
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
            'dusun' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['normal', 'to'])],
            'notes' => ['nullable', 'string'],
        ]);

        $old = $taxObject->only(array_keys($validated));
        $taxObject->update($validated);

        AuditLog::log('update', "Memperbarui Objek Pajak NOP: {$taxObject->formatted_nop}", $taxObject, $old, $validated);

        return redirect()->route('tax-objects.index')->with('success', "Data Objek Pajak NOP {$taxObject->formatted_nop} berhasil diperbarui.");
    }
}
