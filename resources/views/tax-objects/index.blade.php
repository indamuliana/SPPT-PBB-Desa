@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Master Objek Pajak (NOP)</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar Nomor Objek Pajak resmi PBB-P2 terdaftar di {{ \App\Models\Village::current()->name }}.</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('tax-objects.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Objek Pajak (NOP)</span>
        </a>
        @endif
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('tax-objects.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:w-80">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NOP, nama WP, alamat, atau dusun..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div class="w-full sm:w-48">
                <select name="dusun_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="">Semua Dusun</option>
                    @foreach($dusunList as $d)
                    <option value="{{ $d->id }}" {{ request('dusun_id') == $d->id || request('dusun') == $d->name ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-40">
                <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="">Semua Status</option>
                    <option value="normal" {{ request('status') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="to" {{ request('status') === 'to' ? 'selected' : '' }}>Tanpa Objek (TO)</option>
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition">
                Cari Data
            </button>
            @if(request('search') || request('dusun') || request('status'))
            <a href="{{ route('tax-objects.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Nomor Objek Pajak (NOP)</th>
                        <th class="px-6 py-3.5">Nama Wajib Pajak</th>
                        <th class="px-6 py-3.5">Letak Objek Pajak</th>
                        <th class="px-6 py-3.5">Wilayah Dusun</th>
                        <th class="px-6 py-3.5 text-center">Status Objek</th>
                        @if(auth()->user()->isAdmin())
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($taxObjects as $op)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono font-bold text-slate-900 text-sm tracking-wide">{{ $op->formatted_nop }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-900">{{ $op->taxpayer?->name ?? 'Belum Ditautkan' }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $op->taxpayer?->nik ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 max-w-xs truncate" title="{{ $op->formatted_address }}">
                            {{ $op->formatted_address }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                            {{ $op->dusunRelation?->name ?? $op->dusun ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($op->isTo())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                Tanpa Objek (TO)
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Normal
                            </span>
                            @endif
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('tax-objects.edit', $op) }}" class="inline-flex items-center text-xs font-semibold text-emerald-600 hover:text-emerald-700 p-1 rounded hover:bg-emerald-50">
                                Edit
                            </a>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                            Belum ada data Objek Pajak.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $taxObjects->links() }}
        </div>
    </div>
</div>
@endsection
