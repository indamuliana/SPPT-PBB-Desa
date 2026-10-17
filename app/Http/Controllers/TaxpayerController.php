<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Taxpayer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaxpayerController extends Controller
{
    public function index(Request $request): View
    {
        $query = Taxpayer::withCount('taxObjects');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('dusun', 'like', "%{$search}%");
            });
        }

        if ($request->filled('dusun')) {
            $query->where('dusun', $request->dusun);
        }

        $taxpayers = $query->latest('id')->paginate(15)->withQueryString();

        $dusunList = Taxpayer::whereNotNull('dusun')->distinct()->pluck('dusun');

        return view('taxpayers.index', compact('taxpayers', 'dusunList'));
    }

    public function create(): View
    {
        return view('taxpayers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:16'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'dusun' => ['nullable', 'string', 'max:50'],
            'village_name' => ['nullable', 'string', 'max:100'],
            'district_name' => ['nullable', 'string', 'max:100'],
            'regency_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $taxpayer = Taxpayer::create($validated);

        AuditLog::log('create', "Menambahkan Wajib Pajak: {$taxpayer->name}", $taxpayer);

        return redirect()->route('taxpayers.index')->with('success', "Wajib Pajak {$taxpayer->name} berhasil ditambahkan.");
    }

    public function edit(Taxpayer $taxpayer): View
    {
        return view('taxpayers.edit', compact('taxpayer'));
    }

    public function update(Request $request, Taxpayer $taxpayer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'string', 'max:16'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'dusun' => ['nullable', 'string', 'max:50'],
            'village_name' => ['nullable', 'string', 'max:100'],
            'district_name' => ['nullable', 'string', 'max:100'],
            'regency_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $old = $taxpayer->only(array_keys($validated));
        $taxpayer->update($validated);

        AuditLog::log('update', "Memperbarui Wajib Pajak: {$taxpayer->name}", $taxpayer, $old, $validated);

        return redirect()->route('taxpayers.index')->with('success', "Data Wajib Pajak {$taxpayer->name} berhasil diperbarui.");
    }
}
