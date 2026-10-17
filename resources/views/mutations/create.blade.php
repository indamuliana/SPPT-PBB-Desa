@extends('layouts.app')

@section('title', 'Ajukan Mutasi NOP - SADAYA PBB')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('mutations.index') }}" class="hover:underline">Mutasi & Balik Nama</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Formulir Pengajuan Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Ajukan Mutasi Objek Pajak</h1>
            <p class="text-sm text-slate-600 mt-1">Balik nama subjek pajak atau penyesuaian administratif berdasarkan dokumen hukum sah</p>
        </div>
        <a href="{{ route('mutations.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
            Batal
        </a>
    </div>

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg">
        <div class="flex items-center gap-2 font-semibold text-sm mb-1">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Terdapat kendala pengisian mutasi:</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Linked TO Banner if applicable -->
    @if($selectedToCase)
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-center justify-between gap-3 text-amber-900 text-xs">
        <div class="flex items-center gap-2 font-medium">
            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Mutasi ini diajukan untuk menyelesaikan <strong>Kasus TO #{{ $selectedToCase->case_number }}</strong>. Setelah disahkan Admin, status TO akan otomatis selesai.</span>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('mutations.store') }}" method="POST" class="space-y-6">
            @csrf

            @if($selectedToCase)
                <input type="hidden" name="to_case_id" value="{{ $selectedToCase->id }}">
            @endif

            <!-- Grid NOP & Jenis Mutasi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor Objek Pajak (NOP) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nop" required 
                           value="{{ old('nop', $selectedObject ? $selectedObject->formatted_nop : '') }}"
                           placeholder="Contoh: 32.12.050.001.001-0001.0"
                           class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    @if($selectedObject)
                        <div class="mt-2 p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs space-y-0.5">
                            <span class="text-slate-500 block">Pemilik Tercatat Saat Ini:</span>
                            <span class="font-bold text-slate-800">{{ $selectedObject->taxpayer->name }}</span>
                        </div>
                    @endif
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Jenis Mutasi <span class="text-rose-500">*</span>
                    </label>
                    <select name="mutation_type" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        <option value="balik_nama" {{ old('mutation_type') == 'balik_nama' ? 'selected' : '' }}>Balik Nama Penuh (Jual Beli / Hibah / Waris)</option>
                        <option value="pemecahan" {{ old('mutation_type') == 'pemecahan' ? 'selected' : '' }}>Pemecahan Objek Pajak</option>
                        <option value="penggabungan" {{ old('mutation_type') == 'penggabungan' ? 'selected' : '' }}>Penggabungan Objek Pajak</option>
                        <option value="koreksi_data" {{ old('mutation_type') == 'koreksi_data' ? 'selected' : '' }}>Koreksi Data Subjek / Luas</option>
                    </select>
                </div>
            </div>

            <!-- Pilih Wajib Pajak Penerima Baru -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Wajib Pajak Baru (Penerima Hak) <span class="text-rose-500">*</span>
                    </label>
                    <a href="{{ route('taxpayers.create') }}" target="_blank" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold underline">
                        + Daftarkan WP Baru di Tab Baru
                    </a>
                </div>
                <select name="new_taxpayer_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                    <option value="">-- Pilih Wajib Pajak Penerima --</option>
                    @foreach($taxpayers as $tp)
                        <option value="{{ $tp->id }}" {{ old('new_taxpayer_id') == $tp->id ? 'selected' : '' }}>
                            {{ $tp->name }} {{ $tp->nik ? '(NIK: '.$tp->nik.')' : '' }} - {{ $tp->address ?? 'Desa Sukahaji' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Pilih data Wajib Pajak yang telah terdaftar di master data desa.</p>
            </div>

            <!-- Dokumen Dasar Hukum -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Dasar Dokumen Kepemilikan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="legal_doc_type" required list="doc_suggestions" 
                           value="{{ old('legal_doc_type', 'Akta Jual Beli (AJB)') }}"
                           placeholder="Contoh: Akta Jual Beli (AJB)"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">
                    <datalist id="doc_suggestions">
                        <option value="Akta Jual Beli (AJB)">
                        <option value="Akta Hibah (PPAT/Notaris)">
                        <option value="Surat Keterangan Waris (Desa/Kecamatan)">
                        <option value="Sertifikat Hak Milik (SHM/BPN)">
                        <option value="Surat Pengakuan Hak (SPH)">
                        <option value="Surat Keterangan Riwayat Tanah Desa">
                    </datalist>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nomor Dokumen / Akta (Opsional)
                    </label>
                    <input type="text" name="legal_doc_number" value="{{ old('legal_doc_number') }}"
                           placeholder="Contoh: 042/AJB/SKH/2026"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 font-mono">
                </div>
            </div>

            <!-- Alasan / Uraian Mutasi -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Alasan / Keterangan Mutasi <span class="text-rose-500">*</span>
                </label>
                <textarea name="reason" required rows="3" 
                          placeholder="Jelaskan dasar permohonan mutasi, misalnya: Pembelian sebidang tanah darat seluas 250 m2 dari Bpk. X kepada Bpk. Y sesuai akta nomor..."
                          class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500">{{ old('reason') }}</textarea>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('mutations.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan & Ajukan ke Admin Desa</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
