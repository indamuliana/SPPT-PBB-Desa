@extends('layouts.app')

@section('title', 'Laporan Realisasi PBB-P2 per Dusun - SADAYA PBB')

@section('content')
<div class="space-y-6">
    <!-- Action Bar (Hidden on print) -->
    <div class="no-print bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-slate-800">Laporan Realisasi PBB-P2 per Wilayah Dusun</h1>
                <p class="text-xs text-slate-500">Tahun Pajak {{ $taxYear }} &bull; Desa {{ $village->name }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('reports.realization') }}" class="flex items-center gap-2">
                <select name="tax_year" onchange="this.form.submit()" class="px-3 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-emerald-500 bg-white">
                    @for($y = (int)date('Y') + 1; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $taxYear == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </form>

            <a href="{{ route('reports.realization', ['tax_year' => $taxYear, 'export' => 'excel']) }}" 
               class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Unduh Excel (.csv)</span>
            </a>

            <button onclick="window.print()" class="px-3 py-1.5 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Laporan</span>
            </button>
        </div>
    </div>

    <!-- Official Report Document Paper -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 sm:p-10 text-slate-800 printable-area">
        
        <!-- Header Laporan Resmi -->
        <div class="border-b-2 border-slate-800 pb-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs uppercase tracking-widest font-semibold text-slate-500">Pemerintah Kabupaten {{ $village->regency_name ?? 'Majalengka' }}</h3>
                    <h2 class="text-base uppercase font-bold text-slate-800">Kecamatan {{ $village->district_name ?? 'Sukahaji' }} &bull; Desa {{ $village->name ?? 'Sukahaji' }}</h2>
                    <p class="text-xs text-slate-500">{{ $village->address ?? 'Kantor Desa' }} | Kode Pos 45471</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-slate-100 border border-slate-300 font-mono text-xs font-bold uppercase rounded">
                        LAP-PBB / DUSUN
                    </span>
                    <p class="text-[11px] text-slate-500 mt-1">Dicetak: {{ now()->translatedFormat('d F Y H:i') }}</p>
                </div>
            </div>
            <div class="text-center mt-5">
                <h1 class="text-base sm:text-lg font-bold uppercase tracking-wide underline">
                    LAPORAN REALISASI PENERIMAAN PBB-P2 PER WILAYAH DUSUN
                </h1>
                <p class="text-xs text-slate-600 mt-1">TAHUN PAJAK {{ $taxYear }}</p>
            </div>
        </div>

        <!-- Tabel Laporan -->
        <div class="overflow-x-auto border border-slate-300 rounded-lg mb-8">
            <table class="w-full text-left text-xs text-slate-800">
                <thead class="bg-slate-100 text-slate-700 uppercase font-bold border-b border-slate-300">
                    <tr>
                        <th class="py-2.5 px-3 text-center w-8 border-r border-slate-200">No</th>
                        <th class="py-2.5 px-3 border-r border-slate-200">Wilayah / Dusun</th>
                        <th class="py-2.5 px-3 text-center border-r border-slate-200">Baku SPPT</th>
                        <th class="py-2.5 px-3 text-center border-r border-slate-200">Lunas</th>
                        <th class="py-2.5 px-3 text-center border-r border-slate-200">Belum</th>
                        <th class="py-2.5 px-3 text-right border-r border-slate-200">Ketetapan Baku (Rp)</th>
                        <th class="py-2.5 px-3 text-right border-r border-slate-200">Realisasi Masuk (Rp)</th>
                        <th class="py-2.5 px-3 text-right border-r border-slate-200">Sisa Tunggakan (Rp)</th>
                        <th class="py-2.5 px-3 text-center">Capaian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($dusunStats as $idx => $d)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-3 text-center text-slate-500 font-mono border-r border-slate-200">{{ $idx + 1 }}</td>
                        <td class="py-2.5 px-3 font-bold text-slate-800 border-r border-slate-200">{{ $d['dusun'] }}</td>
                        <td class="py-2.5 px-3 text-center font-mono border-r border-slate-200">{{ number_format($d['total_sppts'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-center font-mono text-emerald-700 font-semibold border-r border-slate-200">{{ number_format($d['count_paid'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-center font-mono text-amber-700 border-r border-slate-200">{{ number_format($d['count_unpaid'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right font-mono text-slate-700 border-r border-slate-200">Rp {{ number_format($d['total_bill'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700 border-r border-slate-200">Rp {{ number_format($d['paid_amount'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-right font-mono text-amber-600 border-r border-slate-200">Rp {{ number_format($d['remaining'], 0, ',', '.') }}</td>
                        <td class="py-2.5 px-3 text-center font-bold">
                            <span class="inline-block px-2 py-0.5 rounded text-[11px] {{ $d['percent'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($d['percent'] >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ $d['percent'] }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400">Tidak ada data untuk tahun pajak ini.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 text-slate-900 font-bold border-t-2 border-slate-400">
                    <tr>
                        <td colspan="2" class="py-3 px-3 text-center uppercase border-r border-slate-300">TOTAL KESELURUHAN:</td>
                        <td class="py-3 px-3 text-center font-mono border-r border-slate-300">{{ number_format($totals['total_sppts'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-center font-mono text-emerald-800 border-r border-slate-300">{{ number_format($totals['count_paid'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-center font-mono text-amber-800 border-r border-slate-300">{{ number_format($totals['count_unpaid'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right font-mono border-r border-slate-300">Rp {{ number_format($totals['total_bill'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right font-mono text-emerald-800 border-r border-slate-300">Rp {{ number_format($totals['paid_amount'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right font-mono text-amber-800 border-r border-slate-300">Rp {{ number_format($totals['remaining'], 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-center font-mono text-emerald-800 text-sm">{{ $totals['percent'] }}%</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Tanda Tangan Pejabat Desa -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-10">
            <div>
                <p class="text-slate-500">Mengetahui,<br>Kepala Desa {{ $village->name }}</p>
                <div class="h-24 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900">{{ $village->head_name ?? '..................................' }}</p>
                </div>
                <p class="text-slate-500 text-[11px]">Kepala Desa</p>
            </div>

            <div>
                <p class="text-slate-500">{{ $village->name }}, {{ now()->translatedFormat('d F Y') }}<br>Koordinator Pengelola PBB Desa</p>
                <div class="h-24 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900">{{ auth()->user()->name }}</p>
                </div>
                <p class="text-slate-500 text-[11px]">{{ ucfirst(auth()->user()->role) }}</p>
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
