@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Identitas Desa & Pengaturan PBB</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Konfigurasi data resmi desa, penandatangan kuitansi, target pendapatan, dan tahun pajak aktif.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <form method="POST" action="{{ route('settings.village.update') }}" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Profil Wilayah -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-800 border-b border-emerald-100 pb-2 mb-4">
                    1. Wilayah Pemerintahan Desa
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Desa / Kelurahan <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $village->name) }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kecamatan <span class="text-rose-500">*</span></label>
                        <input type="text" name="district" value="{{ old('district', $village->district) }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kabupaten / Kota <span class="text-rose-500">*</span></label>
                        <input type="text" name="regency" value="{{ old('regency', $village->regency) }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Provinsi <span class="text-rose-500">*</span></label>
                        <input type="text" name="province" value="{{ old('province', $village->province) }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Pos</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code', $village->postal_code) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Telepon Kantor Desa</label>
                        <input type="text" name="phone" value="{{ old('phone', $village->phone) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Kantor Desa</label>
                        <textarea name="address" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('address', $village->address) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Pimpinan & Penandatangan -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-800 border-b border-emerald-100 pb-2 mb-4">
                    2. Pimpinan & Penandatangan Dokumen
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Kepala Desa / Kuwu</label>
                        <input type="text" name="kades_name" value="{{ old('kades_name', $village->kades_name) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIP / NIAP Kepala Desa</label>
                        <input type="text" name="kades_nip" value="{{ old('kades_nip', $village->kades_nip) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Section 3: Parameter PBB & Rekening Setoran -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-800 border-b border-emerald-100 pb-2 mb-4">
                    3. Parameter PBB-P2 & Rekening Resmi
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Pajak Aktif <span class="text-rose-500">*</span></label>
                        <input type="number" name="active_tax_year" value="{{ old('active_tax_year', $village->active_tax_year) }}" min="2020" max="2099" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-emerald-700">
                        <p class="text-[11px] text-slate-400 mt-1">Tahun pajak default saat kasir melayani loket pembayaran.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Target Penerimaan PBB Desa (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="target_amount" value="{{ old('target_amount', $village->target_amount) }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-slate-900">
                        <p class="text-[11px] text-slate-400 mt-1">Target baku total ketetapan DHKP desa tahun berjalan.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Informasi Rekening / Tujuan Setoran Kas</label>
                        <input type="text" name="bank_account_info" value="{{ old('bank_account_info', $village->bank_account_info) }}" placeholder="Contoh: Bank BJB No. Rek 0012345678901 a/n Kas Desa Sukahaji (PBB)" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
