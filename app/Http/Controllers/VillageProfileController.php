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
            'tax_rate_percent' => ['required', 'numeric', 'min:0.001', 'max:100'],
            'bank_account_info' => ['nullable', 'string'],
            'logo_desa' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'logo_kabupaten' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'hero_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
            'remove_logo_desa' => ['nullable'],
            'remove_logo_kabupaten' => ['nullable'],
            'remove_hero_image' => ['nullable'],
        ]);

        $uploadDir = public_path('images/uploads');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // 1. Upload / Remove Logo Desa
        if ($request->filled('remove_logo_desa') && $request->input('remove_logo_desa') == '1') {
            if ($village->logo_path && file_exists(public_path($village->logo_path))) {
                @unlink(public_path($village->logo_path));
            }
            $village->logo_path = null;
        } elseif ($request->hasFile('logo_desa')) {
            if ($village->logo_path && file_exists(public_path($village->logo_path))) {
                @unlink(public_path($village->logo_path));
            }
            $file = $request->file('logo_desa');
            $filename = 'logo_desa_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $village->logo_path = 'images/uploads/' . $filename;
        }

        // 2. Upload / Remove Logo Kabupaten
        if ($request->filled('remove_logo_kabupaten') && $request->input('remove_logo_kabupaten') == '1') {
            if ($village->regency_logo_path && file_exists(public_path($village->regency_logo_path))) {
                @unlink(public_path($village->regency_logo_path));
            }
            $village->regency_logo_path = null;
        } elseif ($request->hasFile('logo_kabupaten')) {
            if ($village->regency_logo_path && file_exists(public_path($village->regency_logo_path))) {
                @unlink(public_path($village->regency_logo_path));
            }
            $file = $request->file('logo_kabupaten');
            $filename = 'logo_kabupaten_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $village->regency_logo_path = 'images/uploads/' . $filename;
        }

        // 3. Upload / Remove Foto Hero Balai/Kantor Desa
        if ($request->filled('remove_hero_image') && $request->input('remove_hero_image') == '1') {
            if ($village->hero_image_path && file_exists(public_path($village->hero_image_path))) {
                @unlink(public_path($village->hero_image_path));
            }
            $village->hero_image_path = null;
        } elseif ($request->hasFile('hero_image')) {
            if ($village->hero_image_path && file_exists(public_path($village->hero_image_path))) {
                @unlink(public_path($village->hero_image_path));
            }
            $file = $request->file('hero_image');
            $filename = 'hero_desa_' . time() . '_' . \Illuminate\Support\Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $village->hero_image_path = 'images/uploads/' . $filename;
        }

        $fillableFields = [
            'name', 'district', 'regency', 'province', 'postal_code',
            'address', 'phone', 'email', 'kades_name', 'kades_nip',
            'active_tax_year', 'target_amount', 'tax_rate_percent', 'bank_account_info',
        ];
        $textData = $request->only($fillableFields);
        $oldValues = $village->only($fillableFields);
        $village->fill($textData);
        $village->save();

        AuditLog::log('update', 'Memperbarui identitas, logo, foto hero, dan pengaturan desa', $village, $oldValues, $textData);

        return redirect()->route('settings.village')->with('success', 'Identitas desa, logo, dan foto hero berhasil disimpan.');
    }
}
