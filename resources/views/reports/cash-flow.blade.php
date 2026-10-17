@extends('layouts.app')

@section('title', 'Laporan Arus Kas & Setoran Bank - SADAYA PBB')

@section('content')
<div class="space-y-6">
    <!-- Action Bar -->
    <div class="no-print bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-slate-800">Laporan Arus Kas 3-Tier & Setoran Bank</h1>
                <p class="text-xs text-slate-500">Rekonsiliasi serah terima kasir ke kolektor dan setoran ke kas daerah</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('reports.cash-flow', ['export' => 'excel']) }}" 
               class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Unduh Excel (.csv)</span>
            </a>
            <button onclick="window.print()" class="px-3 py-1.5 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-xs font-semibold">
                Cetak Laporan
            </button>
        </div>
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
                        LAP-PBB / KAS
                    </span>
                    <p class="text-[11px] text-slate-500 mt-1">{{ now()->translatedFormat('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="text-center mt-5">
                <h1 class="text-base sm:text-lg font-bold uppercase tracking-wide underline">
                    LAPORAN ARUS KAS 3-TIER & REKONSILIASI SETORAN BANK
                </h1>
                <p class="text-xs text-slate-600 mt-1">PEMERINTAH DESA {{ strtoupper($village->name) }}</p>
            </div>
        </div>

        <!-- Section 1: Serah Terima Kasir ke Kolektor -->
        <div class="mb-8">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                I. Rekapitulasi Berita Acara Serah Terima Kas (Kasir &rarr; Kolektor)
            </h3>
            <div class="overflow-x-auto border border-slate-300 rounded-lg">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead class="bg-slate-100 text-slate-700 uppercase font-bold border-b border-slate-300">
                        <tr>
                            <th class="py-2 px-3">No. BA</th>
                            <th class="py-2 px-3">Waktu</th>
                            <th class="py-2 px-3">Kasir / Penyerah</th>
                            <th class="py-2 px-3">Kolektor / Penerima</th>
                            <th class="py-2 px-3 text-center">Trx</th>
                            <th class="py-2 px-3 text-right">Total Sistem (Rp)</th>
                            <th class="py-2 px-3 text-right">Fisik (Rp)</th>
                            <th class="py-2 px-3 text-right">Selisih (Rp)</th>
                            <th class="py-2 px-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($handovers as $h)
                        <tr>
                            <td class="py-2 px-3 font-mono font-medium">{{ $h->handover_number }}</td>
                            <td class="py-2 px-3 text-slate-500 whitespace-nowrap">{{ $h->handover_date->format('d/m/Y H:i') }}</td>
                            <td class="py-2 px-3 font-medium">{{ $h->cashier->name }}</td>
                            <td class="py-2 px-3">{{ $h->collector->name }}</td>
                            <td class="py-2 px-3 text-center font-mono">{{ $h->payments->count() }}</td>
                            <td class="py-2 px-3 text-right font-mono">Rp {{ number_format($h->system_amount, 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($h->physical_amount, 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-right font-mono {{ $h->difference_amount == 0 ? 'text-slate-500' : 'text-rose-600 font-bold' }}">
                                Rp {{ number_format($h->difference_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-2 px-3 text-center capitalize">{{ $h->status }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-4 text-center text-slate-400">Belum ada data serah terima kas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-300">
                        <tr>
                            <td colspan="6" class="py-2 px-3 text-right uppercase">Total Fisik Diterima & Selisih:</td>
                            <td class="py-2 px-3 text-right font-mono text-emerald-800">Rp {{ number_format($totalHandoverFisik, 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-right font-mono text-slate-700">Rp {{ number_format($totalHandoverSelisih, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Section 2: Penyetoran ke Bank/Bapenda -->
        <div class="mb-8">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                II. Rekapitulasi Setoran Kas Kolektor ke Rekening Kas Daerah / Bank BJB
            </h3>
            <div class="overflow-x-auto border border-slate-300 rounded-lg">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead class="bg-slate-100 text-slate-700 uppercase font-bold border-b border-slate-300">
                        <tr>
                            <th class="py-2 px-3">No. Setoran</th>
                            <th class="py-2 px-3">Tanggal Setor</th>
                            <th class="py-2 px-3">Kolektor</th>
                            <th class="py-2 px-3">Lembaga / Bank Tujuan</th>
                            <th class="py-2 px-3">No. Ref / Slip Bank</th>
                            <th class="py-2 px-3 text-right">Nominal Setoran (Rp)</th>
                            <th class="py-2 px-3 text-center">Status</th>
                            <th class="py-2 px-3">Pengesah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($deposits as $d)
                        <tr>
                            <td class="py-2 px-3 font-mono font-medium">{{ $d->deposit_number }}</td>
                            <td class="py-2 px-3 text-slate-500 whitespace-nowrap">{{ $d->deposit_date->format('d/m/Y') }}</td>
                            <td class="py-2 px-3 font-medium">{{ $d->collector->name }}</td>
                            <td class="py-2 px-3">{{ $d->destination_bank }}</td>
                            <td class="py-2 px-3 font-mono text-slate-600">{{ $d->bank_ref_number ?? '-' }}</td>
                            <td class="py-2 px-3 text-right font-mono font-bold text-emerald-800">Rp {{ number_format($d->deposit_amount, 0, ',', '.') }}</td>
                            <td class="py-2 px-3 text-center capitalize">{{ $d->status }}</td>
                            <td class="py-2 px-3 text-slate-600">{{ $d->verifier ? $d->verifier->name : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-slate-400">Belum ada catatan setoran bank.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-300">
                        <tr>
                            <td colspan="5" class="py-2 px-3 text-right uppercase">Total Kas Masuk Bank (Disahkan):</td>
                            <td class="py-2 px-3 text-right font-mono text-emerald-800 text-sm">Rp {{ number_format($totalBankDeposited, 0, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-10">
            <div>
                <p class="text-slate-500">Mengetahui,<br>Kepala Desa {{ $village->name }}</p>
                <div class="h-24 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900">{{ $village->head_name ?? '..................................' }}</p>
                </div>
            </div>
            <div>
                <p class="text-slate-500">{{ $village->name }}, {{ now()->translatedFormat('d F Y') }}<br>Kolektor / Bendahara Desa</p>
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
