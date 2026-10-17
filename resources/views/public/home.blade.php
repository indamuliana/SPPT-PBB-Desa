@extends('layouts.public')

@section('content')
<div x-data="publicPortal()" class="space-y-16 sm:space-y-24">

    <!-- ========================================================================= -->
    <!-- 1. HERO SECTION: SPLIT MODERN DENGAN FOTO KANTOR DESA SUKAHAJI          -->
    <!-- ========================================================================= -->
    <section class="relative bg-gradient-to-b from-slate-900 via-emerald-950 to-slate-900 text-white pt-12 sm:pt-20 pb-20 sm:pb-28 px-4 sm:px-6 lg:px-8 overflow-hidden">
        
        <!-- Ambient Glow Backdrops -->
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none animate-pulse-glow"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left Column: Typography, Tagline, and Live NOP Checker -->
                <div class="lg:col-span-7 space-y-6 sm:space-y-8 text-left">
                    
                    <!-- Pill Tag -->
                    <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-xs font-semibold backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>PORTAL RESMI PBB-P2 DESA SUKAHAJI</span>
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-3xl sm:text-5xl lg:text-5xl font-black tracking-tight text-white leading-[1.15]">
                        Pelayanan PBB Desa <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-300">
                            Cepat, Transparan & Akuntabel
                        </span>
                    </h1>

                    <!-- Paragraph Description -->
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-xl font-normal">
                        Selamat datang di <strong class="text-white font-semibold">SADAYA PBB</strong> Pemerintah Desa Sukahaji, Kecamatan Tanjungsari, Kabupaten Sumedang. Periksa ketetapan tagihan pajak Anda dan pantau realisasi kas desa secara terbuka dan *real-time*.
                    </p>

                    <!-- Interactive Live NOP Search Box -->
                    <div id="cek-tagihan" class="pt-2 max-w-xl">
                        <div class="bg-white/10 backdrop-blur-md p-2 rounded-2xl border border-white/20 shadow-2xl">
                            <form @submit.prevent="checkNop()" class="flex flex-col sm:flex-row gap-2">
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        x-model="nopInput"
                                        placeholder="Masukkan 18 Digit NOP Anda..."
                                        autocomplete="off"
                                        class="w-full pl-10 pr-4 py-3.5 bg-slate-900/60 border border-slate-700/80 rounded-xl text-white placeholder-slate-400 text-sm font-mono tracking-wider focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition"
                                    >
                                </div>
                                <button 
                                    type="submit" 
                                    :disabled="loading"
                                    class="px-6 py-3.5 bg-emerald-500 hover:bg-emerald-400 disabled:opacity-50 text-slate-950 font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 shrink-0 cursor-pointer"
                                >
                                    <template x-if="loading">
                                        <svg class="animate-spin w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    </template>
                                    <span x-text="loading ? 'Memeriksa...' : 'Cek Tagihan'"></span>
                                </button>
                            </form>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 px-2 pt-2">
                            <span>*Contoh: 32.11.050.001.001-0001.0 atau 18 digit angka</span>
                            <a href="#panduan" class="text-emerald-400 hover:underline">Butuh bantuan?</a>
                        </div>
                    </div>

                    <!-- Quick Links & Features -->
                    <div class="pt-2 flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-300">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Kuitansi Sah QR Code</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Mendukung Cicilan Pokok</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Bebas Pungutan Liar</span>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Foto Kantor Desa Sukahaji & Floating Badges -->
                <div class="lg:col-span-5 relative">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        
                        <!-- Decorative Frame Glow -->
                        <div class="absolute -inset-1.5 bg-gradient-to-tr from-emerald-500 via-teal-400 to-cyan-400 rounded-3xl opacity-30 blur-xl group-hover:opacity-60 transition duration-500"></div>

                        <!-- Main Image Container -->
                        <div class="relative rounded-3xl overflow-hidden border-2 border-white/20 bg-slate-900 shadow-2xl group">
                            <img 
                                src="{{ asset('images/kantor-desa-sukahaji.png') }}" 
                                alt="Kantor Desa Sukahaji Kecamatan Tanjungsari Sumedang" 
                                class="w-full h-80 sm:h-96 object-cover object-center transform group-hover:scale-105 transition duration-700 ease-out"
                            >
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent"></div>

                            <!-- Photo Caption Overlay -->
                            <div class="absolute bottom-4 left-4 right-4 p-3 rounded-2xl bg-slate-900/80 backdrop-blur-md border border-white/10 text-left">
                                <p class="text-xs font-bold text-white tracking-wide">KANTOR DESA SUKAHAJI</p>
                                <p class="text-[11px] text-slate-300">Pusat Layanan Administrasi & Loket Kasir PBB-P2</p>
                            </div>
                        </div>

                        <!-- Floating Stat Card Top Right -->
                        <div class="absolute -top-6 -right-4 sm:-right-6 bg-white/95 backdrop-blur-md text-slate-900 p-3.5 rounded-2xl shadow-xl border border-white/40 animate-float-slow hidden sm:flex items-center gap-3 z-20">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="text-left">
                                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tahun Pajak</p>
                                <p class="text-base font-extrabold text-slate-900">{{ $stats['active_tax_year'] }}</p>
                            </div>
                        </div>

                        <!-- Floating Stat Card Bottom Left -->
                        <div class="absolute -bottom-6 -left-4 sm:-left-6 bg-slate-900/95 backdrop-blur-md text-white p-3.5 rounded-2xl shadow-2xl border border-emerald-500/40 hidden sm:flex items-center gap-3.5 z-20">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <div class="text-left">
                                <p class="text-[10px] uppercase font-bold text-emerald-300 tracking-wider">Capaian Realisasi</p>
                                <p class="text-base font-extrabold text-white font-mono">{{ $stats['realization_percent'] }}%</p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- 2. HASIL CEK TAGIHAN NOP MANDIRI (INTERAKTIF MODAL / INLINE CARD)          -->
    <!-- ========================================================================= -->
    <div x-show="hasResult || searchError" x-cloak class="max-w-4xl mx-auto px-4 sm:px-6 -mt-10 sm:-mt-16 relative z-30 transition-all duration-300">
        
        <!-- Error Alert -->
        <template x-if="searchError">
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 shadow-lg flex items-start gap-4">
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div class="flex-1 text-left">
                    <h3 class="text-xs font-bold text-rose-900 uppercase tracking-wide">Pencarian Tidak Ditemukan</h3>
                    <p class="text-xs text-rose-700 mt-0.5 leading-relaxed" x-text="searchError"></p>
                </div>
                <button @click="searchError = null" class="text-rose-400 hover:text-rose-700">&times;</button>
            </div>
        </template>

        <!-- Success Result Card -->
        <template x-if="hasResult && result">
            <div class="bg-white rounded-3xl shadow-2xl border-2 border-emerald-500/30 overflow-hidden">
                
                <!-- Card Header -->
                <div class="p-6 bg-gradient-to-r from-emerald-800 to-teal-800 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-widest text-emerald-200">Hasil Pengecekan Resmi SPPT PBB-P2</span>
                        <h2 class="text-lg sm:text-xl font-bold font-mono tracking-tight text-white mt-0.5" x-text="result.formatted_nop"></h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <span 
                            class="px-3.5 py-1.5 rounded-full text-xs font-extrabold uppercase tracking-wider"
                            :class="{
                                'bg-emerald-400 text-slate-950': result.status === 'paid',
                                'bg-amber-400 text-slate-950': result.status === 'partial',
                                'bg-rose-400 text-slate-950': result.status === 'unpaid' || result.is_to_blocked
                            }"
                            x-text="result.is_to_blocked ? 'DIBLOKIR / TO' : result.status_label"
                        ></span>
                        <button @click="clearResult()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-6 sm:p-8 space-y-6 text-left">
                    
                    <!-- Top Grid: WP & Objek Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-6 border-b border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-400 font-semibold uppercase tracking-wider">Nama Wajib Pajak (SPPT):</span>
                            <p class="text-base font-bold text-slate-900 mt-0.5" x-text="result.taxpayer_name"></p>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold uppercase tracking-wider">Wilayah / Dusun:</span>
                            <p class="text-sm font-semibold text-slate-800 mt-0.5" x-text="result.dusun"></p>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-slate-400 font-semibold uppercase tracking-wider">Alamat / Letak Objek Pajak:</span>
                            <p class="text-slate-700 mt-0.5" x-text="result.address"></p>
                        </div>
                    </div>

                    <!-- Financial Summary 3 Columns -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ketetapan Pokok (Rp)</span>
                            <p class="text-lg font-black text-slate-900 font-mono" x-text="formatRupiah(result.total_bill)"></p>
                            <span class="text-[10px] text-slate-400" x-text="'Tahun Pajak ' + result.tax_year"></span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Sudah Dibayar (Rp)</span>
                            <p class="text-lg font-black text-emerald-600 font-mono" x-text="formatRupiah(result.paid_amount)"></p>
                            <span class="text-[10px] text-slate-400">Tercatat di Loket</span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Sisa Tagihan (Rp)</span>
                            <p class="text-lg font-black font-mono" :class="result.remaining_bill > 0 ? 'text-rose-600' : 'text-slate-400'" x-text="formatRupiah(result.remaining_bill)"></p>
                            <span class="text-[10px] text-slate-400" x-text="'Jatuh Tempo: ' + result.due_date"></span>
                        </div>
                    </div>

                    <!-- Action & Payment Guidance Notice -->
                    <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="space-y-0.5 text-xs text-emerald-900">
                            <p class="font-bold flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Petugas Penanggung Jawab Wilayah:</span>
                            </p>
                            <p class="text-emerald-800">
                                Pembayaran dapat diserahkan langsung ke <strong class="font-semibold text-emerald-950" x-text="result.kadus_name"></strong> atau ke loket kasir Kantor Desa Sukahaji.
                            </p>
                        </div>
                        <button @click="clearResult()" class="px-4 py-2 rounded-xl text-xs font-bold bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 transition shrink-0 cursor-pointer">
                            Tutup Hasil
                        </button>
                    </div>

                </div>
            </div>
        </template>
    </div>


    <!-- ========================================================================= -->
    <!-- 3. TRANSPARANSI REALISASI PBB DESA (REAL-TIME AGGREGATE WIDGET)           -->
    <!-- ========================================================================= -->
    <section id="transparansi" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center space-y-3 mb-10 sm:mb-14">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                Keterbukaan Publik & Akuntabilitas Kas
            </span>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Capaian Penerimaan PBB Tahun {{ $stats['active_tax_year'] }}
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 max-w-2xl mx-auto">
                Pemantauan real-time realisasi penerimaan pajak dari seluruh wilayah Dusun di Desa Sukahaji berdasarkan kuitansi resmi loket kasir desa.
            </p>
        </div>

        <!-- 3 Primary Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <!-- Target Baku -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-sm relative overflow-hidden group hover:border-slate-300 transition text-left">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Target Resmi Baku Desa</span>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 font-mono mt-1">
                    Rp {{ number_format($stats['target_amount'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-500 mt-2">Ketetapan DHKP Bapenda Kab. Sumedang</p>
            </div>

            <!-- Realisasi Kas Masuk -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border-2 border-emerald-500/40 shadow-md relative overflow-hidden group text-left">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Total Realisasi Kas Diterima</span>
                <p class="text-2xl sm:text-3xl font-black text-emerald-700 font-mono mt-1">
                    Rp {{ number_format($stats['realization_amount'], 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-2 mt-2 text-xs text-slate-600">
                    <span class="font-bold text-emerald-600">{{ $stats['total_paid_sppts'] }}</span>
                    <span>dari {{ $stats['total_sppts'] }} SPPT telah lunas</span>
                </div>
            </div>

            <!-- Sisa Tagihan Terutang -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-sm relative overflow-hidden group hover:border-slate-300 transition text-left">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa Target Terutang</span>
                <p class="text-2xl sm:text-3xl font-black text-slate-800 font-mono mt-1">
                    Rp {{ number_format($stats['remaining_target'], 0, ',', '.') }}
                </p>
                <p class="text-xs text-slate-500 mt-2">Dihimpun oleh Kolektor & Loket Desa</p>
            </div>

        </div>

        <!-- Global Progress Bar -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/90 shadow-sm text-left space-y-4">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                <div>
                    <h3 class="font-bold text-sm text-slate-900">Persentase Capaian Target Desa</h3>
                    <p class="text-xs text-slate-500">Perhitungan otomatis penerimaan riil kasir kas desa</p>
                </div>
                <span class="text-2xl font-black text-emerald-600 font-mono">{{ $stats['realization_percent'] }}%</span>
            </div>
            
            <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden p-0.5">
                <div 
                    class="bg-gradient-to-r from-emerald-600 to-teal-500 h-3 rounded-full transition-all duration-1000 shadow-sm"
                    style="width: {{ min(100, $stats['realization_percent']) }}%"
                ></div>
            </div>
        </div>

        <!-- Dusun Breakdown Grid -->
        @if(isset($dusunStats) && $dusunStats->count() > 0)
        <div class="pt-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600">Rincian Progres Per Wilayah Dusun</h3>
                <span class="text-xs text-slate-400">{{ $dusunStats->count() }} Dusun Terdaftar</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($dusunStats as $dusun)
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md transition text-left space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">{{ $dusun['name'] }}</h4>
                            <p class="text-[11px] text-slate-500 font-medium">Kadus: {{ $dusun['kadus'] }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            {{ $dusun['percent'] }}%
                        </span>
                    </div>

                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ min(100, $dusun['percent']) }}%"></div>
                    </div>

                    <div class="flex justify-between items-center text-xs pt-1 border-t border-slate-100 text-slate-600 font-mono">
                        <span>Realisasi: Rp {{ number_format($dusun['paid'], 0, ',', '.') }}</span>
                        <span class="text-slate-400 text-[11px]">Baku: {{ number_format($dusun['target'], 0, ',', '.') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </section>


    <!-- ========================================================================= -->
    <!-- 4. PANDUAN 4 LANGKAH PEMBAYARAN PBB BAGI WARGA                           -->
    <!-- ========================================================================= -->
    <section id="panduan" class="bg-slate-100/70 py-16 sm:py-24 border-y border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center space-y-3 mb-12 sm:mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full">
                    Kemudahan Layanan
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    4 Langkah Mudah Membayar PBB di Desa Sukahaji
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 max-w-xl mx-auto">
                    Prosedur resmi dan aman dalam menunaikan kewajiban pajak bumi dan bangunan tanpa ribet.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-left">
                
                <!-- Step 1 -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm relative group hover:border-emerald-500 transition">
                    <span class="text-4xl font-black text-emerald-100 group-hover:text-emerald-200 transition font-mono absolute top-4 right-4">01</span>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm mb-1.5">Siapkan 18 Digit NOP</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Lihat Nomor Objek Pajak pada SPPT tahun lalu atau lakukan pencarian melalui form di atas menggunakan nama pemilik.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm relative group hover:border-emerald-500 transition">
                    <span class="text-4xl font-black text-emerald-100 group-hover:text-emerald-200 transition font-mono absolute top-4 right-4">02</span>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm mb-1.5">Datangi Kadus / Loket</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Temui Kepala Dusun di wilayah tempat tinggal Anda atau kunjungi langsung Loket Kasir di Kantor Desa Sukahaji.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm relative group hover:border-emerald-500 transition">
                    <span class="text-4xl font-black text-emerald-100 group-hover:text-emerald-200 transition font-mono absolute top-4 right-4">03</span>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm mb-1.5">Bayar Lunas / Dicicil</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Sistem SADAYA PBB mendukung pembayaran pokok secara lunas maupun bertahap (cicilan) sesuai kemampuan warga.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm relative group hover:border-emerald-500 transition">
                    <span class="text-4xl font-black text-emerald-100 group-hover:text-emerald-200 transition font-mono absolute top-4 right-4">04</span>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm mb-1.5">Terima Kuitansi QR</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Pastikan Anda menerima Tanda Terima resmi ber-QR Code yang dapat dipindai untuk menjamin setoran kas sah masuk ke kas desa.
                    </p>
                </div>

            </div>
        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- 5. VERIFIKASI KEASLIAN KUITANSI QR CODE                                  -->
    <!-- ========================================================================= -->
    <section id="verifikasi" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-tr from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-8 sm:p-12 text-white shadow-2xl relative overflow-hidden text-left">
            
            <div class="relative z-10 max-w-xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    <span>Anti Kuitansi Palsu</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Verifikasi Tanda Terima Kuitansi Pembayaran
                </h2>
                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    Pindai kode QR pada struk thermal / lembar kuitansi A4 Anda menggunakan kamera HP, atau masukkan kode verifikasi 32 karakter untuk memvalidasi keabsahan transaksi.
                </p>

                <form @submit.prevent="verifyToken()" class="pt-2 flex flex-col sm:flex-row gap-2 max-w-md">
                    <input 
                        type="text" 
                        x-model="tokenInput"
                        placeholder="Masukkan token kuitansi..." 
                        class="flex-1 px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white text-xs font-mono placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                    >
                    <button 
                        type="submit" 
                        class="px-5 py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-xl transition cursor-pointer shrink-0"
                    >
                        Verifikasi
                    </button>
                </form>
            </div>

            <!-- Graphic Icon Decor -->
            <div class="absolute -right-8 -bottom-8 opacity-10 pointer-events-none">
                <svg class="w-64 h-64 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
            </div>
        </div>
    </section>

</div>

<!-- ========================================================================= -->
<!-- ALPINE.JS LOGIC UNTUK CEK NOP & VERIFIKASI QR                             -->
<!-- ========================================================================= -->
<script>
    function publicPortal() {
        return {
            nopInput: '',
            tokenInput: '',
            loading: false,
            hasResult: false,
            searchError: null,
            result: null,

            async checkNop() {
                const query = this.nopInput.trim();
                if (!query) {
                    this.searchError = 'Silakan masukkan 18 digit NOP Anda terlebih dahulu.';
                    this.hasResult = false;
                    return;
                }

                this.loading = true;
                this.searchError = null;
                this.hasResult = false;

                try {
                    const res = await fetch(`/api/public/check-nop?nop=${encodeURIComponent(query)}`);
                    const data = await res.json();

                    if (!res.ok || !data.success) {
                        this.searchError = data.message || 'Data NOP tidak ditemukan.';
                        this.hasResult = false;
                        this.result = null;
                    } else {
                        this.result = data;
                        this.hasResult = true;
                        this.searchError = null;
                    }
                } catch (err) {
                    this.searchError = 'Terjadi gangguan koneksi ke server desa. Silakan coba kembali sesaat lagi.';
                    this.hasResult = false;
                } finally {
                    this.loading = false;
                }
            },

            clearResult() {
                this.hasResult = false;
                this.result = null;
                this.searchError = null;
            },

            verifyToken() {
                const token = this.tokenInput.trim();
                if (!token) return;
                window.location.href = `/verifikasi/${encodeURIComponent(token)}`;
            },

            formatRupiah(amount) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount || 0);
            }
        };
    }
</script>
@endsection
