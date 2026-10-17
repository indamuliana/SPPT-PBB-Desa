@extends('layouts.app')

@section('title', 'Tandai Objek sebagai TO - SADAYA PBB')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('to-cases.index') }}" class="hover:underline">Kasus TO</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Lapor Kasus Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Tandai Objek Pajak Sebagai TO</h1>
            <p class="text-sm text-slate-600 mt-1">Mengunci NOP dari pembayaran kasir dan menugaskan penelusuran pemilik sebenarnya</p>
        </div>
        <a href="{{ route('to-cases.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
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
            <span>Terdapat kendala pengisian formulir:</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('to-cases.store') }}" method="POST" class="space-y-6">
            @csrf

            @if($selectedSppt)
                <input type="hidden" name="sppt_id" value="{{ $selectedSppt->id }}">
            @endif

            <!-- Input NOP -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nomor Objek Pajak (NOP) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nop" required 
                       value="{{ old('nop', $selectedObject ? $selectedObject->formatted_nop : '') }}"
                       placeholder="Contoh: 32.12.050.001.001-0001.0"
                       class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                @if($selectedObject)
                    <div class="mt-2 p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs space-y-0.5">
                        <p class="font-bold text-slate-800">Subjek Terdaftar: {{ $selectedObject->taxpayer->name }}</p>
                        <p class="text-slate-600">Alamat Objek: {{ $selectedObject->formatted_address }}</p>
                    </div>
                @else
                    <p class="text-[11px] text-slate-500 mt-1">Masukkan 18 digit NOP atau format resmi berspasi/titik.</p>
                @endif
            </div>

            <!-- Kategori Alasan TO -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Kategori Kendala / Alasan TO <span class="text-rose-500">*</span>
                </label>
                <select name="reason_type" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500 bg-white">
                    <option value="">-- Pilih Kategori Alasan TO --</option>
                    <option value="pemilik_tidak_dikenal" {{ old('reason_type') == 'pemilik_tidak_dikenal' ? 'selected' : '' }}>Subjek Pajak Tidak Dikenal / Tidak Ditemukan</option>
                    <option value="pindah_tanpa_kabar" {{ old('reason_type') == 'pindah_tanpa_kabar' ? 'selected' : '' }}>Pemilik Pindah Keluar Kota Tanpa Alamat Jelas</option>
                    <option value="sengketa" {{ old('reason_type') == 'sengketa' ? 'selected' : '' }}>Lahan Sengketa Kepemilikan / Waris</option>
                    <option value="objek_tidak_ada" {{ old('reason_type') == 'objek_tidak_ada' ? 'selected' : '' }}>Fisik Tanah / Bangunan Tidak Ditemukan</option>
                    <option value="terjual_belum_mutasi" {{ old('reason_type') == 'terjual_belum_mutasi' ? 'selected' : '' }}>Sudah Dijual tapi Belum Balik Nama Resmi</option>
                    <option value="lainnya" {{ old('reason_type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <!-- Petugas Kolektor Penanggung Jawab -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Tugaskan Penelusuran Lapangan ke Kolektor (Opsional)
                </label>
                <select name="assigned_to_user_id" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500 bg-white">
                    <option value="">-- Tanpa Penugasan Khusus --</option>
                    @foreach($collectors as $col)
                        <option value="{{ $col->id }}" {{ old('assigned_to_user_id') == $col->id ? 'selected' : '' }}>
                            {{ $col->name }} ({{ $col->username }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Petugas terpilih akan melakukan investigasi ke lapangan untuk mencari pemilik baru/ahli waris.</p>
            </div>

            <!-- Deskripsi Masalah Lapangan -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Uraian Detail Masalah Lapangan <span class="text-rose-500">*</span>
                </label>
                <textarea name="description" required rows="4" 
                          placeholder="Jelaskan kondisi riil di lapangan, misalnya: Rumah kosong sejak tahun 2020, tetangga tidak mengetahui kontak pemilik, atau riwayat jual beli yang belum rampung..."
                          class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500">{{ old('description') }}</textarea>
            </div>

            <!-- Warning Notice -->
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-xs flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <span class="font-bold block">Pemberitahuan Sistem:</span>
                    NOP yang ditandai TO tidak akan dapat ditransaksikan pada loket kasir hingga kasus diselesaikan dan disahkan mutasinya oleh Administrator Desa.
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('to-cases.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-bold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Kunci NOP & Buat Kasus TO</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
