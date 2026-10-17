@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Daftar Ketetapan SPPT & DHKP</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Data ketetapan resmi tagihan PBB-P2 dari Bapenda per tahun pajak di {{ \App\Models\Village::current()->name }}.</p>
        </div>
        @if(auth()->user()->isAdmin())
        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
            <a href="{{ route('sppts.create-unified') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Perekaman Terpadu (Single-Page)</span>
            </a>
            <a href="{{ route('sppts.import.view') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-semibold text-xs shadow-md shadow-teal-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                <span>Import DHKP</span>
            </a>
            <a href="{{ route('sppts.import.view', ['mode' => 'previous_year']) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs shadow-md shadow-indigo-600/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Salin dari Tahun Lalu</span>
            </a>
            <a href="{{ route('sppts.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Manual</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Warnings from import if any -->
    @if(session('import_warnings'))
    <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-xs text-amber-800 space-y-1">
        <p class="font-bold flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span>Catatan Baris yang Dilewati Saat Import:</span>
        </p>
        <ul class="list-disc list-inside space-y-0.5 font-mono text-[11px] text-amber-700">
            @foreach(session('import_warnings') as $warn)
            <li>{{ $warn }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Summary KPI for Selected Tax Year -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Total Tagihan ({{ $selectedYear }})</span>
            <p class="text-base sm:text-xl font-bold text-slate-900">Rp {{ number_format($summary['total_bills'], 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-400 mt-1">{{ $summary['total_count'] }} SPPT Terdata</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Sudah Terbayar</span>
            <p class="text-base sm:text-xl font-bold text-emerald-600">Rp {{ number_format($summary['total_paid'], 0, ',', '.') }}</p>
            <p class="text-[11px] text-emerald-600 font-medium mt-1">{{ $summary['paid_count'] }} Lunas &bull; {{ $summary['partial_count'] }} Cicilan</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Sisa Tagihan Tertunggak</span>
            <p class="text-base sm:text-xl font-bold text-amber-600">Rp {{ number_format($summary['total_remaining'], 0, ',', '.') }}</p>
            <p class="text-[11px] text-amber-600 font-medium mt-1">{{ $summary['unpaid_count'] + $summary['partial_count'] }} Belum Lunas</p>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Persentase Realisasi</span>
            @php
                $pct = $summary['total_bills'] > 0 ? round(($summary['total_paid'] / $summary['total_bills']) * 100, 1) : 0;
            @endphp
            <p class="text-base sm:text-xl font-bold text-blue-600">{{ $pct }}%</p>
            <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('sppts.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:w-36">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Tahun Pajak</label>
                <select name="year" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white font-bold text-emerald-700">
                    @foreach($years as $yr)
                    <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-40">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Status Bayar</label>
                <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="">Semua Status</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Cicilan</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Lunas</option>
                </select>
            </div>

            <div class="w-full sm:w-44">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Wilayah Dusun</label>
                <select name="dusun" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="">Semua Dusun</option>
                    @foreach($dusunList as $d)
                    <option value="{{ $d }}" {{ request('dusun') === $d ? 'selected' : '' }}>{{ $d }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:flex-1">
                <label class="block text-[10px] font-bold uppercase text-slate-400 mb-1">Pencarian NOP / Nama WP</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik NOP atau nama wajib pajak..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div class="w-full sm:w-auto self-end">
                <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition">
                    Filter
                </button>
            </div>
            @if(request('search') || request('status') || request('dusun') || request('year'))
            <div class="self-end pb-1.5">
                <a href="{{ route('sppts.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            </div>
            @endif
        </form>
    </div>

    <!-- SPPT Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">NOP & Objek Pajak</th>
                        <th class="px-6 py-3.5">Nama Wajib Pajak</th>
                        <th class="px-6 py-3.5">Wilayah Dusun</th>
                        <th class="px-6 py-3.5 text-right">PBB Terutang (Rp)</th>
                        <th class="px-6 py-3.5 text-right">Terbayar (Rp)</th>
                        <th class="px-6 py-3.5 text-right">Sisa Tagihan (Rp)</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($sppts as $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono font-bold text-slate-900 text-xs tracking-wide">{{ $item->taxObject->formatted_nop }}</span>
                            <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $item->taxObject->address ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900">{{ $item->taxObject->taxpayer?->name ?? 'Belum Ditautkan' }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $item->taxObject->taxpayer?->nik ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                            {{ $item->taxObject->dusunRelation?->name ?? $item->taxObject->dusun ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-semibold text-slate-900">
                            Rp {{ number_format($item->pbb_terutang, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono text-emerald-600 font-medium">
                            Rp {{ number_format($item->paid_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-bold {{ $item->remaining_bill > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                            Rp {{ number_format($item->remaining_bill, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($item->taxObject->isTo())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                Blokir TO
                            </span>
                            @elseif($item->isPaid())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Lunas
                            </span>
                            @elseif($item->isPartial())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                Cicilan
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                Belum Bayar
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('tax-objects.show', $item->taxObject) }}" class="inline-flex items-center text-xs font-semibold text-slate-600 hover:text-slate-900 p-1 rounded hover:bg-slate-100" title="Detail Objek">
                                    Detail
                                </a>
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('sppts.edit', $item) }}" class="inline-flex items-center text-xs font-semibold text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50" title="Edit Ketetapan SPPT">
                                    Edit
                                </a>
                                @if($item->payments()->count() === 0)
                                <form action="{{ route('sppts.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ketetapan SPPT ini secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center text-xs font-semibold text-rose-600 hover:text-rose-800 p-1 rounded hover:bg-rose-50" title="Hapus SPPT">
                                        Hapus
                                    </button>
                                </form>
                                @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-slate-400">
                            Belum ada data ketetapan SPPT untuk tahun pajak {{ $selectedYear }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $sppts->links() }}
        </div>
    </div>
</div>
@endsection
