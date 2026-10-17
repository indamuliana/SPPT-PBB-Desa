@extends('layouts.app')

@section('title', 'Laporan Penerimaan Kuitansi Kasir - SADAYA PBB')

@section('content')
<div class="space-y-6">
    <!-- Action Bar -->
    <div class="no-print bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-slate-800">Laporan Transaksi Kuitansi Kasir</h1>
                <p class="text-xs text-slate-500">Periode: {{ date('d/m/Y', strtotime($startDate)) }} s.d. {{ date('d/m/Y', strtotime($endDate)) }}</p>
            </div>
        </div>

        <form method="GET" action="{{ route('reports.payments') }}" class="flex flex-wrap items-center gap-2">
            <div>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs">
            </div>
            <span class="text-xs text-slate-400">s/d</span>
            <div>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs">
            </div>
            <div>
                <select name="cashier_id" class="px-2.5 py-1.5 border border-slate-300 rounded-lg text-xs bg-white">
                    <option value="">-- Semua Kasir --</option>
                    @foreach($cashiers as $c)
                        <option value="{{ $c->id }}" {{ $cashierId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold">
                Filter
            </button>
            <a href="{{ route('reports.payments', array_merge(request()->all(), ['export' => 'excel'])) }}" 
               class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Unduh (.csv)</span>
            </a>
            <button type="button" onclick="window.print()" class="px-3 py-1.5 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-xs font-semibold">
                Cetak
            </button>
        </form>
    </div>

    <!-- Official Document Paper -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 sm:p-10 text-slate-800 printable-area">
        
        <div class="border-b-2 border-slate-800 pb-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs uppercase tracking-widest font-semibold text-slate-500">Pemerintah Kabupaten {{ $village->regency_name ?? 'Majalengka' }}</h3>
                    <h2 class="text-base uppercase font-bold text-slate-800">Kecamatan {{ $village->district_name ?? 'Sukahaji' }} &bull; Desa {{ $village->name ?? 'Sukahaji' }}</h2>
                    <p class="text-xs text-slate-500">{{ $village->address ?? 'Kantor Desa' }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-slate-100 border border-slate-300 font-mono text-xs font-bold uppercase rounded">
                        LAP-PBB / KUITANSI
                    </span>
                    <p class="text-[11px] text-slate-500 mt-1">{{ now()->translatedFormat('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="text-center mt-5">
                <h1 class="text-base sm:text-lg font-bold uppercase tracking-wide underline">
                    LAPORAN TRANSAKSI PENERIMAAN KUITANSI PBB-P2
                </h1>
                <p class="text-xs text-slate-600 mt-1">PERIODE: {{ date('d F Y', strtotime($startDate)) }} s.d. {{ date('d F Y', strtotime($endDate)) }}</p>
            </div>
        </div>

        <div class="overflow-x-auto border border-slate-300 rounded-lg mb-8">
            <table class="w-full text-left text-xs text-slate-800">
                <thead class="bg-slate-100 text-slate-700 uppercase font-bold border-b border-slate-300">
                    <tr>
                        <th class="py-2.5 px-3 text-center w-8">No</th>
                        <th class="py-2.5 px-3">No. Kuitansi</th>
                        <th class="py-2.5 px-3">Waktu Bayar</th>
                        <th class="py-2.5 px-3">NOP</th>
                        <th class="py-2.5 px-3">Wajib Pajak</th>
                        <th class="py-2.5 px-3">Dusun</th>
                        <th class="py-2.5 px-3 text-center">Tahun</th>
                        <th class="py-2.5 px-3">Kasir</th>
                        <th class="py-2.5 px-3 text-right">Pokok (Rp)</th>
                        <th class="py-2.5 px-3 text-right">Denda (Rp)</th>
                        <th class="py-2.5 px-3 text-right">Total Bayar (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($payments as $idx => $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2 px-3 text-center text-slate-500 font-mono">{{ $idx + 1 }}</td>
                        <td class="py-2 px-3 font-mono font-medium text-slate-900">{{ $p->receipt_number }}</td>
                        <td class="py-2 px-3 text-slate-500 whitespace-nowrap">{{ $p->payment_date->format('d/m/Y H:i') }}</td>
                        <td class="py-2 px-3 font-mono text-slate-700">{{ $p->sppt->taxObject->formatted_nop }}</td>
                        <td class="py-2 px-3 font-medium text-slate-800">{{ $p->sppt->taxObject->taxpayer->name }}</td>
                        <td class="py-2 px-3 text-slate-600 capitalize">{{ $p->sppt->taxObject->dusun ?? '-' }}</td>
                        <td class="py-2 px-3 font-mono text-center">{{ $p->sppt->tax_year }}</td>
                        <td class="py-2 px-3 text-slate-700">{{ $p->cashier->name }}</td>
                        <td class="py-2 px-3 text-right font-mono">Rp {{ number_format($p->amount_principal, 0, ',', '.') }}</td>
                        <td class="py-2 px-3 text-right font-mono text-amber-700">Rp {{ number_format($p->amount_fine, 0, ',', '.') }}</td>
                        <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-8 text-center text-slate-400">Tidak ada kuitansi pembayaran pada rentang tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 text-slate-900 font-bold border-t-2 border-slate-400">
                    <tr>
                        <td colspan="8" class="py-3 px-3 text-center uppercase">TOTAL ({{ $payments->count() }} KUITANSI):</td>
                        <td class="py-3 px-3 text-right font-mono">Rp {{ number_format($totalPrincipal, 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right font-mono text-amber-800">Rp {{ number_format($totalFine, 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right font-mono text-emerald-800 text-sm">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-10">
            <div>
                <p class="text-slate-500">Mengetahui,<br>Kepala Desa {{ $village->name }}</p>
                <div class="h-24 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900">{{ $village->head_name ?? '..................................' }}</p>
                </div>
            </div>
            <div>
                <p class="text-slate-500">{{ $village->name }}, {{ now()->translatedFormat('d F Y') }}<br>Bendahara / Petugas PBB</p>
                <div class="h-24 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900">{{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background-color: #fff !important; }
    .printable-area { border: none !important; box-shadow: none !important; padding: 0 !important; }
}
</style>
@endsection
