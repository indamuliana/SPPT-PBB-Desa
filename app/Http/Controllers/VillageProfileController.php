<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Village;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VillageProfileController extends Controller
{
    public function edit(): View
    {
        $village = Village::current();
        return view('settings.village', compact('village'));
    }

    public function update(Request $request): RedirectResponse
    {
        $village = Village::current();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'regency' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'kades_name' => ['nullable', 'string', 'max:255'],
            'kades_nip' => ['nullable', 'string', 'max:50'],
            'active_tax_year' => ['required', 'integer', 'min:2000', 'max:2099'],
            'target_amount' => ['required', 'numeric', 'min:0'],
            'bank_account_info' => ['nullable', 'string'],
        ]);

        $oldValues = $village->only(array_keys($validated));
        $village->update($validated);

        AuditLog::log('update', 'Memperbarui identitas dan pengaturan desa', $village, $oldValues, $validated);

        return redirect()->route('settings.village')->with('success', 'Identitas desa dan pengaturan PBB berhasil disimpan.');
    }
}
