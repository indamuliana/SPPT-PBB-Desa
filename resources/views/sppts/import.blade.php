@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ mode: '{{ $initialMode ?? 'file' }}' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-[11px] font-semibold text-emerald-700 mb-2">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Modul Integrasi & Sinkronisasi DHKP</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Import & Sinkronisasi SPPT / DHKP</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Sinkronisasi data ketetapan PBB-P2 dari Bapenda Kabupaten atau salin data dari tahun pajak sebelumnya secara cepat, aman, dan terpadu.</p>
        </div>
        <a href="{{ route('sppts.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 px-3.5 py-2 rounded-xl bg-white border border-slate-200 shadow-2xs hover:bg-slate-50 transition self-start sm:self-auto">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Daftar SPPT</span>
        </a>
    </div>

    <!-- Warnings / Errors Notice if any -->
    @if(session('import_warnings'))
    <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800 space-y-2">
        <p class="font-bold flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Catatan Hasil Import Terakhir:</span>
        </p>
        <ul class="list-disc list-inside space-y-1 font-mono text-[11px] text-amber-700">
            @foreach(session('import_warnings') as $warn)
            <li>{{ $warn }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Mode Tab Navigation -->
    <div class="flex items-center gap-2 border-b border-slate-200">
        <button type="button" @click="mode = 'file'" :class="mode === 'file' ? 'text-emerald-700 border-b-2 border-emerald-600 font-bold bg-emerald-50/50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50 font-semibold'" class="px-4 py-3 rounded-t-xl transition text-xs sm:text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Import Berkas Excel / CSV</span>
        </button>

        <button type="button" @click="mode = 'previous_year'" :class="mode === 'previous_year' ? 'text-indigo-700 border-b-2 border-indigo-600 font-bold bg-indigo-50/50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50 font-semibold'" class="px-4 py-3 rounded-t-xl transition text-xs sm:text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            <span>Salin / Import dari Tahun Sebelumnya</span>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800">Antartahun</span>
        </button>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: IMPORT FROM EXCEL / CSV FILE       -->
    <!-- ========================================== -->
    <div x-show="mode === 'file'" class="space-y-6">
        <!-- STEP 1: Download Official Template -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-sm flex items-center justify-center shrink-0">1</span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Unduh Format Template Resmi</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Template telah disesuaikan 100% dengan skema database aplikasi (termasuk 4 komponen objek pajak: Bumi, Bangunan, Bumi Bersama, Bangunan Bersama, dan NIK Wajib Pajak).</p>
                    </div>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-[11px] font-semibold text-emerald-700 border border-emerald-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Siap Pakai</span>
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <!-- Excel Template Card -->
                <div class="p-4 rounded-xl border border-emerald-200 bg-linear-to-br from-emerald-50/60 to-white flex flex-col justify-between gap-3 hover:border-emerald-300 transition">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-emerald-600/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-800">Template Microsoft Excel (.xlsx)</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Direkomendasikan</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Lengkap dengan format teks NOP, sel berwarna hijau, dan 3 baris data contoh realistis.</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-emerald-100">
                        <span class="text-[11px] text-slate-400 font-mono">Format: .xlsx</span>
                        <a id="btn-download-xlsx" href="{{ route('sppts.import.template', ['format' => 'xlsx', 'tax_year' => $village->active_tax_year]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh Excel (.xlsx)</span>
                        </a>
                    </div>
                </div>

                <!-- CSV Template Card -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 flex flex-col justify-between gap-3 hover:border-slate-300 transition">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-700 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-800">Template Comma Separated (.csv)</h3>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">Teks Standar</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Format teks ringan dipisahkan koma, cocok untuk integrasi ekspor sistem atau data besar.</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-200">
                        <span class="text-[11px] text-slate-400 font-mono">Format: .csv</span>
                        <a id="btn-download-csv" href="{{ route('sppts.import.template', ['format' => 'csv', 'tax_year' => $village->active_tax_year]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh CSV (.csv)</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: Upload and Process Form -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-6">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-teal-100 text-teal-800 font-bold text-sm flex items-center justify-center shrink-0">2</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Unggah & Mulai Proses Sinkronisasi Berkas</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih tahun pajak ketetapan dan unggah berkas DHKP yang telah diisi sesuai template.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('sppts.import.process') }}" enctype="multipart/form-data" class="space-y-6" id="import-form">
                @csrf

                <!-- Tax Year Selection -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <label for="tax_year" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                            Tahun Pajak Ketetapan <span class="text-rose-500">*</span>
                        </label>
                        <p class="text-xs text-slate-500">Tahun pajak default untuk semua ketetapan baru. Jika pada berkas ada kolom <code class="text-emerald-700 bg-white px-1 py-0.5 rounded border border-slate-200">TAHUN_PAJAK</code>, nilai pada berkas akan diprioritaskan.</p>
                    </div>
                    <div class="w-full sm:w-44 shrink-0">
                        <input type="number" id="tax_year" name="tax_year" value="{{ old('tax_year', $village->active_tax_year) }}" required min="2000" max="2099" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-emerald-700 bg-white shadow-2xs text-center" oninput="updateDownloadYear(this.value)">
                    </div>
                </div>

                <!-- File Upload Dropzone -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">Berkas Excel (.xlsx) atau CSV (.csv) <span class="text-rose-500">*</span></label>
                    <div id="dropzone" class="relative border-2 border-dashed border-slate-300 rounded-2xl p-8 hover:border-emerald-500 bg-slate-50/50 hover:bg-emerald-50/20 transition-all text-center cursor-pointer">
                        <input id="dhkp_file" name="dhkp_file" type="file" accept=".xlsx,.csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="handleFileSelect(this)">
                        
                        <div class="space-y-3 pointer-events-none">
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 shadow-2xs group-hover:text-emerald-600">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div>
                                <p class="text-xs sm:text-sm font-bold text-slate-700">Tarik berkas ke sini, atau <span class="text-emerald-600 underline">klik untuk memilih dari komputer</span></p>
                                <p class="text-[11px] text-slate-400 mt-1">Mendukung berkas Microsoft Excel (.xlsx) atau CSV (.csv) berukuran maksimal 20 MB.</p>
                            </div>

                            <!-- Selected File Preview Badge -->
                            <div id="file-preview" class="hidden inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white border border-emerald-300 text-xs font-semibold text-slate-800 shadow-2xs">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span id="file-name" class="font-mono text-emerald-700"></span>
                                <span id="file-size" class="text-slate-400 text-[11px]"></span>
                            </div>
                        </div>
                    </div>
                    @error('dhkp_file')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <!-- Safety & Integrity Badges -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-100 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <div>
                            <strong class="font-bold text-emerald-900 block">Perlindungan Saldo Kasir:</strong>
                            <p class="text-emerald-800 text-[11px] mt-0.5">Jika SPPT sudah memiliki pembayaran cicilan/lunas di kasir, nominal tagihan tidak akan dikurangi di bawah nilai yang telah disetorkan warga untuk mencegah selisih kas.</p>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-blue-50/60 border border-blue-100 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="font-bold text-blue-900 block">Pemetaan Otomatis Dusun & Kadus:</strong>
                            <p class="text-blue-800 text-[11px] mt-0.5">Nama dusun pada berkas otomatis dihubungkan ke data master Dusun Desa {{ $village->name }} sehingga filter penagihan Kepala Dusun langsung siap.</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <span class="text-xs text-slate-400">Proses impor akan mengeksekusi sinkronisasi transaksi secara atomik dan aman.</span>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="{{ route('sppts.index') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition text-center">
                            Batal
                        </a>
                        <button type="submit" id="btn-submit-import" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Mulai Proses Import DHKP</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Master Dusun Quick Reference -->
        @if(isset($dusuns) && $dusuns->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Daftar Nama Dusun Terdaftar di {{ $village->name }}:</span>
                </h3>
                <span class="text-[11px] text-slate-400">Salin nama dusun persis agar terhubung otomatis</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($dusuns as $d)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>{{ $d->name }}</span>
                    @if($d->code)
                    <span class="text-[10px] text-slate-400 font-mono">({{ $d->code }})</span>
                    @endif
                </span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- STEP 3: Data Dictionary / Kamus Kolom -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 font-bold text-sm flex items-center justify-center shrink-0">3</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Kamus Kolom Berkas DHKP & Aturan Pengisian</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Sistem membaca baris pertama berkas secara fleksibel (tidak terpengaruh huruf besar/kecil, spasi, atau tanda garis bawah).</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold">
                        <tr>
                            <th class="py-3 px-3.5">Nama Kolom</th>
                            <th class="py-3 px-3.5">Status</th>
                            <th class="py-3 px-3.5">Tipe & Aturan</th>
                            <th class="py-3 px-3.5">Penjelasan Skema</th>
                            <th class="py-3 px-3.5">Contoh Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">NOP</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">Wajib</span></td>
                            <td class="py-2.5 px-3.5">Angka 18 digit</td>
                            <td class="py-2.5 px-3.5">Nomor Objek Pajak resmi. Tanda titik atau strip otomatis dibersihkan.</td>
                            <td class="py-2.5 px-3.5 font-mono text-[11px] text-slate-800">321114000100100010</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">NAMA_WP</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">Wajib</span></td>
                            <td class="py-2.5 px-3.5">Teks</td>
                            <td class="py-2.5 px-3.5">Nama lengkap pemilik objek pajak atau pihak yang bertanggung jawab.</td>
                            <td class="py-2.5 px-3.5 font-medium text-slate-800">H. AHMAD SUBAGJA</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">NIK_WP</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Opsional</span></td>
                            <td class="py-2.5 px-3.5">Angka 16 digit</td>
                            <td class="py-2.5 px-3.5">Nomor Induk Kependudukan KTP Wajib Pajak untuk pencocokan data warga desa.</td>
                            <td class="py-2.5 px-3.5 font-mono text-[11px] text-slate-800">3211140105820001</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">ALAMAT_WP</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Opsional</span></td>
                            <td class="py-2.5 px-3.5">Teks</td>
                            <td class="py-2.5 px-3.5">Alamat domisili atau tempat tinggal Wajib Pajak.</td>
                            <td class="py-2.5 px-3.5 text-slate-800">Dusun Babakan RT 01 RW 01</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">ALAMAT_OP</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700">Dianjurkan</span></td>
                            <td class="py-2.5 px-3.5">Teks</td>
                            <td class="py-2.5 px-3.5">Letak atau alamat lokasi fisik tanah/bangunan objek pajak.</td>
                            <td class="py-2.5 px-3.5 text-slate-800">Jl. Desa Sukahaji Blok Babakan</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">RT / RW</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Opsional</span></td>
                            <td class="py-2.5 px-3.5">Maks 5 digit</td>
                            <td class="py-2.5 px-3.5">Nomor Rukun Tetangga dan Rukun Warga lokasi objek.</td>
                            <td class="py-2.5 px-3.5 font-mono text-slate-800">01 / 01</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">DUSUN</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700">Dianjurkan</span></td>
                            <td class="py-2.5 px-3.5">Teks</td>
                            <td class="py-2.5 px-3.5">Nama dusun objek pajak. Otomatis terhubung dengan master Dusun desa.</td>
                            <td class="py-2.5 px-3.5 font-semibold text-slate-800">Dusun I Babakan</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">LUAS_BUMI</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Opsional</span></td>
                            <td class="py-2.5 px-3.5">Desimal / m²</td>
                            <td class="py-2.5 px-3.5">Komponen 1: Luas tanah pokok dalam satuan meter persegi.</td>
                            <td class="py-2.5 px-3.5 font-mono text-slate-800">350</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">LUAS_BANGUNAN</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Opsional</span></td>
                            <td class="py-2.5 px-3.5">Desimal / m²</td>
                            <td class="py-2.5 px-3.5">Komponen 2: Luas bangunan pokok dalam satuan meter persegi (0 jika tanah kosong).</td>
                            <td class="py-2.5 px-3.5 font-mono text-slate-800">90</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">LUAS_BUMI_BERSAMA</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Opsional</span></td>
                            <td class="py-2.5 px-3.5">Desimal / m²</td>
                            <td class="py-2.5 px-3.5">Komponen 3: Bagian luas tanah bersama (apartemen / ruko bersama / perumahan).</td>
                            <td class="py-2.5 px-3.5 font-mono text-slate-800">0</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">LUAS_BANGUNAN_BERSAMA</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Opsional</span></td>
                            <td class="py-2.5 px-3.5">Desimal / m²</td>
                            <td class="py-2.5 px-3.5">Komponen 4: Bagian luas bangunan bersama jika ada.</td>
                            <td class="py-2.5 px-3.5 font-mono text-slate-800">0</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">PBB_TERUTANG</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">Wajib</span></td>
                            <td class="py-2.5 px-3.5">Nominal (Rupiah)</td>
                            <td class="py-2.5 px-3.5">Pokok ketetapan PBB-P2 resmi dari Bapenda Kabupaten.</td>
                            <td class="py-2.5 px-3.5 font-mono font-bold text-slate-900">135000</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">NO_URUT_DHKP</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Opsional</span></td>
                            <td class="py-2.5 px-3.5">Teks</td>
                            <td class="py-2.5 px-3.5">Referensi nomor urut atau nomor buku halaman pada lembar DHKP Bapenda.</td>
                            <td class="py-2.5 px-3.5 font-mono text-slate-800">0001</td>
                        </tr>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-2.5 px-3.5 font-mono font-bold text-emerald-700">TAHUN_PAJAK</td>
                            <td class="py-2.5 px-3.5"><span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Opsional</span></td>
                            <td class="py-2.5 px-3.5">Angka 4 digit</td>
                            <td class="py-2.5 px-3.5">Tahun ketetapan SPPT (jika dikosongkan akan memakai tahun pada formulir).</td>
                            <td class="py-2.5 px-3.5 font-mono text-slate-800">2026</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- TAB 2: IMPORT / SALIN DARI TAHUN SEBELUMNYA (ROLLOVER)  -->
    <!-- ======================================================= -->
    <div x-show="mode === 'previous_year'" class="space-y-6" style="display: none;">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 sm:p-8 space-y-6">
            <div class="flex items-start gap-3.5 pb-5 border-b border-slate-200">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-700 flex items-center justify-center shrink-0 shadow-2xs">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Salin & Replikasi SPPT dari Tahun Pajak Sebelumnya</h2>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed max-w-3xl">
                        Fitur ini digunakan saat pergantian tahun pajak (misalnya dari 2025 ke 2026 atau 2026 ke 2027) untuk menyalin data ketetapan SPPT, Wajib Pajak, dan 4 komponen fisik tanah/bangunan aktif secara otomatis tanpa perlu mengunggah berkas eksternal.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('sppts.import.previous-year') }}" class="space-y-6" id="rollover-form">
                @csrf

                <!-- Year Settings Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 rounded-2xl bg-slate-50/80 border border-slate-200">
                    <div>
                        <label for="source_year" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Tahun Pajak Sumber (Asal Data) <span class="text-rose-500">*</span>
                        </label>
                        <select id="source_year" name="source_year" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold text-slate-800 bg-white focus:ring-2 focus:ring-indigo-500 outline-none shadow-2xs">
                            @if(isset($availableSourceYears) && $availableSourceYears->count() > 0)
                                @foreach($availableSourceYears as $yr)
                                    <option value="{{ $yr->tax_year }}" {{ old('source_year', $village->active_tax_year) == $yr->tax_year ? 'selected' : '' }}>
                                        Tahun {{ $yr->tax_year }} ({{ number_format($yr->sppt_count, 0, ',', '.') }} SPPT Terdaftar)
                                    </option>
                                @endforeach
                            @else
                                <option value="{{ $village->active_tax_year }}">{{ $village->active_tax_year }}</option>
                            @endif
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Pilih tahun pajak yang datanya akan disalin.</p>
                    </div>

                    <div>
                        <label for="target_year" class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Tahun Pajak Tujuan (Target Salinan) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" id="target_year" name="target_year" value="{{ old('target_year', $village->active_tax_year + 1) }}" required min="2000" max="2099" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-bold text-indigo-700 bg-white focus:ring-2 focus:ring-indigo-500 outline-none shadow-2xs">
                        <p class="text-[11px] text-slate-500 mt-1">Tahun pajak baru tempat data SPPT akan dibentuk.</p>
                    </div>
                </div>

                <!-- Scope & Filter Options -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="dusun_id" class="block text-xs font-semibold text-slate-700 mb-1">
                            Cakupan Wilayah Dusun
                        </label>
                        <select id="dusun_id" name="dusun_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-700 bg-white focus:ring-2 focus:ring-indigo-500 outline-none shadow-2xs">
                            <option value="">Semua Dusun (Seluruh Wilayah Desa)</option>
                            @if(isset($dusuns))
                                @foreach($dusuns as $d)
                                    <option value="{{ $d->id }}" {{ old('dusun_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1">Dapat memilih satu dusun tertentu jika ingin menyalin data per wilayah.</p>
                    </div>

                    <div>
                        <label for="pbb_adjustment_percent" class="block text-xs font-semibold text-slate-700 mb-1">
                            Penyesuaian Nilai Ketetapan (Kenaikan / Penurunan %)
                        </label>
                        <div class="relative">
                            <input type="number" step="0.1" id="pbb_adjustment_percent" name="pbb_adjustment_percent" value="{{ old('pbb_adjustment_percent', 0) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 bg-white focus:ring-2 focus:ring-indigo-500 outline-none pr-8 shadow-2xs">
                            <span class="absolute right-3 top-2.5 text-xs text-slate-400 font-bold">%</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Isi 0% jika nilai ketetapan sama persis dengan tahun asal.</p>
                    </div>
                </div>

                <!-- Options Checkboxes & Policy -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3 text-xs">
                    <span class="font-bold text-slate-800 uppercase tracking-wider text-[11px] block">Aturan & Kebijakan Duplikasi Data:</span>
                    
                    <div class="space-y-2">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="on_duplicate" value="skip" checked class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <strong class="text-slate-800 block">Lewati NOP yang sudah terdaftar di tahun tujuan (Direkomendasikan & Aman)</strong>
                                <span class="text-slate-500 text-[11px]">Jika NOP sudah memiliki SPPT di tahun tujuan, data tersebut tidak akan disentuh sama sekali.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="radio" name="on_duplicate" value="update" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <strong class="text-slate-800 block">Perbarui nilai tagihan jika belum ada pembayaran di kasir</strong>
                                <span class="text-slate-500 text-[11px]">SPPT yang sudah terdaftar akan disesuaikan nominalnya, namun SPPT yang sudah memiliki pembayaran kasir tetap otomatis dilewati demi keamanan saldo.</span>
                            </div>
                        </label>
                    </div>

                    <div class="pt-2 border-t border-slate-200">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="copy_components" value="1" checked class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span class="text-slate-700 font-medium">Salin juga komponen luas bumi, luas bangunan, bumi bersama & bangunan bersama</span>
                        </label>
                    </div>
                </div>

                <!-- Safety Information Notice -->
                <div class="p-3.5 rounded-xl bg-indigo-50/60 border border-indigo-100 flex items-start gap-2.5 text-xs">
                    <svg class="w-4 h-4 text-indigo-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <strong class="font-bold text-indigo-900 block">Catatan Keamanan Salin Antartahun:</strong>
                        <p class="text-indigo-800 text-[11px] mt-0.5 leading-relaxed">
                            Seluruh SPPT baru yang terbentuk akan berstatus <strong>Belum Bayar (Unpaid)</strong> dengan denda Rp 0. Objek pajak yang berstatus nonaktif (deactivated) tidak akan disalin. Objek pajak yang berstatus TO (Tanpa Objek) tetap terkunci dari loket pembayaran hingga kasusnya diselesaikan.
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center gap-3">
                    <span class="text-xs text-slate-400">Proses salin data dijalankan dalam transaksi basis data yang aman.</span>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="{{ route('sppts.index') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition text-center">
                            Batal
                        </a>
                        <button type="submit" id="btn-submit-rollover" onclick="return confirm('Apakah Anda yakin ingin menyalin seluruh data SPPT dari tahun sumber ke tahun tujuan?');" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <span>Mulai Proses Salin Data SPPT</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleFileSelect(input) {
    const preview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        fileName.textContent = file.name;

        let sizeFormatted = '';
        if (file.size > 1024 * 1024) {
            sizeFormatted = '(' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)';
        } else {
            sizeFormatted = '(' + (file.size / 1024).toFixed(1) + ' KB)';
        }
        fileSize.textContent = sizeFormatted;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}

function updateDownloadYear(year) {
    if (!year || year < 2000 || year > 2099) return;
    const btnXlsx = document.getElementById('btn-download-xlsx');
    const btnCsv = document.getElementById('btn-download-csv');

    if (btnXlsx) {
        const urlXlsx = new URL(btnXlsx.href);
        urlXlsx.searchParams.set('tax_year', year);
        btnXlsx.href = urlXlsx.toString();
    }

    if (btnCsv) {
        const urlCsv = new URL(btnCsv.href);
        urlCsv.searchParams.set('tax_year', year);
        btnCsv.href = urlCsv.toString();
    }
}

document.getElementById('import-form')?.addEventListener('submit', function() {
    const submitBtn = document.getElementById('btn-submit-import');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Sedang Memproses Sinkronisasi Data...</span>
        `;
    }
});

document.getElementById('rollover-form')?.addEventListener('submit', function() {
    const submitBtn = document.getElementById('btn-submit-rollover');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        submitBtn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Sedang Menyalin Data SPPT...</span>
        `;
    }
});
</script>
@endsection
