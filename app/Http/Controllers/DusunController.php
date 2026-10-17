<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dusun;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DusunController extends Controller
{
    public function index(): View
    {
        $dusuns = Dusun::with(['users' => function ($q) {
            $q->where('role', 'kadus');
        }])
        ->withCount(['taxObjects', 'sppts'])
        ->orderBy('name')
        ->paginate(15);

        $allDusuns = Dusun::orderBy('name')->get();

        return view('dusuns.index', compact('dusuns', 'allDusuns'));
    }

    public function create(): View
    {
        $kadusUsers = User::where('role', 'kadus')->where('is_active', true)->orderBy('name')->get();
        return view('dusuns.create', compact('kadusUsers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:dusuns,name'],
            'code' => ['nullable', 'string', 'max:20'],
            'rw' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $dusun = Dusun::create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'rw' => $validated['rw'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['user_ids'])) {
            $dusun->users()->sync($validated['user_ids']);
        }

        AuditLog::log('create', "Menambahkan Master Dusun baru: {$dusun->name} (" . ($dusun->code ?: 'Tanpa Kode') . ")", $dusun);

        return redirect()->route('dusuns.index')->with('success', "Master Dusun {$dusun->name} berhasil ditambahkan.");
    }

    public function edit(Dusun $dusun): View
    {
        $dusun->load('users');
        $kadusUsers = User::where('role', 'kadus')->where('is_active', true)->orderBy('name')->get();

        return view('dusuns.edit', compact('dusun', 'kadusUsers'));
    }

    public function update(Request $request, Dusun $dusun): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('dusuns', 'name')->ignore($dusun->id)],
            'code' => ['nullable', 'string', 'max:20'],
            'rw' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $old = $dusun->only(['name', 'code', 'rw', 'description']);

        $dusun->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'rw' => $validated['rw'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        $dusun->users()->sync($validated['user_ids'] ?? []);

        AuditLog::log('update', "Memperbarui Master Dusun: {$dusun->name}", $dusun, $old, $validated);

        return redirect()->route('dusuns.index')->with('success', "Data Dusun {$dusun->name} berhasil diperbarui.");
    }

    public function destroy(Request $request, Dusun $dusun): RedirectResponse
    {
        $taxObjectsCount = $dusun->taxObjects()->count();
        $dusunName = $dusun->name;

        if ($taxObjectsCount > 0) {
            $action = $request->input('action_on_objects');

            if ($action === 'reassign') {
                $targetDusunId = $request->input('target_dusun_id');
                $targetDusun = Dusun::where('id', '!=', $dusun->id)->find($targetDusunId);

                if (! $targetDusun) {
                    return back()->with('error', 'Silakan pilih Dusun pengganti yang valid untuk memindahkan data Objek Pajak.');
                }

                \Illuminate\Support\Facades\DB::transaction(function () use ($dusun, $targetDusun, $taxObjectsCount, $dusunName) {
                    \App\Models\TaxObject::where('dusun_id', $dusun->id)
                        ->orWhere('dusun', $dusun->name)
                        ->update([
                            'dusun_id' => $targetDusun->id,
                            'dusun' => $targetDusun->name,
                        ]);

                    $dusun->users()->detach();
                    $dusun->delete();

                    AuditLog::log('delete', "Menghapus Master Dusun: {$dusunName} dan mengalihkan {$taxObjectsCount} NOP ke {$targetDusun->name}", null);
                });

                return redirect()->route('dusuns.index')->with('success', "Master Dusun {$dusunName} berhasil dihapus. {$taxObjectsCount} Objek Pajak telah dialihkan ke Dusun {$targetDusun->name}.");
            } elseif ($action === 'detach') {
                \Illuminate\Support\Facades\DB::transaction(function () use ($dusun, $taxObjectsCount, $dusunName) {
                    \App\Models\TaxObject::where('dusun_id', $dusun->id)
                        ->orWhere('dusun', $dusun->name)
                        ->update([
                            'dusun_id' => null,
                            'dusun' => null,
                        ]);

                    $dusun->users()->detach();
                    $dusun->delete();

                    AuditLog::log('delete', "Menghapus Master Dusun: {$dusunName} dan melepaskan kaitan {$taxObjectsCount} NOP", null);
                });

                return redirect()->route('dusuns.index')->with('success', "Master Dusun {$dusunName} berhasil dihapus. {$taxObjectsCount} Objek Pajak kini berstatus tanpa dusun.");
            } else {
                return back()->with('error', "Dusun {$dusun->name} tidak dapat dihapus karena masih terkait dengan {$taxObjectsCount} Objek Pajak (NOP). Silakan pilih opsi pindahkan atau sesuaikan objek pajak terlebih dahulu.");
            }
        }

        $dusun->users()->detach();
        $dusun->delete();

        AuditLog::log('delete', "Menghapus Master Dusun: {$dusunName}", null);

        return redirect()->route('dusuns.index')->with('success', "Master Dusun {$dusunName} berhasil dihapus.");
    }
}
