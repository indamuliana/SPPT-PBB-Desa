@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Audit Trail Aktivitas Sistem</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Log rekam jejak aktivitas pengguna, perubahan data master, transaksi, dan wewenang.</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari keterangan atau IP address..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div class="w-full sm:w-48">
                <select name="action" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="">Semua Aksi</option>
                    <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login</option>
                    <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Logout</option>
                    <option value="create" {{ request('action') === 'create' ? 'selected' : '' }}>Tambah Data (Create)</option>
                    <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>Ubah Data (Update)</option>
                    <option value="delete" {{ request('action') === 'delete' ? 'selected' : '' }}>Hapus Data (Delete)</option>
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition">
                Filter
            </button>
            @if(request('search') || request('action'))
            <a href="{{ route('audit-logs.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Waktu</th>
                        <th class="px-6 py-3.5">Petugas</th>
                        <th class="px-6 py-3.5">Aksi</th>
                        <th class="px-6 py-3.5">Keterangan Aktivitas</th>
                        <th class="px-6 py-3.5">Data Terkait</th>
                        <th class="px-6 py-3.5">IP & Perangkat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/80 transition" x-data="{ openDetails: false }">
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-mono">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900">{{ $log->user?->name ?? 'Sistem / Tamu' }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $log->user?->username ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                {{ $log->action === 'login' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $log->action === 'logout' ? 'bg-slate-100 text-slate-600' : '' }}
                                {{ $log->action === 'create' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $log->action === 'update' ? 'bg-amber-100 text-amber-700' : '' }}
                                {{ $log->action === 'delete' ? 'bg-rose-100 text-rose-700' : '' }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-700">
                            <div>{{ $log->description ?? '-' }}</div>
                            @if($log->old_values || $log->new_values)
                            <button @click="openDetails = !openDetails" class="text-[11px] text-emerald-600 hover:underline mt-1 font-semibold block">
                                <span x-text="openDetails ? 'Sembunyikan Nilai Perubahan' : 'Lihat Nilai Perubahan &rarr;'"></span>
                            </button>
                            <div x-show="openDetails" x-cloak class="mt-2 p-3 bg-slate-50 rounded-xl border border-slate-200 font-mono text-[11px] max-w-md overflow-x-auto space-y-1">
                                @if($log->old_values)
                                <div><strong class="text-rose-600">Lama:</strong> {{ json_encode($log->old_values, JSON_UNESCAPED_SLASHES) }}</div>
                                @endif
                                @if($log->new_values)
                                <div><strong class="text-emerald-600">Baru:</strong> {{ json_encode($log->new_values, JSON_UNESCAPED_SLASHES) }}</div>
                                @endif
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-mono text-[11px]">
                            @if($log->entity_type)
                                {{ class_basename($log->entity_type) }} #{{ $log->entity_id }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[11px] text-slate-400 font-mono">
                            <div>{{ $log->ip_address }}</div>
                            <div class="truncate max-w-[140px]" title="{{ $log->user_agent }}">{{ $log->user_agent }}</div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                            Belum ada riwayat aktivitas tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
