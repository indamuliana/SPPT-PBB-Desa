@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Manajemen Akun & Petugas</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar pengguna dengan wewenang tunggal (*Strict Single-Role*) sesuai tata kelola desa.</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Petugas Baru</span>
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('users.index') }}" class="w-full flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:w-72">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, username, atau dusun..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div class="w-full sm:w-48">
                <select name="role" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="">Semua Peran (3 Peran Resmi)</option>
                    <option value="kolektor" {{ request('role') === 'kolektor' ? 'selected' : '' }}>Kolektor Desa (Akses Tertinggi)</option>
                    <option value="kadus" {{ request('role') === 'kadus' ? 'selected' : '' }}>Kadus / Kasir</option>
                    <option value="kades" {{ request('role') === 'kades' ? 'selected' : '' }}>Kepala Desa</option>
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition">
                Filter
            </button>
            @if(request('search') || request('role'))
            <a href="{{ route('users.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Nama & Username</th>
                        <th class="px-6 py-3.5">Peran / Wewenang</th>
                        <th class="px-6 py-3.5">Wilayah Dusun</th>
                        <th class="px-6 py-3.5">Kontak</th>
                        <th class="px-6 py-3.5">Status Akun</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($users as $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $item->name }}</div>
                            <div class="text-slate-400 font-mono text-[11px]">@<span>{{ $item->username }}</span></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold
                                {{ in_array($item->role, ['kolektor', 'admin', 'bendahara']) ? 'bg-amber-100 text-amber-800 border border-amber-200' : '' }}
                                {{ $item->role === 'kadus' ? 'bg-blue-100 text-blue-800 border border-blue-200' : '' }}
                                {{ $item->role === 'kades' ? 'bg-rose-100 text-rose-800 border border-rose-200' : '' }}">
                                {{ $item->role_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                            {{ $item->assignedDusunNames() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                            <div>{{ $item->phone ?? '-' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $item->email ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($item->is_active)
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                Nonaktif
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                            <a href="{{ route('users.edit', $item) }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-emerald-600 p-1 rounded hover:bg-slate-100">
                                Edit
                            </a>
                            @if($item->id !== auth()->id())
                            <form action="{{ route('users.toggle', $item) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktif akun ini?')">
                                @csrf
                                <button type="submit" class="inline-flex items-center text-xs font-semibold {{ $item->is_active ? 'text-amber-600 hover:text-amber-700' : 'text-emerald-600 hover:text-emerald-700' }} p-1 rounded hover:bg-slate-100">
                                    {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                            Tidak ada data pengguna yang sesuai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
