<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Portal Informasi PBB' }} - {{ \App\Models\Village::current()->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full flex flex-col font-sans antialiased text-slate-800 bg-slate-50">
    <!-- Public Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center font-bold text-white shadow-sm text-base">
                    SP
                </div>
                <div>
                    <h1 class="font-bold text-base text-slate-900 leading-tight">SADAYA PBB</h1>
                    <p class="text-xs text-slate-500 font-medium">{{ \App\Models\Village::current()->name }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm">
                    <span>Masuk Dashboard</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Login Petugas</span>
                </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Public Main Body -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Public Footer -->
    <footer class="bg-white border-t border-slate-200 mt-12 py-8 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-2">
            <p class="font-semibold text-slate-700">Pemerintah {{ \App\Models\Village::current()->name }}</p>
            <p>{{ \App\Models\Village::current()->address }} &bull; {{ \App\Models\Village::current()->phone ?? 'Telp. Kantor Desa' }}</p>
            <p class="text-slate-400 pt-2">&copy; {{ date('Y') }} SADAYA PBB (Sistem Administrasi Data dan Pelayanan Pajak PBB Desa)</p>
        </div>
    </footer>
</body>
</html>
