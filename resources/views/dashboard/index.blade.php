@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Top Welcome Card -->
    <div class="bg-gradient-to-r from-emerald-800 to-teal-900 rounded-2xl p-6 sm:p-8 text-white shadow-lg shadow-emerald-950/20 relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-xs font-semibold text-emerald-200 mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Portal Sistem Administrasi & Pelayanan PBB-P2</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white mb-2">
                Selamat Datang, {{ $user->name }}
            </h1>
            <p class="text-emerald-100 text-sm leading-relaxed">
                Anda masuk sebagai <strong class="text-white underline decoration-emerald-400">{{ $user->role_label }}</strong>
                @if($user->dusun_name)
                    wilayah penugasan <span class="bg-emerald-700/60 px-2 py-0.5 rounded text-white font-medium">{{ $user->dusun_name }}</span>
                @endif
                pada sistem pembukuan PBB {{ $village->name }}, Tahun Pajak {{ $village->active_tax_year }}.
            </p>
        </div>
        <!-- Decorative Background Circle -->
        <div class="absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-emerald-500/10 pointer-events-none blur-2xl"></div>
    </div>

    <!-- Quick Role Context Notice for Phase 1 -->
    <div class="bg-white rounded-xl p-4 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2.5 text-slate-700 font-medium">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
            <span>Status Fondasi Sistem (Fase 1): <strong>Aktif & Terverifikasi</strong>. Master data, POS kasir, dan alur kas siap dilanjutkan ke tahap berikutnya.</span>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span class="text-slate-400 font-mono text-[11px]">PHP {{ phpversion() }} &bull; MySQL &bull; Laravel {{ app()->version() }}</span>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Target Pajak Desa -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Target Resmi Desa</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <div>
                <p class="text-xl sm:text-2xl font-bold text-slate-900">Rp {{ number_format($stats['target_amount'], 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">Tahun Pajak {{ $village->active_tax_year }}</p>
            </div>
        </div>

        <!-- Realisasi Penerimaan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Realisasi Penerimaan</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div>
                <p class="text-xl sm:text-2xl font-bold text-slate-900">Rp {{ number_format($stats['realization_amount'], 0, ',', '.') }}</p>
                <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $stats['realization_percent'] }}%"></div>
                </div>
                <p class="text-xs text-slate-400 mt-1 flex justify-between">
                    <span>Capaian</span>
                    <span class="font-semibold text-slate-700">{{ $stats['realization_percent'] }}%</span>
                </p>
            </div>
        </div>

        <!-- Sisa Tunggakan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Sisa Belum Tertagih</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div>
                <p class="text-xl sm:text-2xl font-bold text-slate-900">Rp {{ number_format($stats['remaining_amount'], 0, ',', '.') }}</p>
                <p class="text-xs text-amber-600 font-medium mt-1">Belum terbayar</p>
            </div>
        </div>

        <!-- Akun & Pengguna Aktif -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Petugas Terdaftar</span>
                <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div>
                <p class="text-xl sm:text-2xl font-bold text-slate-900">{{ $stats['total_users'] }} <span class="text-sm font-normal text-slate-500">Akun</span></p>
                <p class="text-xs text-slate-400 mt-1">Admin, Kasir, Kolektor, Kaur, Kuwu</p>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Actions -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-xs">
        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>Akses Cepat Pengelolaan</span>
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
            @if($user->isAdmin())
            <a href="{{ route('settings.village') }}" class="p-4 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition group flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-emerald-700">Identitas & Target Desa</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Ubah nama desa, target penerimaan, dan tahun pajak aktif.</p>
                </div>
            </a>

            <a href="{{ route('users.index') }}" class="p-4 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition group flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-blue-700">Kelola Akun Pengguna</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tambah akun baru, reset password, dan kelola peran petugas.</p>
                </div>
            </a>
            @endif

            @if(in_array($user->role, ['admin', 'kades', 'bendahara']))
            <a href="{{ route('audit-logs.index') }}" class="p-4 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition group flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-purple-700">Audit Trail Aktivitas</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pantau riwayat login, perubahan master, dan tindakan petugas.</p>
                </div>
            </a>
            @endif

            <a href="{{ route('home') }}" target="_blank" class="p-4 rounded-xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition group flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-teal-700">Portal Publik Desa</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Lihat tampilan landing page transparansi penerimaan warga.</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Audit Logs Table -->
    @if(in_array($user->role, ['admin', 'kades', 'bendahara']))
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Aktivitas Sistem Terkini (Audit Trail)</span>
            </h2>
            <a href="{{ route('audit-logs.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Lihat Semua &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Waktu</th>
                        <th class="px-6 py-3">Petugas</th>
                        <th class="px-6 py-3">Aksi</th>
                        <th class="px-6 py-3">Deskripsi</th>
                        <th class="px-6 py-3">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($recentAudits as $log)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-3.5 whitespace-nowrap text-slate-500 font-mono">
                            {{ $log->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap font-medium text-slate-900">
                            {{ $log->user?->name ?? 'Sistem' }}
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $log->action === 'login' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $log->action === 'logout' ? 'bg-slate-100 text-slate-600' : '' }}
                                {{ $log->action === 'create' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $log->action === 'update' ? 'bg-amber-100 text-amber-700' : '' }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-slate-600">
                            {{ $log->description ?? '-' }}
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap font-mono text-slate-400">
                            {{ $log->ip_address }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                            Belum ada aktivitas tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
