@extends('layouts.app')

@section('title', 'Kasus TO (Tanpa Objek) - SADAYA PBB')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Kasus TO (Tanpa Objek / Pemilik Belum Jelas)</h1>
            <p class="text-sm text-slate-600 mt-1">Daftar objek pajak bermasalah yang dikunci dari pembayaran dan dalam penelusuran lapangan</p>
        </div>
        <div>
            <a href="{{ route('to-cases.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Tandai NOP sebagai TO</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Status Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <a href="{{ route('to-cases.index') }}" 
           class="p-4 bg-white rounded-xl border {{ empty($status) ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200' }} shadow-sm hover:border-slate-300 transition">
            <span class="text-xs uppercase tracking-wider text-slate-500 font-semibold block">Total Kasus TO</span>
            <div class="text-2xl font-bold font-mono text-slate-800 mt-1">{{ $counts['total'] }}</div>
        </a>

        <a href="{{ route('to-cases.index', ['status' => 'open']) }}" 
           class="p-4 bg-white rounded-xl border {{ $status === 'open' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-200' }} shadow-sm hover:border-slate-300 transition">
            <span class="text-xs uppercase tracking-wider text-rose-600 font-semibold block">Kasus Terbuka (Baru)</span>
            <div class="text-2xl font-bold font-mono text-rose-600 mt-1">{{ $counts['open'] }}</div>
        </a>

        <a href="{{ route('to-cases.index', ['status' => 'investigating']) }}" 
           class="p-4 bg-white rounded-xl border {{ $status === 'investigating' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200' }} shadow-sm hover:border-slate-300 transition">
            <span class="text-xs uppercase tracking-wider text-amber-600 font-semibold block">Dalam Penelusuran</span>
            <div class="text-2xl font-bold font-mono text-amber-600 mt-1">{{ $counts['investigating'] }}</div>
        </a>

        <a href="{{ route('to-cases.index', ['status' => 'resolved']) }}" 
           class="p-4 bg-white rounded-xl border {{ $status === 'resolved' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200' }} shadow-sm hover:border-slate-300 transition">
            <span class="text-xs uppercase tracking-wider text-emerald-600 font-semibold block">Telah Diselesaikan</span>
            <div class="text-2xl font-bold font-mono text-emerald-600 mt-1">{{ $counts['resolved'] }}</div>
        </a>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="GET" action="{{ route('to-cases.index') }}" class="w-full sm:w-80 flex items-center gap-2">
            @if($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="relative w-full">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari NOP / No. Kasus..." 
                       class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-lg transition">
                Cari
            </button>
        </form>

        <div class="text-xs text-slate-500 self-end sm:self-auto">
            SPPT dengan status TO otomatis ditolak saat diinput ke kasir POS
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-700 text-xs font-semibold uppercase border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Kasus</th>
                        <th class="py-3 px-4">NOP & Letak Objek</th>
                        <th class="py-3 px-4">Subjek Tercatat</th>
                        <th class="py-3 px-4">Kategori Alasan</th>
                        <th class="py-3 px-4">Petugas Lapangan</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($cases as $case)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4">
                            <span class="font-mono font-medium text-slate-900 text-xs">{{ $case->case_number }}</span>
                            <span class="block text-[11px] text-slate-400">{{ $case->created_at->format('d/m/Y') }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-mono font-bold text-slate-900 text-xs">{{ $case->taxObject->formatted_nop }}</span>
                            <span class="block text-xs text-slate-500">{{ $case->taxObject->formatted_address }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-medium text-slate-800 text-xs">{{ $case->taxObject->taxpayer->name }}</span>
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-700">
                            <span class="font-semibold text-rose-700">{{ $case->reason_label }}</span>
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-600">
                            @if($case->assignedTo)
                                <span class="font-medium text-slate-800">{{ $case->assignedTo->name }}</span>
                                <span class="block text-[10px] text-slate-400">Kolektor</span>
                            @else
                                <span class="text-slate-400 italic">Belum ditugaskan</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($case->isResolved())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Selesai
                                </span>
                            @elseif($case->isInvestigating())
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> Investigasi
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Kasus Terbuka
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <a href="{{ route('to-cases.show', $case) }}" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium transition">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Detail & Log</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="font-medium text-slate-600">Tidak ada data kasus TO</p>
                            <p class="text-xs text-slate-400 mt-1">Gunakan tombol 'Tandai NOP sebagai TO' jika ada tanah yang tidak jelas pemiliknya.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cases->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $cases->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
