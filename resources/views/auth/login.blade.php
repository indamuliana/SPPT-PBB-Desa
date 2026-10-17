<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Aplikasi - SADAYA PBB</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900">
    <div class="w-full max-w-md" x-data="{ 
        fillCredentials(username) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = 'password';
        }
    }">
        <!-- App Logo & Card Header -->
        <div class="text-center mb-6">
            <div class="inline-flex w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 items-center justify-center font-bold text-white shadow-xl shadow-emerald-500/20 text-2xl mb-3">
                SP
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">SADAYA PBB</h1>
            <p class="text-sm text-emerald-300 font-medium">{{ \App\Models\Village::current()->name }} &bull; Tahun Pajak {{ \App\Models\Village::current()->active_tax_year }}</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 border border-slate-200/80">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">Masuk ke Akun Petugas</h2>
                <p class="text-xs text-slate-500 mt-1">Gunakan username dan kata sandi yang telah didaftarkan desa.</p>
            </div>

            <!-- Error Banner -->
            @if($errors->any())
            <div class="rounded-xl bg-rose-50 border border-rose-200 p-3.5 mb-5 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div class="text-xs text-rose-700 font-medium">
                    {{ $errors->first() }}
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="username" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="{{ old('username', 'admin') }}" 
                           required 
                           autofocus 
                           placeholder="Masukkan username"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Password</label>
                    </div>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           value="password"
                           required 
                           placeholder="Masukkan kata sandi"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition">
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs text-slate-600 font-medium">Ingat Sesi Saya</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm shadow-md shadow-emerald-600/30 transition duration-150 flex items-center justify-center gap-2">
                    <span>Masuk Sekarang</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <!-- Quick Demo Role Switcher for Development & Testing -->
            <div class="mt-6 pt-5 border-t border-slate-200">
                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2.5 text-center">Pilih Akun Cepat (3 Peran Resmi)</p>
                <div class="grid grid-cols-1 gap-2 text-xs">
                    <button type="button" @click="fillCredentials('kolektor')" class="w-full px-3 py-2 rounded-xl border border-amber-200 bg-amber-50/50 hover:bg-amber-100/70 text-slate-800 font-medium text-left flex items-center justify-between transition">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                            <span class="font-bold text-slate-900">Kolektor Desa</span>
                            <span class="text-[10px] text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded font-semibold">Akses Tertinggi</span>
                        </div>
                        <span class="text-[11px] text-slate-400 font-mono">@kolektor</span>
                    </button>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" @click="fillCredentials('kadus1')" class="px-2.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium text-left flex items-center justify-between transition">
                            <div class="flex items-center gap-1.5 truncate">
                                <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                                <span class="truncate">Kadus / Kasir</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-mono">@kadus1</span>
                        </button>
                        <button type="button" @click="fillCredentials('kades')" class="px-2.5 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium text-left flex items-center justify-between transition">
                            <div class="flex items-center gap-1.5 truncate">
                                <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                <span class="truncate">Kepala Desa</span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-mono">@kades</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Public Link -->
        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-xs text-emerald-300 hover:text-white transition font-medium inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Halaman Informasi Publik</span>
            </a>
        </div>
    </div>
</body>
</html>
