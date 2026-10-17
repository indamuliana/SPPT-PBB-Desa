@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Import Berkas DHKP (Excel / CSV)</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Unggah berkas Daftar Himpunan Ketetapan Pajak (DHKP) dari Bapenda Kabupaten untuk sinkronisasi massal.</p>
        </div>
        <a href="{{ route('sppts.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; Kembali
        </a>
    </div>

    <!-- Upload Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('sppts.import.process') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Pajak Ketetapan <span class="text-rose-500">*</span></label>
                <input type="number" name="tax_year" value="{{ old('tax_year', $village->active_tax_year) }}" required min="2000" max="2099" class="w-full sm:w-48 px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-emerald-700">
                <p class="text-[11px] text-slate-400 mt-1">Jika di dalam berkas terdapat kolom tahun, kolom tersebut akan diprioritaskan.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Berkas DHKP (.xlsx atau .csv) <span class="text-rose-500">*</span></label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-2xl hover:border-emerald-500 transition bg-slate-50/50">
                    <div class="space-y-2 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-xs text-slate-600 justify-center">
                            <label for="dhkp_file" class="relative cursor-pointer bg-white rounded-lg font-semibold text-emerald-600 hover:text-emerald-500 px-3 py-1 border border-slate-200 shadow-xs">
                                <span>Pilih Berkas dari Komputer</span>
                                <input id="dhkp_file" name="dhkp_file" type="file" accept=".xlsx,.csv" required class="sr-only" onchange="document.getElementById('filename-display').innerText = this.files[0] ? this.files[0].name : '';">
                            </label>
                        </div>
                        <p id="filename-display" class="text-xs font-semibold text-slate-800 font-mono"></p>
                        <p class="text-[11px] text-slate-400">Mendukung format Microsoft Excel (.xlsx) atau Comma Separated Values (.csv) maksimal 20 MB.</p>
                    </div>
                </div>
                @error('dhkp_file')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Format Info Guide -->
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-xs space-y-2">
                <h3 class="font-bold text-slate-800 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Standar Nama Kolom Header Berkas:</span>
                </h3>
                <p class="text-slate-600">Sistem otomatis mengenali variasi nama kolom pada baris pertama berkas:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 font-mono text-[11px] text-slate-700">
                    <div class="p-2 bg-white rounded border border-slate-200">
                        <strong class="text-emerald-700 block">nop</strong> (Nomor Objek Pajak)
                    </div>
                    <div class="p-2 bg-white rounded border border-slate-200">
                        <strong class="text-emerald-700 block">nama_wp</strong> (Nama Wajib Pajak)
                    </div>
                    <div class="p-2 bg-white rounded border border-slate-200">
                        <strong class="text-emerald-700 block">alamat_op</strong> (Letak Objek Pajak)
                    </div>
                    <div class="p-2 bg-white rounded border border-slate-200">
                        <strong class="text-emerald-700 block">dusun</strong> (Nama Dusun)
                    </div>
                    <div class="p-2 bg-white rounded border border-slate-200">
                        <strong class="text-emerald-700 block">rt / rw</strong> (RT dan RW)
                    </div>
                    <div class="p-2 bg-white rounded border border-slate-200">
                        <strong class="text-emerald-700 block">pbb_terutang</strong> (Nominal Tagihan)
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('sppts.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold shadow-md shadow-teal-600/20 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Mulai Proses Import DHKP</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
