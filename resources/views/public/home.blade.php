@extends('layouts.public')

@section('content')
<div x-data="publicPortal()" class="w-full">

    <!-- ========================================================================= -->
    <!-- 1. HERO SECTION: FOTO KANTOR DESA SEBAGAI LATAR BELAKANG                  -->
    <!-- ========================================================================= -->
    <section class="relative text-white py-16 sm:py-24 px-4 sm:px-6 lg:px-8 overflow-hidden min-h-[560px] flex items-center">
        
        <!-- Background Foto Kantor Desa Sukahaji & Gradient Overlay -->
        <div class="absolute inset-0 z-0">
            <img 
                src="{{ asset('images/kantor-desa-sukahaji.png') }}" 
                alt="Kantor Desa Sukahaji Kecamatan Tanjungsari Kabupaten Sumedang" 
                class="w-full h-full object-cover object-center transform scale-105 filter brightness-95"
            >
            <!-- Layer Gelap & Gradasi Hijau Zamrud untuk Kontras Teks Maksimal -->
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/90 to-emerald-950/85"></div>
            <div class="absolute inset-0 bg-slate-950/30 backdrop-blur-[1px]"></div>
        </div>

        <div class="max-w-7xl mx-auto w-full relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                
                <!-- Kolom Kiri: Informasi, Headline & Form Cek NOP -->
                <div class="lg:col-span-7 space-y-6 text-left">
                    
                    <!-- Badge Status Resmi -->
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-xs font-semibold backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>PORTAL RESMI PBB-P2 DESA SUKAHAJI</span>
                    </div>

                    <!-- Headline Utama -->
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white leading-tight">
                        Tata Kelola Pajak Desa yang <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-300">
                            Transparan, Cepat & Terpercaya
                        </span>
                    </h1>

                    <!-- Deskripsi Sambutan -->
                    <p class="text-slate-200 text-sm sm:text-base leading-relaxed max-w-xl font-normal drop-shadow-xs">
                        Selamat datang di portal <strong class="text-white font-semibold">SADAYA PBB</strong> Pemerintah Desa Sukahaji, Kecamatan Tanjungsari, Kabupaten Sumedang. Warga dapat memeriksa status SPPT mandiri dan memantau realisasi penerimaan kas desa secara terbuka.
                    </p>

                    <!-- Form Cek Tagihan NOP Mandiri (Kontras Tinggi & Bersih) -->
                    <div id="cek-tagihan" class="pt-2 max-w-xl">
                        <div class="bg-white/95 backdrop-blur-md p-4 sm:p-5 rounded-2xl shadow-2xl border border-white/30 text-slate-800">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Cek Ketetapan & Tagihan PBB-P2 Mandiri
                            </label>
                            <form @submit.prevent="checkNop()" class="flex flex-col sm:flex-row gap-2.5">
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        x-model="nopInput"
                                        placeholder="Ketik 18 digit NOP (contoh: 32.11.050...)"
                                        autocomplete="off"
                                        class="w-full pl-10 pr-3.5 py-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 text-sm font-mono tracking-wider focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition"
                                    >
                                </div>
                                <button 
                                    type="submit" 
                                    :disabled="loading"
                                    class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition shadow-md shadow-emerald-700/20 flex items-center justify-center gap-2 shrink-0 cursor-pointer"
                                >
                                    <template x-if="loading">
                                        <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    </template>
                                    <span x-text="loading ? 'Mengecek...' : 'Cek Tagihan'"></span>
                                </button>
                            </form>
                            <div class="flex items-center justify-between text-[11px] text-slate-500 pt-2 px-1">
                                <span>*Format 18 digit angka atau format resmi bertitik</span>
                                <a href="#panduan" class="text-emerald-700 font-semibold hover:underline">Petunjuk Pembayaran &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <!-- Indikator Layanan & Keamanan -->
                    <div class="pt-1 flex flex-wrap items-center gap-4 text-xs font-medium text-slate-300">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Kuitansi Ber-QR Code</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Dapat Dicicil Bertahap</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Audit Kas 3 Tingkat</span>
                        </div>
                    </div>

                </div>

                <!-- Kolom Kanan: Glassmorphism Info & Status Card Desa -->
                <div class="lg:col-span-5">
                    <div class="bg-slate-900/70 backdrop-blur-xl p-6 sm:p-7 rounded-3xl border border-white/20 shadow-2xl text-left space-y-5">
                        
                        <!-- Header Kartu -->
                        <div class="flex items-center gap-3.5 pb-4 border-b border-white/10">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 flex items-center justify-center font-bold shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-extrabold text-white text-base tracking-tight">Kantor Desa Sukahaji</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">ONLINE</span>
                                </div>
                                <p class="text-xs text-slate-300 mt-0.5">Kecamatan Tanjungsari, Kabupaten Sumedang</p>
                            </div>
                        </div>

                        <!-- Metrik Ringkas -->
                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between items-center py-1 border-b border-white/5">
                                <span class="text-slate-300">Tahun Pajak Ketetapan:</span>
                                <span class="font-bold font-mono text-emerald-300 text-sm">{{ $stats['active_tax_year'] }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-white/5">
                                <span class="text-slate-300">Target Baku Desa:</span>
                                <span class="font-bold font-mono text-white">Rp {{ number_format($stats['target_amount'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-white/5">
                                <span class="text-slate-300">Realisasi Kas Diterima:</span>
                                <span class="font-bold font-mono text-emerald-400 text-sm">Rp {{ number_format($stats['realization_amount'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="text-slate-300">Lembar SPPT Terbayar:</span>
                                <span class="font-bold text-white">{{ $stats['total_paid_sppts'] }} <span class="text-slate-400 font-normal">/ {{ $stats['total_sppts'] }} lembar</span></span>
                            </div>
                        </div>

                        <!-- Progress Bar Capaian -->
                        <div class="space-y-2 pt-2">
                            <div class="flex justify-between text-xs font-semibold">
                                <span class="text-slate-300">Persentase Realisasi Kas</span>
                                <span class="text-emerald-400 font-mono font-bold">{{ $stats['realization_percent'] }}%</span>
                            </div>
                            <div class="w-full bg-slate-800 rounded-full h-3 overflow-hidden p-0.5 border border-white/10">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-2 rounded-full transition-all duration-700" style="width: {{ min(100, $stats['realization_percent']) }}%"></div>
                            </div>
                        </div>

                        <!-- Tombol Cepat Navigasi -->
                        <div class="pt-2 border-t border-white/10 flex items-center justify-between text-xs">
                            <a href="#transparansi" class="text-emerald-300 hover:text-white font-medium transition flex items-center gap-1">
                                <span>Rincian Per Dusun &darr;</span>
                            </a>
                            <a href="#verifikasi" class="text-slate-300 hover:text-white font-medium transition flex items-center gap-1">
                                <span>Verifikasi Kuitansi &darr;</span>
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- 2. HASIL CEK TAGIHAN NOP MANDIRI (MUNCUL OTOMATIS JIKA DICARI)           -->
    <!-- ========================================================================= -->
    <div x-show="hasResult || searchError" x-cloak class="bg-slate-100 border-b border-slate-200 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            
            <!-- Alert Jika NOP Tidak Ditemukan -->
            <template x-if="searchError">
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 shadow-sm flex items-start gap-4 text-left">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xs font-bold text-rose-900 uppercase tracking-wide">Pencarian NOP Tidak Ditemukan</h3>
                        <p class="text-xs text-rose-700 mt-1 leading-relaxed" x-text="searchError"></p>
                    </div>
                    <button @click="searchError = null" class="text-rose-400 hover:text-rose-700 text-sm font-bold">&times;</button>
                </div>
            </template>

            <!-- Kartu Hasil Cek Tagihan -->
            <template x-if="hasResult && result">
                <div class="bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden text-left">
                    
                    <!-- Header Kartu -->
                    <div class="px-6 py-5 bg-gradient-to-r from-emerald-800 to-teal-800 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <span class="text-[10px] uppercase font-bold tracking-widest text-emerald-200">Ketetapan Resmi SPPT PBB-P2</span>
                            <h2 class="text-lg sm:text-xl font-bold font-mono tracking-tight text-white mt-0.5" x-text="result.formatted_nop"></h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <span 
                                class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider"
                                :class="{
                                    'bg-emerald-400 text-slate-950': result.status === 'paid',
                                    'bg-amber-400 text-slate-950': result.status === 'partial',
                                    'bg-rose-400 text-slate-950': result.status === 'unpaid' || result.is_to_blocked
                                }"
                                x-text="result.is_to_blocked ? 'DIBLOKIR / TO' : result.status_label"
                            ></span>
                            <button @click="clearResult()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Rincian Objek & Wajib Pajak -->
                    <div class="p-6 sm:p-8 space-y-6">
                        
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
                                <span class="text-slate-400 font-semibold uppercase tracking-wider">Alamat Objek Pajak:</span>
                                <p class="text-slate-700 mt-0.5" x-text="result.address"></p>
                            </div>
                        </div>

                        <!-- Ringkasan Finansial 3 Kolom -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <div>
                                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ketetapan Pokok</span>
                                <p class="text-lg font-black text-slate-900 font-mono mt-0.5" x-text="formatRupiah(result.total_bill)"></p>
                                <span class="text-[10px] text-slate-400" x-text="'Tahun Pajak ' + result.tax_year"></span>
                            </div>
                            <div>
                                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Sudah Terbayar</span>
                                <p class="text-lg font-black text-emerald-600 font-mono mt-0.5" x-text="formatRupiah(result.paid_amount)"></p>
                                <span class="text-[10px] text-slate-400">Tercatat di Kasir</span>
                            </div>
                            <div>
                                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Sisa Tagihan</span>
                                <p class="text-lg font-black font-mono mt-0.5" :class="result.remaining_bill > 0 ? 'text-rose-600' : 'text-slate-400'" x-text="formatRupiah(result.remaining_bill)"></p>
                                <span class="text-[10px] text-slate-400" x-text="'Jatuh Tempo: ' + result.due_date"></span>
                            </div>
                        </div>

                        <!-- Petunjuk Lokasi / Petugas Bayar -->
                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                            <div class="text-emerald-900">
                                <p class="font-bold">Petugas Penanggung Jawab Wilayah:</p>
                                <p class="mt-0.5">
                                    Pembayaran dapat dilakukan melalui <strong class="font-semibold text-emerald-950" x-text="result.kadus_name"></strong> atau langsung di loket kasir Kantor Desa Sukahaji.
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
    </div>


    <!-- ========================================================================= -->
    <!-- 3. TRANSPARANSI REALISASI PBB DESA (BERSIH & TERORGANISIR)                 -->
    <!-- ========================================================================= -->
    <section id="transparansi" class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 bg-white border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto space-y-12">
            
            <!-- Judul Section -->
            <div class="text-center space-y-2.5 max-w-2xl mx-auto">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    Transparansi & Akuntabilitas Publik
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Capaian Penerimaan PBB Tahun {{ $stats['active_tax_year'] }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500">
                    Data agregat pergerakan kas resmi yang diterima loket desa dan para kolektor di seluruh wilayah Dusun Desa Sukahaji.
                </p>
            </div>

            <!-- 3 Kartu Metrik Utama -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
                
                <!-- Target Baku -->
                <div class="bg-slate-50 p-6 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Target Resmi Baku Desa</span>
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 font-mono mt-1">
                            Rp {{ number_format($stats['target_amount'], 0, ',', '.') }}
                        </p>
                    </div>
                    <p class="text-xs text-slate-500 pt-1 border-t border-slate-200">Ketetapan DHKP Bapenda Kab. Sumedang</p>
                </div>

                <!-- Realisasi Sah -->
                <div class="bg-emerald-50/60 p-6 sm:p-7 rounded-3xl border-2 border-emerald-500/50 shadow-sm space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Total Kas Sah Diterima</span>
                        <p class="text-2xl sm:text-3xl font-black text-emerald-800 font-mono mt-1">
                            Rp {{ number_format($stats['realization_amount'], 0, ',', '.') }}
                        </p>
                    </div>
                    <p class="text-xs text-emerald-700 font-medium pt-1 border-t border-emerald-200/60">
                        {{ $stats['total_paid_sppts'] }} dari {{ $stats['total_sppts'] }} lembar SPPT lunas
                    </p>
                </div>

                <!-- Sisa Target -->
                <div class="bg-slate-50 p-6 sm:p-7 rounded-3xl border border-slate-200 shadow-xs space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa Target Terutang</span>
                        <p class="text-2xl sm:text-3xl font-black text-slate-800 font-mono mt-1">
                            Rp {{ number_format($stats['remaining_target'], 0, ',', '.') }}
                        </p>
                    </div>
                    <p class="text-xs text-slate-500 pt-1 border-t border-slate-200">Dalam proses penagihan aktif</p>
                </div>

            </div>

            <!-- Bar Progres Global -->
            <div class="p-6 sm:p-8 bg-slate-50 rounded-3xl border border-slate-200 text-left space-y-3">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Persentase Capaian Target Desa</h3>
                        <p class="text-xs text-slate-500">Perhitungan akumulatif dari seluruh transaksi kuitansi kasir</p>
                    </div>
                    <span class="text-2xl font-black text-emerald-700 font-mono">{{ $stats['realization_percent'] }}%</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-3.5 overflow-hidden p-0.5">
                    <div 
                        class="bg-gradient-to-r from-emerald-600 to-teal-500 h-2.5 rounded-full transition-all duration-700"
                        style="width: {{ min(100, $stats['realization_percent']) }}%"
                    ></div>
                </div>
            </div>

            <!-- Rincian Capaian Per Dusun -->
            @if(isset($dusunStats) && $dusunStats->count() > 0)
            <div class="space-y-4 text-left">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Rincian Realisasi Per Wilayah Dusun</h3>
                    <span class="text-xs text-slate-400">{{ $dusunStats->count() }} Dusun</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($dusunStats as $dusun)
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-bold text-sm text-slate-900">{{ $dusun['name'] }}</h4>
                                <p class="text-[11px] text-slate-500">Kadus: <span class="font-medium text-slate-700">{{ $dusun['kadus'] }}</span></p>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-100 text-emerald-800">
                                {{ $dusun['percent'] }}%
                            </span>
                        </div>

                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-emerald-600 h-2 rounded-full" style="width: {{ min(100, $dusun['percent']) }}%"></div>
                        </div>

                        <div class="flex justify-between items-center text-xs pt-1 border-t border-slate-100 font-mono text-slate-600">
                            <span>Realisasi: Rp {{ number_format($dusun['paid'], 0, ',', '.') }}</span>
                            <span class="text-slate-400 text-[11px]">Baku: {{ number_format($dusun['target'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- 4. PANDUAN 4 LANGKAH PEMBAYARAN PBB BAGI WARGA                           -->
    <!-- ========================================================================= -->
    <section id="panduan" class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 bg-slate-50 border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto space-y-12">
            
            <div class="text-center space-y-2.5 max-w-2xl mx-auto">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full">
                    Panduan Warga
                </span>
                <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    4 Langkah Mudah Membayar PBB-P2
                </h2>
                <p class="text-xs sm:text-sm text-slate-500">
                    Prosedur resmi untuk memastikan pembayaran Anda tercatat langsung di buku kas desa secara sah.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-left">
                
                <!-- Langkah 1 -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3 relative group">
                    <span class="text-3xl font-black text-slate-200 group-hover:text-emerald-200 transition font-mono absolute top-4 right-4">01</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Siapkan Nomor Objek Pajak (NOP)</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Lihat 18 digit NOP pada lembar SPPT tahun sebelumnya atau cek melalui form pencarian di atas.
                    </p>
                </div>

                <!-- Langkah 2 -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3 relative group">
                    <span class="text-3xl font-black text-slate-200 group-hover:text-emerald-200 transition font-mono absolute top-4 right-4">02</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Temui Kadus atau Loket Kasir</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Datangi Kepala Dusun wilayah tempat tinggal Anda atau kunjungi langsung Loket Kasir di Kantor Desa Sukahaji.
                    </p>
                </div>

                <!-- Langkah 3 -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3 relative group">
                    <span class="text-3xl font-black text-slate-200 group-hover:text-emerald-200 transition font-mono absolute top-4 right-4">03</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Bayar Lunas atau Dicicil</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Sistem SADAYA PBB mendukung pembayaran pokok sekaligus maupun bertahap (cicilan) sesuai kondisi warga.
                    </p>
                </div>

                <!-- Langkah 4 -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs space-y-3 relative group">
                    <span class="text-3xl font-black text-slate-200 group-hover:text-emerald-200 transition font-mono absolute top-4 right-4">04</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Terima Tanda Terima Ber-QR Code</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Pastikan petugas mencetak Tanda Terima resmi ber-QR Code untuk menjamin setoran langsung masuk ke kas desa.
                    </p>
                </div>

            </div>

        </div>
    </section>


    <!-- ========================================================================= -->
    <!-- 5. VERIFIKASI KEASLIAN KUITANSI BER-QR CODE                              -->
    <!-- ========================================================================= -->
    <section id="verifikasi" class="py-14 sm:py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-emerald-950 via-slate-900 to-slate-950 text-white">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white/10 backdrop-blur-md p-8 sm:p-10 rounded-3xl border border-white/15 text-left space-y-4">
                
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Jaminan Keaslian Kuitansi</span>
                </div>

                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Verifikasi Tanda Terima Pembayaran
                </h2>

                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-2xl">
                    Pindai kode QR pada struk kasir menggunakan kamera smartphone Anda, atau masukkan token verifikasi unik kuitansi di bawah ini untuk melihat bukti setoran sah di basis data desa.
                </p>

                <form @submit.prevent="verifyToken()" class="pt-2 flex flex-col sm:flex-row gap-2 max-w-lg">
                    <input 
                        type="text" 
                        x-model="tokenInput"
                        placeholder="Ketik kode token kuitansi..." 
                        class="flex-1 px-4 py-3 bg-slate-950/60 border border-slate-700 rounded-xl text-white text-xs font-mono placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                    >
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs uppercase tracking-wider rounded-xl transition cursor-pointer shrink-0"
                    >
                        Verifikasi
                    </button>
                </form>

            </div>
        </div>
    </section>

</div>

<!-- Script Alpine.js untuk Cek NOP & Verifikasi Token -->
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
                    this.searchError = 'Terjadi gangguan jaringan ke server desa. Silakan coba kembali sesaat lagi.';
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
