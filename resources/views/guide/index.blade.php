@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'alur' }">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/90 shadow-xs">
        <div>
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-[11px] font-semibold text-emerald-700 mb-2">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <span>Standar Operasional Prosedur (SOP) Desa</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Buku Panduan & Alur Kerja SADAYA PBB</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pedoman operasional lengkap sistem pengelolaan PBB-P2 {{ $village->name }} untuk seluruh peran (Admin, Kasir, Kolektor, Bendahara, dan Kepala Desa).</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('guide.download-pdf') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md shadow-rose-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Unduh Panduan PDF Resmi</span>
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / Pratinjau</span>
            </button>
        </div>
    </div>

    <!-- Quick Role Summary Badges -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <button @click="activeTab = 'admin'" :class="activeTab === 'admin' ? 'border-purple-500 ring-2 ring-purple-100 bg-purple-50/50' : 'border-slate-200 bg-white'" class="p-3.5 rounded-xl border text-left transition shadow-2xs hover:border-purple-300">
            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 block">Peran 1</span>
            <span class="text-xs sm:text-sm font-bold text-slate-800 block mt-0.5">Admin Desa</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Master Data & Ingestion</span>
        </button>

        <button @click="activeTab = 'kadus'" :class="activeTab === 'kadus' ? 'border-emerald-500 ring-2 ring-emerald-100 bg-emerald-50/50' : 'border-slate-200 bg-white'" class="p-3.5 rounded-xl border text-left transition shadow-2xs hover:border-emerald-300">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">Peran 2</span>
            <span class="text-xs sm:text-sm font-bold text-slate-800 block mt-0.5">Kadus / Kasir</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Loket POS & Kuitansi QR</span>
        </button>

        <button @click="activeTab = 'kolektor'" :class="activeTab === 'kolektor' ? 'border-blue-500 ring-2 ring-blue-100 bg-blue-50/50' : 'border-slate-200 bg-white'" class="p-3.5 rounded-xl border text-left transition shadow-2xs hover:border-blue-300">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">Peran 3</span>
            <span class="text-xs sm:text-sm font-bold text-slate-800 block mt-0.5">Kolektor Desa</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Buku Kas & Setor BJB</span>
        </button>

        <button @click="activeTab = 'bendahara'" :class="activeTab === 'bendahara' ? 'border-amber-500 ring-2 ring-amber-100 bg-amber-50/50' : 'border-slate-200 bg-white'" class="p-3.5 rounded-xl border text-left transition shadow-2xs hover:border-amber-300">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 block">Peran 4</span>
            <span class="text-xs sm:text-sm font-bold text-slate-800 block mt-0.5">Bendahara</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Monitoring & Rekonsiliasi</span>
        </button>

        <button @click="activeTab = 'kades'" :class="activeTab === 'kades' ? 'border-teal-500 ring-2 ring-teal-100 bg-teal-50/50' : 'border-slate-200 bg-white'" class="p-3.5 rounded-xl border text-left transition shadow-2xs hover:border-teal-300">
            <span class="text-[10px] font-bold uppercase tracking-wider text-teal-600 block">Peran 5</span>
            <span class="text-xs sm:text-sm font-bold text-slate-800 block mt-0.5">Kepala Desa</span>
            <span class="text-[11px] text-slate-400 mt-1 block">Executive Dashboard</span>
        </button>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-1.5 border-b border-slate-200 overflow-x-auto pb-1 text-xs font-semibold">
        <button @click="activeTab = 'alur'" :class="activeTab === 'alur' ? 'text-emerald-700 border-b-2 border-emerald-600 font-bold bg-emerald-50/50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'" class="px-3.5 py-2.5 rounded-t-xl transition whitespace-nowrap">
            Alur Terpadu (End-to-End)
        </button>
        <button @click="activeTab = 'admin'" :class="activeTab === 'admin' ? 'text-purple-700 border-b-2 border-purple-600 font-bold bg-purple-50/50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'" class="px-3.5 py-2.5 rounded-t-xl transition whitespace-nowrap">
            Panduan Admin Desa
        </button>
        <button @click="activeTab = 'kadus'" :class="activeTab === 'kadus' ? 'text-emerald-700 border-b-2 border-emerald-600 font-bold bg-emerald-50/50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'" class="px-3.5 py-2.5 rounded-t-xl transition whitespace-nowrap">
            Panduan Kadus / Kasir
        </button>
        <button @click="activeTab = 'kolektor'" :class="activeTab === 'kolektor' ? 'text-blue-700 border-b-2 border-blue-600 font-bold bg-blue-50/50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'" class="px-3.5 py-2.5 rounded-t-xl transition whitespace-nowrap">
            Panduan Kolektor Desa
        </button>
        <button @click="activeTab = 'bendahara'" :class="activeTab === 'bendahara' ? 'text-amber-700 border-b-2 border-amber-600 font-bold bg-amber-50/50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'" class="px-3.5 py-2.5 rounded-t-xl transition whitespace-nowrap">
            Panduan Bendahara
        </button>
        <button @click="activeTab = 'kades'" :class="activeTab === 'kades' ? 'text-teal-700 border-b-2 border-teal-600 font-bold bg-teal-50/50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'" class="px-3.5 py-2.5 rounded-t-xl transition whitespace-nowrap">
            Panduan Kepala Desa
        </button>
        <button @click="activeTab = 'faq'" :class="activeTab === 'faq' ? 'text-rose-700 border-b-2 border-rose-600 font-bold bg-rose-50/50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50'" class="px-3.5 py-2.5 rounded-t-xl transition whitespace-nowrap">
            Tanya Jawab & Kendala (FAQ)
        </button>
    </div>

    <!-- TAB 1: ALUR KERJA TERPADU (END-TO-END) -->
    <div x-show="activeTab === 'alur'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-6">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900">Alur Kerja Utama Sistem SADAYA PBB</h2>
                <p class="text-xs text-slate-500 mt-1">Sistem ini menerapkan prinsip <em>Segregation of Duties</em> (Pemisahan Fungsi) yang ketat antara Petugas Penerima Uang (Kasir/Kadus), Pengelola Kas Fisik (Kolektor), Otoritas Penyetor & Verifikator (Admin/Bendahara), dan Pengawas Kebijakan (Kades) untuk mencegah potensi kebocoran dana desa.</p>
            </div>

            <!-- Step by Step Visual Pipeline -->
            <div class="relative border-l-2 border-emerald-500 ml-4 sm:ml-6 space-y-8 pl-6 sm:pl-8 py-2">
                <!-- Step 1 -->
                <div class="relative group">
                    <span class="absolute -left-[35px] sm:-left-[43px] top-0 w-8 h-8 rounded-full bg-purple-600 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-sm">1</span>
                    <div class="bg-purple-50/50 border border-purple-200 rounded-xl p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-bold text-purple-900">Penerimaan Data DHKP & Input Ketetapan SPPT</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-200 text-purple-800">Admin Desa</span>
                        </div>
                        <p class="text-xs text-purple-950 leading-relaxed">
                            Bapenda Kabupaten menerbitkan berkas resmi Daftar Himpunan Ketetapan Pajak (DHKP). Admin Desa mengunggah berkas Excel/CSV melalui menu <strong>Import DHKP</strong> menggunakan template resmi atau merekam data baru via menu <strong>Perekaman Terpadu</strong>. Sistem secara otomatis membentuk objek pajak, 4 komponen (bumi & bangunan), dan tagihan SPPT tahun berjalan.
                        </p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative group">
                    <span class="absolute -left-[35px] sm:-left-[43px] top-0 w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-sm">2</span>
                    <div class="bg-emerald-50/50 border border-emerald-200 rounded-xl p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-bold text-emerald-900">Pelayanan Pembayaran Loket & Penerbitan Kuitansi Sah</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 text-emerald-800">Kadus / Kasir Loket</span>
                        </div>
                        <p class="text-xs text-emerald-950 leading-relaxed">
                            Warga Wajib Pajak (WP) mendatangi loket desa atau dikunjungi oleh Kepala Dusun. Petugas kasir mencari data menggunakan NOP atau Nama WP pada menu <strong>Kasir Loket POS</strong>. Kasir menerima uang pembayaran (bisa Lunas langsung atau Cicilan bertahap), lalu mencetak <strong>Kuitansi Resmi ber-QR Code</strong> (format A4 2-in-1 atau Kuitansi Thermal Kasir) untuk diserahkan kepada warga sebagai bukti sah.
                        </p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="relative group">
                    <span class="absolute -left-[35px] sm:-left-[43px] top-0 w-8 h-8 rounded-full bg-emerald-600 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-sm">3</span>
                    <div class="bg-emerald-50/50 border border-emerald-200 rounded-xl p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-bold text-emerald-900">Serah Terima Kas Harian (Kasir &rarr; Kolektor)</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-200 text-emerald-800">Kasir & Kolektor</span>
                        </div>
                        <p class="text-xs text-emerald-950 leading-relaxed">
                            Pada akhir jam pelayanan loket atau jadwal setoran, Kasir membuat Berita Acara Serah Terima melalui menu <strong>Serah Terima Kas</strong>. Uang tunai fisik yang terkumpul diserahkan langsung kepada Kolektor Desa. Kolektor melakukan penghitungan fisik uang dan menekan tombol <strong>Konfirmasi Terima</strong> di dalam sistem.
                        </p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="relative group">
                    <span class="absolute -left-[35px] sm:-left-[43px] top-0 w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-sm">4</span>
                    <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-bold text-blue-900">Pembukuan Real-Time & Rekonsiliasi Kas</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-200 text-blue-800">Kolektor & Bendahara</span>
                        </div>
                        <p class="text-xs text-blue-950 leading-relaxed">
                            Setiap uang yang dikonfirmasi oleh Kolektor otomatis tercatat di <strong>Buku Kas Umum (Cashbook Ledger)</strong> secara kronologis. Sistem menghitung saldo berjalan kasir, saldo fisik di tangan kolektor, dan memastikan angka di sistem sama persis dengan uang kas fisik (Zero Discrepancy).
                        </p>
                    </div>
                </div>

                <!-- Step 5 -->
                <div class="relative group">
                    <span class="absolute -left-[35px] sm:-left-[43px] top-0 w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-sm">5</span>
                    <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-bold text-blue-900">Penyetoran Kas ke Bank BJB & Validasi Admin</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-200 text-blue-800">Kolektor & Admin Desa</span>
                        </div>
                        <p class="text-xs text-blue-950 leading-relaxed">
                            Kolektor menyetorkan dana pajak yang terkumpul ke Rekening Kas Desa di Bank Jabar Banten (BJB) atau loket Bapenda. Kolektor mengunggah bukti slip setoran bank di menu <strong>Setoran Bank</strong>. Admin Desa memverifikasi bukti validasi dan menyetujui (Approve) transaksi tersebut.
                        </p>
                    </div>
                </div>

                <!-- Step 6 -->
                <div class="relative group">
                    <span class="absolute -left-[35px] sm:-left-[43px] top-0 w-8 h-8 rounded-full bg-teal-600 text-white font-bold text-xs flex items-center justify-center ring-4 ring-white shadow-sm">6</span>
                    <div class="bg-teal-50/50 border border-teal-200 rounded-xl p-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs sm:text-sm font-bold text-teal-900">Monitoring Realisasi Target & Audit Finansial</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-200 text-teal-800">Bendahara & Kepala Desa</span>
                        </div>
                        <p class="text-xs text-teal-950 leading-relaxed">
                            Kepala Desa dan Bendahara memantau pencapaian target PBB tahunan (Target Desa: Rp {{ number_format($village->target_amount, 0, ',', '.') }}) melalui <strong>Dashboard Utama</strong> dan menu <strong>Laporan & Rekapitulasi</strong>. Seluruh pergerakan data diawasi melalui <strong>Audit Log</strong> untuk transparansi penuh.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: PANDUAN ADMIN DESA -->
    <div x-show="activeTab === 'admin'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-5">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-base">AD</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Panduan Tugas & Wewenang Admin Desa</h2>
                    <p class="text-xs text-slate-500">Penanggung jawab konfigurasi master, impor data, validasi persetujuan, dan pengawasan sistem.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <!-- Card 1 -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                    <h3 class="font-bold text-purple-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>1. Import DHKP & Master Data SPPT</span>
                    </h3>
                    <p class="text-slate-600 leading-relaxed">
                        Gunakan menu <strong>Daftar SPPT &rarr; Import DHKP</strong>. Unduh template resmi Excel (.xlsx) atau CSV (.csv), lengkapi data objek pajak dan wajib pajak, lalu unggah kembali. Sistem otomatis membentuk 4 komponen objek pajak dan mencegah penurunan tagihan jika SPPT telah memiliki cicilan.
                    </p>
                </div>

                <!-- Card 2 -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                    <h3 class="font-bold text-purple-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>2. Approval Pembatalan Kuitansi Kasir</span>
                    </h3>
                    <p class="text-slate-600 leading-relaxed">
                        Jika kasir melakukan kesalahan pengetikan nominal atau salah memilih Wajib Pajak, kasir akan mengajukan pembatalan. Admin memeriksa alasan pembatalan di menu <strong>Riwayat Kuitansi</strong> dan dapat menyetujui (Approve) atau menolak (Reject). Tagihan SPPT akan dipulihkan otomatis saat di-approve.
                    </p>
                </div>

                <!-- Card 3 -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                    <h3 class="font-bold text-purple-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                        <span>3. Approval Setoran Bank Kolektor</span>
                    </h3>
                    <p class="text-slate-600 leading-relaxed">
                        Setelah Kolektor menyetorkan uang ke Bank BJB dan mengunggah bukti slip setoran, Admin memverifikasi kesesuaian nilai nominal dan nomor referensi bank pada menu <strong>Setoran Bank</strong> untuk memberikan status <em>Approved</em>.
                    </p>
                </div>

                <!-- Card 4 -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                    <h3 class="font-bold text-purple-800 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                        <span>4. Manajemen Dusun, Pengguna, & Profil Desa</span>
                    </h3>
                    <p class="text-slate-600 leading-relaxed">
                        Admin mengelola data master Dusun (beserta penugasan Kadus penanggung jawab), akun petugas loket, serta konfigurasi target penerimaan dan tahun pajak aktif di menu <strong>Pengaturan</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: PANDUAN KADUS / KASIR LOKET -->
    <div x-show="activeTab === 'kadus'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-5">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-base">KS</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Panduan Tugas Kadus & Kasir Loket POS</h2>
                    <p class="text-xs text-slate-500">Ujung tombak pelayanan warga di loket desa, penerbitan kuitansi sah, dan serah terima kas.</p>
                </div>
            </div>

            <div class="space-y-4 text-xs">
                <!-- Prosedur Loket Kasir -->
                <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/40 space-y-3">
                    <h3 class="font-bold text-emerald-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Langkah Transaksi Pembayaran di Kasir POS:</span>
                    </h3>
                    <ol class="list-decimal list-inside space-y-1.5 text-emerald-950 font-medium leading-relaxed">
                        <li>Buka menu <strong>Kasir Loket POS</strong>. Ketik NOP 18 digit atau nama Wajib Pajak pada bilah pencarian.</li>
                        <li>Sistem otomatis menampilkan tagihan pokok, denda (jika ada), sisa tagihan, dan riwayat cicilan sebelumnya.</li>
                        <li>Pilih mode pembayaran: <strong>Lunas Penuh</strong> atau <strong>Cicilan / Bertahap</strong> sesuai nominal uang yang diserahkan warga.</li>
                        <li>Klik <strong>Proses Pembayaran</strong>. Sistem mencatat pembayaran secara permanen dan menghasilkan Nomor Kuitansi unik.</li>
                        <li>Pilih cetak kuitansi: <strong>Format A4 (2 rangkap)</strong> untuk arsip kantor desa dan warga, atau <strong>Format Thermal (58mm/80mm)</strong> untuk printer struk kecil.</li>
                    </ol>
                </div>

                <!-- Prosedur Serah Terima Kas -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        <span>Serah Terima Uang Fisik Kasir ke Kolektor:</span>
                    </h3>
                    <p class="text-slate-600 leading-relaxed">
                        Setiap kali selesai bertugas atau di akhir hari, Kasir membuka menu <strong>Serah Terima Kas &rarr; Buat Serah Terima</strong>. Sistem otomatis menghitung total uang dari kuitansi yang belum diserahterimakan. Kasir menyerahkan uang fisik kepada Kolektor Desa dan menunggu konfirmasi penerimaan dari Kolektor.
                    </p>
                </div>

                <!-- Penanganan Kasus TO & Mutasi -->
                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/40 space-y-2">
                    <h3 class="font-bold text-amber-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Laporan Kasus TO (Tanpa Objek) & Usulan Mutasi:</span>
                    </h3>
                    <p class="text-amber-950 leading-relaxed">
                        Jika warga melaporkan objek pajak sengketa atau fisik tanah tidak ada, Kadus melaporkan di menu <strong>Kasus TO</strong> agar NOP otomatis terkunci dan tidak dapat ditransaksikan di kasir. Jika terjadi jual beli atau waris, catat di menu <strong>Mutasi & Balik Nama</strong> sebagai laporan bahan revisi SPPT tahun berikutnya ke Bapenda.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: PANDUAN KOLEKTOR DESA -->
    <div x-show="activeTab === 'kolektor'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-5">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-base">KL</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Panduan Tugas Kolektor Desa</h2>
                    <p class="text-xs text-slate-500">Penanggung jawab penerimaan kas dari kasir, pembukuan kas harian, dan penyetoran ke Bank BJB.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/30 space-y-2">
                    <h3 class="font-bold text-blue-900 text-sm">1. Konfirmasi Kasir</h3>
                    <p class="text-blue-950 leading-relaxed">
                        Saat kasir menyerahkan uang fisik, Kolektor membuka menu <strong>Serah Terima Kas</strong>. Hitung fisik uang. Jika cocok, klik <strong>Konfirmasi Terima</strong> (tersedia juga tombol <em>Bulk Receive</em> untuk menerima serah terima dari seluruh kasir sekaligus).
                    </p>
                </div>

                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/30 space-y-2">
                    <h3 class="font-bold text-blue-900 text-sm">2. Buku Kas Umum Real-Time</h3>
                    <p class="text-blue-950 leading-relaxed">
                        Pantau menu <strong>Buku Kas Umum (Cashbook)</strong>. Sistem menyajikan saldo awal, total kas masuk dari kasir, total setoran bank yang telah disetujui, serta posisi saldo riil di tangan kolektor.
                    </p>
                </div>

                <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/30 space-y-2">
                    <h3 class="font-bold text-blue-900 text-sm">3. Setor ke Bank BJB</h3>
                    <p class="text-blue-950 leading-relaxed">
                        Buka menu <strong>Setoran Bank &rarr; Catat Setoran Baru</strong>. Masukkan nominal yang disetorkan ke Bank BJB, tanggal setoran, dan unggah foto/berkas bukti slip setoran bank untuk diverifikasi oleh Admin Desa.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: PANDUAN BENDAHARA DESA -->
    <div x-show="activeTab === 'bendahara'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-5">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-base">BN</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Panduan Tugas Bendahara Desa</h2>
                    <p class="text-xs text-slate-500">Auditor keuangan internal, monitoring likuiditas pajak, dan verifikasi rekonsiliasi kas.</p>
                </div>
            </div>

            <div class="space-y-4 text-xs">
                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/40 space-y-2">
                    <h3 class="font-bold text-amber-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Pengawasan Laporan Realisasi & Arus Kas (Cash Flow):</span>
                    </h3>
                    <p class="text-amber-950 leading-relaxed">
                        Bendahara menggunakan modul <strong>Laporan & Rekapitulasi</strong> untuk meneliti 4 jenis laporan utama:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-slate-700 pl-2">
                        <li><strong>Laporan Realisasi per Dusun:</strong> Membandingkan target ketetapan terhadap uang riil yang sudah terbayar per wilayah.</li>
                        <li><strong>Laporan Kuitansi Pembayaran:</strong> Rincian transaksi loket harian, bulanan, atau per kasir.</li>
                        <li><strong>Laporan Arus Kas (Cash Flow):</strong> Merekonsiliasi uang kasir yang belum disetor vs uang di tangan kolektor vs uang yang telah masuk ke Bank BJB.</li>
                        <li><strong>Laporan Kasus TO & Mutasi:</strong> Memastikan nilai ketetapan yang bermasalah tidak mengganggu laporan APBDes desa.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 6: PANDUAN KEPALA DESA -->
    <div x-show="activeTab === 'kades'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-5">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-base">KD</span>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Panduan Tugas & Akses Kepala Desa</h2>
                    <p class="text-xs text-slate-500">Pemimpin eksekutif desa, pengambil kebijakan, dan evaluator kinerja penagihan pajak.</p>
                </div>
            </div>

            <div class="space-y-4 text-xs">
                <div class="p-4 rounded-xl border border-teal-200 bg-teal-50/40 space-y-2">
                    <h3 class="font-bold text-teal-900 text-sm flex items-center gap-2">
                        <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span>Executive Dashboard & Pengambilan Keputusan:</span>
                    </h3>
                    <p class="text-teal-950 leading-relaxed">
                        Kepala Desa dapat memantau secara langsung:
                    </p>
                    <ul class="list-disc list-inside space-y-1 text-slate-700 pl-2">
                        <li><strong>Persentase Realisasi Capaian:</strong> Berapa persen target APBDes PBB Desa (Rp {{ number_format($village->target_amount, 0, ',', '.') }}) yang sudah terealisasi.</li>
                        <li><strong>Performa Penagihan per Dusun:</strong> Membandingkan dusun mana yang tercepat mencapai 100% dan dusun mana yang membutuhkan bantuan penagihan intensif.</li>
                        <li><strong>Transparansi Kas:</strong> Melihat langsung uang kas yang sudah disetorkan ke kas daerah untuk memastikan akuntabilitas seluruh perangkat desa.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 7: FAQ & SOLUSI KENDALA -->
    <div x-show="activeTab === 'faq'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900">Tanya Jawab & Solusi Kendala Lapangan (FAQ)</h2>
            <div class="space-y-3 text-xs">
                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/60">
                    <strong class="text-slate-900 block mb-1 font-bold">1. Bagaimana jika NOP warga tidak ditemukan saat dicari di loket kasir?</strong>
                    <p class="text-slate-600">Pastikan NOP yang diketik terdiri dari 18 digit angka tanpa spasi. Jika berkas SPPT baru dan belum ada di sistem, Admin Desa dapat menambahkannya melalui menu <em>Perekaman Terpadu</em> atau <em>Import DHKP</em>.</p>
                </div>

                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/60">
                    <strong class="text-slate-900 block mb-1 font-bold">2. Apa yang harus dilakukan jika kasir salah mengetik nominal pembayaran?</strong>
                    <p class="text-slate-600">Kasir dapat membuka menu <em>Riwayat Kuitansi</em>, cari nomor kuitansi terkait, lalu klik <em>Ajukan Pembatalan</em> dengan menyertakan alasan. Admin Desa kemudian melakukan persetujuan (Approve) agar saldo tagihan SPPT warga kembali pulih seperti semula.</p>
                </div>

                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/60">
                    <strong class="text-slate-900 block mb-1 font-bold">3. Apakah mutasi & balik nama di desa langsung mengubah SPPT dan DHKP tahun ini?</strong>
                    <p class="text-slate-600"><strong>Tidak.</strong> Wewenang perubahan SPPT dan DHKP berada di Bapenda Kabupaten (di luar wewenang desa). Fitur mutasi di desa berfungsi sebagai catatan, berita acara, dan laporan resmi desa ke Bapenda untuk pembaruan SPPT di tahun pajak berikutnya.</p>
                </div>

                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/60">
                    <strong class="text-slate-900 block mb-1 font-bold">4. Mengapa objek pajak berstatus 'TO' (Tanpa Objek) tidak bisa dibayar di loket kasir?</strong>
                    <p class="text-slate-600">Objek berstatus TO secara otomatis dikunci sistem untuk melindungi warga dari membayar tanah yang tidak jelas keberadaannya atau sengketa. Pembayaran baru dapat dilakukan setelah kasus diinvestigasi oleh Kolektor dan diselesaikan.</p>
                </div>

                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/60">
                    <strong class="text-slate-900 block mb-1 font-bold">5. Bagaimana cara memverifikasi keaslian kuitansi pembayaran yang dibawa warga?</strong>
                    <p class="text-slate-600">Setiap kuitansi resmi dilengkapi QR Code unik. Siapapun (termasuk warga) dapat memindai QR Code tersebut menggunakan kamera smartphone untuk melihat bukti sah pelunasan langsung dari server desa secara online.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
