<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Portal Informasi & Pelayanan PBB-P2' }} - {{ \App\Models\Village::current()->name }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.05); }
        }
        .animate-float-slow {
            animation: floatSlow 5s ease-in-out infinite;
        }
        .animate-pulse-glow {
            animation: pulseGlow 4s ease-in-out infinite;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-slate-800 bg-slate-50 selection:bg-emerald-500 selection:text-white" x-data="{ mobileNav: false }">

    <!-- Sticky Navigation Header -->
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-40 shadow-xs transition duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Logo & Brand Identity -->
                <a href="{{ route('home') }}" class="flex items-center gap-3.5 group">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-gradient-to-tr from-emerald-700 via-emerald-600 to-teal-500 flex items-center justify-center font-black text-white shadow-md shadow-emerald-700/20 group-hover:scale-105 transition transform text-base tracking-wider">
                        SP
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-base sm:text-lg text-slate-900 tracking-tight leading-none">SADAYA PBB</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200/60 hidden sm:inline-block">RESMI</span>
                        </div>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Pemerintah {{ \App\Models\Village::current()->name }}</p>
                    </div>
                </a>

                <!-- Desktop Navigation Menu -->
                <nav class="hidden md:flex items-center gap-7 text-xs font-semibold text-slate-600">
                    <a href="#cek-tagihan" class="hover:text-emerald-700 transition flex items-center gap-1.5 py-1">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Cek Tagihan NOP</span>
                    </a>
                    <a href="#transparansi" class="hover:text-emerald-700 transition flex items-center gap-1.5 py-1">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Transparansi Realisasi</span>
                    </a>
                    <a href="#panduan" class="hover:text-emerald-700 transition flex items-center gap-1.5 py-1">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Alur Bayar</span>
                    </a>
                    <a href="#verifikasi" class="hover:text-emerald-700 transition flex items-center gap-1.5 py-1">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>Verifikasi QR</span>
                    </a>
                </nav>

                <!-- Auth Button (Right) -->
                <div class="flex items-center gap-3">
                    @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl text-xs font-semibold bg-emerald-700 hover:bg-emerald-800 text-white transition shadow-sm shadow-emerald-700/20">
                        <span>Buka Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl text-xs font-semibold bg-emerald-700 hover:bg-emerald-800 text-white transition shadow-sm shadow-emerald-700/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        <span>Login Petugas</span>
                    </a>
                    @endauth

                    <!-- Mobile Menu Hamburger -->
                    <button @click="mobileNav = !mobileNav" type="button" class="md:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileNav" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileNav" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div x-show="mobileNav" x-cloak class="md:hidden py-4 border-t border-slate-100 space-y-2">
                <a @click="mobileNav = false" href="#cek-tagihan" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    Cek Tagihan NOP
                </a>
                <a @click="mobileNav = false" href="#transparansi" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    Transparansi Realisasi
                </a>
                <a @click="mobileNav = false" href="#panduan" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    Alur Pembayaran
                </a>
                <a @click="mobileNav = false" href="#verifikasi" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100">
                    Verifikasi Kuitansi QR
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Public Footer -->
    <footer class="bg-slate-900 text-slate-400 text-xs border-t border-slate-800 pt-16 pb-12 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-slate-800">
                
                <!-- Col 1: Profil Desa -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center font-black text-white text-sm">
                            SP
                        </div>
                        <div>
                            <h2 class="font-bold text-white text-sm tracking-tight">SADAYA PBB DESA SUKAHAJI</h2>
                            <p class="text-[11px] text-slate-400">Sistem Administrasi Data Pajak & Pelayanan PBB-P2</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-md">
                        Inovasi digitalisasi tata kelola Pajak Bumi dan Bangunan Perdesaan dan Perkotaan (PBB-P2) Pemerintah Desa Sukahaji untuk mewujudkan pelayanan yang transparan, akuntabel, dan bebas pungutan liar.
                    </p>
                    <div class="flex items-center gap-2 pt-2 text-slate-300">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-xs font-medium">Kecamatan Tanjungsari, Kabupaten Sumedang, Jawa Barat</span>
                    </div>
                </div>

                <!-- Col 2: Jam Layanan Loket -->
                <div class="space-y-3">
                    <h3 class="font-bold text-white text-xs uppercase tracking-wider">Jam Layanan Loket PBB</h3>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex justify-between border-b border-slate-800/80 pb-1">
                            <span>Senin - Kamis</span>
                            <span class="text-slate-200 font-semibold font-mono">08.00 - 15.00 WIB</span>
                        </li>
                        <li class="flex justify-between border-b border-slate-800/80 pb-1">
                            <span>Jumat</span>
                            <span class="text-slate-200 font-semibold font-mono">08.00 - 14.30 WIB</span>
                        </li>
                        <li class="flex justify-between text-slate-500">
                            <span>Sabtu - Minggu</span>
                            <span class="font-semibold text-rose-400">Libur / Lewat Kadus</span>
                        </li>
                    </ul>
                    <p class="text-[11px] text-slate-500 italic">
                        *Pembayaran di luar jam kantor dapat dilakukan langsung melalui Kepala Dusun (Kadus) setempat.
                    </p>
                </div>

                <!-- Col 3: Akses Cepat & Validasi -->
                <div class="space-y-3">
                    <h3 class="font-bold text-white text-xs uppercase tracking-wider">Layanan Mandiri Warga</h3>
                    <ul class="space-y-2 text-xs">
                        <li>
                            <a href="#cek-tagihan" class="text-slate-400 hover:text-emerald-400 transition flex items-center gap-1.5">
                                <span>&rarr;</span> <span>Cek Tagihan NOP Mandiri</span>
                            </a>
                        </li>
                        <li>
                            <a href="#transparansi" class="text-slate-400 hover:text-emerald-400 transition flex items-center gap-1.5">
                                <span>&rarr;</span> <span>Statistik Realisasi Dusun</span>
                            </a>
                        </li>
                        <li>
                            <a href="#verifikasi" class="text-slate-400 hover:text-emerald-400 transition flex items-center gap-1.5">
                                <span>&rarr;</span> <span>Verifikasi Tanda Terima QR</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('login') }}" class="text-slate-400 hover:text-emerald-400 transition flex items-center gap-1.5">
                                <span>&rarr;</span> <span>Portal Administrator & Kolektor</span>
                            </a>
                        </li>
                    </ul>
                </div>

            </div>

            <!-- Bottom Copyright & Credit -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} Pemerintah Desa Sukahaji &bull; Hak Cipta Dilindungi Undang-Undang.</p>
                <p class="flex items-center gap-1">
                    <span>Dikembangkan dengan standar GovTech akuntabilitas kas desa</span>
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
