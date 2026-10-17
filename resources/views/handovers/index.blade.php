@extends('layouts.app')

@section('title', 'Serah Terima Kas - SADAYA PBB')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Serah Terima Kas</h1>
            <p class="text-sm text-slate-600 mt-1">Pengalihan tanggung jawab kas penerimaan PBB dari Kasir/Kadus kepada Kolektor Desa</p>
        </div>
        <div>
            <a href="{{ route('handovers.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Serah Terima Kas</span>
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

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-700 text-xs font-semibold uppercase border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Serah Terima</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Kasir / Penyerah</th>
                        <th class="py-3 px-4">Kolektor / Penerima</th>
                        <th class="py-3 px-4 text-center">Trx</th>
                        <th class="py-3 px-4 text-right">Total Sistem</th>
                        <th class="py-3 px-4 text-right">Fisik Diserahkan</th>
                        <th class="py-3 px-4 text-right">Selisih</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($handovers as $h)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-mono font-medium text-slate-900">
                            {{ $h->handover_number }}
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-500 whitespace-nowrap">
                            {{ $h->handover_date->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-medium text-slate-800">{{ $h->cashier->name }}</span>
                            <span class="block text-[11px] text-slate-400 capitalize">{{ $h->cashier->role }} {{ $h->cashier->dusun ? '('.$h->cashier->dusun.')' : '' }}</span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-medium text-slate-800">{{ $h->collector->name }}</span>
                            <span class="block text-[11px] text-slate-400 capitalize">Kolektor Desa</span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-xs">
                            <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 rounded-full font-semibold">
                                {{ $h->payments->count() }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs text-slate-600">
                            Rp {{ number_format($h->system_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-sm font-semibold text-slate-900">
                            Rp {{ number_format($h->physical_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-xs whitespace-nowrap">
                            @if($h->difference_amount == 0)
                                <span class="text-emerald-600 font-medium">Rp 0 (Pas)</span>
                            @elseif($h->difference_amount > 0)
                                <span class="text-blue-600 font-semibold">+Rp {{ number_format($h->difference_amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-rose-600 font-semibold">-Rp {{ number_format(abs($h->difference_amount), 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($h->isReceived())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Diterima
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> Menunggu Konfirmasi
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <a href="{{ route('handovers.show', $h) }}" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-medium transition">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Detail & BA</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                            <p class="font-medium text-slate-600">Belum ada riwayat serah terima kas</p>
                            <p class="text-xs text-slate-400 mt-1">Kasir dapat mengajukan serah terima dari transaksi pembayaran yang telah dicatat.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($handovers->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $handovers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
