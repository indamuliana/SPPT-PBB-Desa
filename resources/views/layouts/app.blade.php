<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name', 'SADAYA PBB') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased font-sans text-slate-800" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex flex-col md:flex-row">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 z-40 md:hidden"
             @click="sidebarOpen = false" 
             style="display: none;"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-emerald-950 text-white flex flex-col transition-transform duration-300 ease-in-out md:static md:translate-x-0">
            <!-- App Brand & Village -->
            <div class="p-5 border-b border-emerald-900/80 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center font-bold text-white shadow-md shadow-emerald-950/40 text-lg">
                        SP
                    </div>
                    <div>
                        <h1 class="font-bold text-base tracking-tight text-white leading-tight">SADAYA PBB</h1>
                        <p class="text-xs text-emerald-300/90 font-medium truncate max-w-[160px]">{{ \App\Models\Village::current()->name }}</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="md:hidden text-emerald-400 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Tax Year Status Pill -->
            <div class="px-5 py-3 bg-emerald-900/40 border-b border-emerald-900/40 flex items-center justify-between text-xs">
                <span class="text-emerald-300">Tahun Pajak Aktif</span>
                <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-semibold border border-emerald-400/30">
                    {{ \App\Models\Village::current()->active_tax_year }}
                </span>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-4 space-y-1.5 overflow-y-auto text-sm">
                <!-- Common: Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('dashboard') ? 'bg-emerald-800/80 text-white shadow-sm' : 'text-emerald-200 hover:bg-emerald-900/60 hover:text-white' }}">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard Utama</span>
                </a>

                @if(in_array(auth()->user()->role, ['admin', 'kadus', 'kolektor']))
                <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-emerald-400 uppercase tracking-wider">
                    Pelayanan Pajak
                </div>
                <!-- Future POS / Pembayaran in next phases -->
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-emerald-200/60 cursor-not-allowed">
                    <svg class="w-5 h-5 text-emerald-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Loket Kasir / POS</span>
                    <span class="ml-auto text-[10px] bg-emerald-900/80 text-emerald-300 px-1.5 py-0.5 rounded">Fase 3</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-emerald-200/60 cursor-not-allowed">
                    <svg class="w-5 h-5 text-emerald-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Data SPPT & DHKP</span>
                    <span class="ml-auto text-[10px] bg-emerald-900/80 text-emerald-300 px-1.5 py-0.5 rounded">Fase 2</span>
                </a>
                @endif

                @if(in_array(auth()->user()->role, ['admin', 'kolektor', 'bendahara']))
                <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-emerald-400 uppercase tracking-wider">
                    Arus Kas & Setoran
                </div>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-emerald-200/60 cursor-not-allowed">
                    <svg class="w-5 h-5 text-emerald-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>Serah Terima Kas</span>
                    <span class="ml-auto text-[10px] bg-emerald-900/80 text-emerald-300 px-1.5 py-0.5 rounded">Fase 4</span>
                </a>
                <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium text-emerald-200/60 cursor-not-allowed">
                    <svg class="w-5 h-5 text-emerald-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Setoran Bank/Bapenda</span>
                    <span class="ml-auto text-[10px] bg-emerald-900/80 text-emerald-300 px-1.5 py-0.5 rounded">Fase 4</span>
                </a>
                @endif

                @if(auth()->user()->isAdmin())
                <div class="pt-3 pb-1 px-3 text-[11px] font-semibold text-emerald-400 uppercase tracking-wider">
                    Pengaturan Sistem
                </div>
                <a href="{{ route('users.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('users.*') ? 'bg-emerald-800/80 text-white shadow-sm' : 'text-emerald-200 hover:bg-emerald-900/60 hover:text-white' }}">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Manajemen Pengguna</span>
                </a>
                <a href="{{ route('settings.village') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('settings.village') ? 'bg-emerald-800/80 text-white shadow-sm' : 'text-emerald-200 hover:bg-emerald-900/60 hover:text-white' }}">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Identitas Desa & Target</span>
                </a>
                @endif

                @if(in_array(auth()->user()->role, ['admin', 'kades', 'bendahara']))
                <a href="{{ route('audit-logs.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg font-medium transition {{ request()->routeIs('audit-logs.*') ? 'bg-emerald-800/80 text-white shadow-sm' : 'text-emerald-200 hover:bg-emerald-900/60 hover:text-white' }}">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Audit Trail Log</span>
                </a>
                @endif
            </nav>

            <!-- User Footer in Sidebar -->
            <div class="p-4 border-t border-emerald-900/80 bg-emerald-950/60">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-700 flex items-center justify-center font-bold text-white text-xs">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-emerald-400 truncate">{{ auth()->user()->role_label }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
                <div class="px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = true" class="md:hidden text-slate-600 hover:text-slate-900 p-1.5 rounded-lg border border-slate-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                {{ \App\Models\Village::current()->name }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <a href="{{ route('home') }}" target="_blank" class="text-xs font-medium text-slate-500 hover:text-emerald-600 hidden sm:inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Lihat Portal Publik
                        </a>

                        <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>

                        <!-- Logout Form -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-medium text-rose-600 hover:text-rose-700 px-3 py-1.5 rounded-lg border border-rose-200 hover:bg-rose-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Alerts Banner -->
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                @if(session('success'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4 mb-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-sm text-emerald-800 font-medium">
                        {{ session('success') }}
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="rounded-xl bg-rose-50 border border-rose-200 p-4 mb-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-sm text-rose-800 font-medium">
                        {{ session('error') }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
