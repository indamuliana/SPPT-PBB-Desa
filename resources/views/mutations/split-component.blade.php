@extends('layouts.app')

@section('title', 'Pecah Komponen Objek Pajak - SADAYA PBB')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    selectedTaxObjectId: '{{ old('tax_object_id', $selectedObject->id ?? '') }}',
    objects: {{ Js::from($taxObjects) }},
    splitType: '{{ old('split_component_type', 'bumi') }}',
    splitArea: {{ old('split_area_m2', 0) }},
    getCurrentComponent() {
        const obj = this.objects.find(o => o.id == this.selectedTaxObjectId);
        if (!obj || !obj.components) return null;
        return obj.components.find(c => c.type === this.splitType);
    },
    getMaxArea() {
        const comp = this.getCurrentComponent();
        return comp ? parseFloat(comp.area_m2) : 0;
    },
    getNjopM2() {
        const comp = this.getCurrentComponent();
        return comp ? parseFloat(comp.njop_per_m2) : 0;
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('mutations.index') }}" class="hover:underline">Mutasi & Balik Nama</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Pemecahan Komponen Objek</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pecah / Alihkan Komponen Objek Pajak</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pemecahan sebagian atau seluruh komponen (Bumi, Bangunan, Bumi Bersama, Bangunan Bersama) kepada Wajib Pajak lain.</p>
        </div>
        <a href="{{ route('mutations.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-50 transition">
            Batal
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs space-y-1">
        <p class="font-bold">Terdapat kesalahan:</p>
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('mutations.store-split-component') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Objek Asal & Komponen -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                1. NOP Asal & Komponen yang Dipecah
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih NOP Asal <span class="text-rose-500">*</span></label>
                    <select name="tax_object_id" x-model="selectedTaxObjectId" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">-- Pilih NOP Asal --</option>
                        @foreach($taxObjects as $obj)
                            <option value="{{ $obj->id }}" {{ (old('tax_object_id', $selectedObject->id ?? '') == $obj->id) ? 'selected' : '' }}>
                                {{ $obj->formatted_nop }} - {{ $obj->taxpayer->name }} ({{ $obj->formatted_address }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Komponen yang Dipecah <span class="text-rose-500">*</span></label>
                    <select name="split_component_type" x-model="splitType" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-semibold">
                        <option value="bumi">BUMI (Tanah / Darat)</option>
                        <option value="bangunan">BANGUNAN</option>
                        <option value="bumi_bersama">BUMI BERSAMA</option>
                        <option value="bangunan_bersama">BANGUNAN BERSAMA</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Luas yang Dialihkan (M²) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" :max="getMaxArea()" x-model.number="splitArea" name="split_area_m2" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-emerald-700">
                    <p class="text-[11px] text-slate-400 mt-1">
                        Luas eksisting NOP asal: <strong class="text-slate-700" x-text="getMaxArea() + ' M²'">-</strong>
                    </p>
                </div>
            </div>

            <!-- Preview calculation -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs flex flex-wrap items-center justify-between gap-4" x-show="selectedTaxObjectId">
                <div>
                    <span class="text-slate-500">Estimasi Sisa Luas di NOP Asal:</span>
                    <strong class="text-slate-800 ml-1" x-text="Math.max(0, getMaxArea() - (splitArea || 0)).toFixed(2) + ' M²'">-</strong>
                </div>
                <div>
                    <span class="text-slate-500">NJOP / M²:</span>
                    <strong class="text-slate-800 ml-1" x-text="'Rp ' + Math.round(getNjopM2()).toLocaleString('id-ID')">-</strong>
                </div>
                <div>
                    <span class="text-slate-500">Nilai NJOP yang Dialihkan:</span>
                    <strong class="text-emerald-700 ml-1 font-mono font-bold" x-text="'Rp ' + Math.round((splitArea || 0) * getNjopM2()).toLocaleString('id-ID')">-</strong>
                </div>
            </div>
        </div>

        <!-- 2. Penerima / Wajib Pajak Tujuan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                2. Subjek Pajak Penerima & Objek Tujuan
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Wajib Pajak Baru / Pembeli <span class="text-rose-500">*</span></label>
                    <select name="new_taxpayer_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">-- Pilih Wajib Pajak Pembeli --</option>
                        @foreach($taxpayers as $tp)
                            <option value="{{ $tp->id }}" {{ old('new_taxpayer_id') == $tp->id ? 'selected' : '' }}>
                                {{ $tp->name }} (NIK: {{ $tp->nik ?? '-' }}) - Dusun {{ $tp->dusun ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NOP Tujuan (Opsional)</label>
                    <select name="target_tax_object_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">-- Buat NOP Pecahan Baru Otomatis --</option>
                        @foreach($taxObjects as $obj)
                            <option value="{{ $obj->id }}" {{ old('target_tax_object_id') == $obj->id ? 'selected' : '' }}>
                                {{ $obj->formatted_nop }} - {{ $obj->taxpayer->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika ingin sistem membuat NOP turunan baru secara otomatis.</p>
                </div>
            </div>
        </div>

        <!-- 3. Legalitas & Dasar Pemecahan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-4">
            <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                3. Dasar Hukum & Dokumen Pendukung
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Dokumen Hukum <span class="text-rose-500">*</span></label>
                    <select name="legal_doc_type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="AJB" {{ old('legal_doc_type') == 'AJB' ? 'selected' : '' }}>Akta Jual Beli (AJB)</option>
                        <option value="SHM" {{ old('legal_doc_type') == 'SHM' ? 'selected' : '' }}>Sertifikat Hak Milik (SHM)</option>
                        <option value="Surat Hibah" {{ old('legal_doc_type') == 'Surat Hibah' ? 'selected' : '' }}>Surat Keterangan Hibah</option>
                        <option value="Keterangan Waris" {{ old('legal_doc_type') == 'Keterangan Waris' ? 'selected' : '' }}>Surat Keterangan Waris</option>
                        <option value="Lainnya" {{ old('legal_doc_type') == 'Lainnya' ? 'selected' : '' }}>Lainnya / Surat Desa</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Dokumen</label>
                    <input type="text" name="legal_doc_number" value="{{ old('legal_doc_number') }}" placeholder="Contoh: 123/AJB/2026" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pemecahan / Keterangan <span class="text-rose-500">*</span></label>
                    <textarea name="reason" rows="2" required placeholder="Contoh: Dijual sebagian tanah seluas 200 M² kepada Wajib Pajak baru berdasarkan AJB No. 123." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('reason') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Ajukan Pemecahan Komponen</span>
            </button>
        </div>
    </form>
</div>
@endsection
