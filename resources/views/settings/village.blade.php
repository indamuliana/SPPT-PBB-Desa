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
        <form method="POST" action="{{ route('settings.village.update') }}" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
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

            <!-- Section 3: Parameter PBB & Tarif Ketetapan -->
            <div>
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-800 border-b border-emerald-100 pb-2 mb-4">
                    3. Parameter PBB-P2 & Tarif Ketetapan
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tarif Pajak PBB-P2 (%) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.001" min="0.001" max="100" name="tax_rate_percent" value="{{ old('tax_rate_percent', $village->tax_rate_percent ?? '0.100') }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-emerald-600">
                        <p class="text-[11px] text-slate-400 mt-1">Tarif resmi SPPT (standar 0.100% atau 0.1%).</p>
                    </div>
                </div>
            <!-- Section 4: Identitas Visual, Logo Resmi & Foto Hero Desa -->
            <div x-data="{
                desaPreview: '{{ $village->logo_url }}',
                regencyPreview: '{{ $village->regency_logo_url }}',
                heroPreview: '{{ $village->hero_image_url }}',
                previewImage(event, target) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => { this[target] = e.target.result; };
                        reader.readAsDataURL(file);
                    }
                }
            }">
                <h2 class="text-xs font-bold uppercase tracking-wider text-emerald-800 border-b border-emerald-100 pb-2 mb-4">
                    4. Identitas Visual, Logo Resmi & Foto Hero Desa
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Card 1: Logo Aplikasi / Desa -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-800">Logo Aplikasi / Desa</label>
                            <span class="text-[10px] text-slate-400">PNG / JPG / SVG (Maks. 2MB)</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-16 rounded-xl border border-slate-200 bg-white flex items-center justify-center p-1.5 shrink-0 overflow-hidden shadow-2xs">
                                <template x-if="desaPreview">
                                    <img :src="desaPreview" alt="Logo Aplikasi" class="max-h-full max-w-full w-auto h-auto object-contain">
                                </template>
                                <template x-if="!desaPreview">
                                    <span class="text-[10px] text-slate-400 font-bold text-center leading-tight">Belum Ada Logo</span>
                                </template>
                            </div>
                            <div class="flex-1 space-y-1.5">
                                <input type="file" name="logo_desa" accept="image/*" @change="previewImage($event, 'desaPreview')" class="block w-full text-xs text-slate-500 file:mr-2.5 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                                @if($village->logo_path)
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-rose-600 hover:text-rose-700">
                                    <input type="checkbox" name="remove_logo_desa" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                    <span>Hapus Logo Aplikasi / Desa Saat Ini</span>
                                </label>
                                @endif
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Ditampilkan sebagai logo aplikasi di sisi kanan kop kuitansi bukti pembayaran PBB, navigasi portal publik, dan dokumen resmi desa.
                        </p>
                        @error('logo_desa')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>

                    <!-- Card 2: Logo Kabupaten -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-800">Logo Pemerintah Kabupaten / Kota</label>
                            <span class="text-[10px] text-slate-400">PNG / JPG / SVG (Maks. 2MB)</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-16 rounded-xl border border-slate-200 bg-white flex items-center justify-center p-1.5 shrink-0 overflow-hidden shadow-2xs">
                                <template x-if="regencyPreview">
                                    <img :src="regencyPreview" alt="Logo Kabupaten" class="max-h-full max-w-full w-auto h-auto object-contain">
                                </template>
                                <template x-if="!regencyPreview">
                                    <span class="text-[10px] text-slate-400 font-bold text-center leading-tight">Belum Ada Logo</span>
                                </template>
                            </div>
                            <div class="flex-1 space-y-1.5">
                                <input type="file" name="logo_kabupaten" accept="image/*" @change="previewImage($event, 'regencyPreview')" class="block w-full text-xs text-slate-500 file:mr-2.5 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                                @if($village->regency_logo_path)
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-rose-600 hover:text-rose-700">
                                    <input type="checkbox" name="remove_logo_kabupaten" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                    <span>Hapus Logo Kabupaten Saat Ini</span>
                                </label>
                                @endif
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Lambang resmi Pemkab/Pemkot untuk sisi kiri kop kuitansi tanda terima sah dan laporan rekonsiliasi kas daerah.
                        </p>
                        @error('logo_kabupaten')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
                    </div>

                    <!-- Card 3: Foto Kantor / Balai Desa (Hero Welcome) -->
                    <div class="md:col-span-2 p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/50 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                            <div>
                                <label class="block text-xs font-bold text-slate-800">Foto Kantor / Balai Desa (Halaman Hero Welcome Publik)</label>
                                <span class="text-[11px] text-slate-500">Foto lanskap gedung kantor desa sebagai latar belakang utama halaman depan publik.</span>
                            </div>
                            <span class="text-[10px] text-slate-400">JPG / PNG / WEBP (Maks. 5MB)</span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="w-full h-44 sm:h-56 rounded-xl border border-slate-200 bg-slate-900 overflow-hidden relative shadow-xs">
                                <img :src="heroPreview" alt="Foto Kantor Desa" class="w-full h-full object-cover object-center">
                                <div class="absolute bottom-2 left-2 px-2.5 py-1 rounded-lg bg-slate-950/70 text-white text-[11px] font-medium backdrop-blur-xs flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $village->hero_image_path ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                    <span>{{ $village->hero_image_path ? 'Foto Kustom Desa Aktif' : 'Foto Default Sistem' }}</span>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
                                <div class="flex-1">
                                    <input type="file" name="hero_image" accept="image/*" @change="previewImage($event, 'heroPreview')" class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                                </div>
                                @if($village->hero_image_path)
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-rose-600 hover:text-rose-700 shrink-0">
                                    <input type="checkbox" name="remove_hero_image" value="1" class="rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                    <span>Kembalikan ke Foto Default Sistem</span>
                                </label>
                                @endif
                            </div>
                        </div>
                        @error('hero_image')<p class="text-xs text-rose-500">{{ $message }}</p>@enderror
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
