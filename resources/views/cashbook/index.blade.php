@extends('layouts.app')

@section('title', 'Buku Kas & Saldo Real-Time - SADAYA PBB')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Buku Kas & Rekonsiliasi Real-Time</h1>
            <p class="text-sm text-slate-600 mt-1">
                Pengawasan 3-Tier Arus Kas: Kasir / Kadus &rarr; Serah Terima &rarr; Kolektor Desa &rarr; Bank / Bapenda
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-4 py-2 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-semibold transition flex items-center gap-2 no-print">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Buku Kas</span>
            </button>
        </div>
    </div>

    <!-- Reconciliation Verification Banner -->
    @php
        $calculatedAccounted = $summary['cashiers_held'] + $summary['in_transit'] + $summary['collectors_held'] + $summary['bank_deposited'] - $summary['total_difference'];
        $isBalanced = abs($calculatedAccounted - $summary['total_payments']) < 0.01;
    @endphp

    <div class="p-4 rounded-xl border {{ $isBalanced ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            @if($isBalanced)
                <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm">Status Rekonsiliasi: SEIMBANG & VALID</h3>
                    <p class="text-xs text-emerald-700 mt-0.5">
                        Seluruh kas penerimaan pembayaran (Rp {{ number_format($summary['total_payments'], 0, ',', '.') }}) telah teridentifikasi posisinya secara presisi pada buku kas 3-tier.
                    </p>
                </div>
            @else
                <div class="w-10 h-10 rounded-full bg-amber-600 text-white flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-sm">Status Rekonsiliasi: PERLU PENYESUAIAN</h3>
                    <p class="text-xs text-amber-700 mt-0.5">
                        Terdapat selisih pencatatan antara total kuitansi sistem dengan posisi kas tercatat.
                    </p>
                </div>
            @endif
        </div>
        <div class="text-right font-mono text-xs font-semibold whitespace-nowrap">
            Desa {{ $village->name ?? 'Sukahaji' }} &bull; {{ now()->translatedFormat('d F Y H:i') }}
        </div>
    </div>

    <!-- 6 KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <!-- 1. Total Pembayaran Valid -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">1. Total Penerimaan Valid</span>
                <span class="p-1.5 bg-blue-50 text-blue-600 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-slate-900">
                Rp {{ number_format($summary['total_payments'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Akumulasi seluruh kuitansi sah yang diterbitkan</p>
        </div>

        <!-- 2. Kas Dipegang Kasir -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">2. Kas di Kasir / Kadus</span>
                <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-amber-600">
                Rp {{ number_format($summary['cashiers_held'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Uang belum diserahterimakan ke kolektor</p>
        </div>

        <!-- 3. Kas dalam Serah Terima (In Transit) -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">3. Kas Sedang Diserahkan</span>
                <span class="p-1.5 bg-indigo-50 text-indigo-600 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-indigo-600">
                Rp {{ number_format($summary['in_transit'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Diajukan kasir, menunggu konfirmasi kolektor</p>
        </div>

        <!-- 4. Kas Dipegang Kolektor -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">4. Kas di Kolektor Desa</span>
                <span class="p-1.5 bg-teal-50 text-teal-600 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-teal-700">
                Rp {{ number_format($summary['collectors_held'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Fisik diterima kolektor, siap disetor ke bank</p>
        </div>

        <!-- 5. Kas Resmi Masuk Bank -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">5. Setoran Bank Disahkan</span>
                <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono text-emerald-600">
                Rp {{ number_format($summary['bank_deposited'], 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-500 mt-1">Resmi masuk kas daerah / BJB tervalidasi</p>
        </div>

        <!-- 6. Akumulasi Selisih Kas Fisik -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">6. Akumulasi Selisih Fisik</span>
                <span class="p-1.5 bg-slate-100 text-slate-600 rounded-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="text-2xl font-bold font-mono {{ $summary['total_difference'] == 0 ? 'text-slate-800' : ($summary['total_difference'] > 0 ? 'text-blue-600' : 'text-rose-600') }}">
                @if($summary['total_difference'] == 0)
                    Rp 0
                @else
                    {{ $summary['total_difference'] > 0 ? '+' : '' }}Rp {{ number_format($summary['total_difference'], 0, ',', '.') }}
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-1">Variansi fisik saat serah terima kas</p>
        </div>
    </div>

    <!-- Section: Buku Kas Umum (Jurnal Arus Kas & Saldo Berjalan) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/70">
            <div>
                <h2 class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Buku Kas Umum: Jurnal Arus Kas &amp; Saldo Berjalan</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Catatan kronologis penerimaan tunai dari WP (+) dan penyetoran ke kas daerah / bank (-).</p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 font-semibold font-mono">
                    Saldo Kas Desa: Rp {{ number_format($summary['current_village_cash'], 0, ',', '.') }}
                </span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-2.5 px-3 w-12 text-center">No</th>
                        <th class="py-2.5 px-3 w-32">Waktu / Tanggal</th>
                        <th class="py-2.5 px-3 w-36">No. Referensi</th>
                        <th class="py-2.5 px-3">Uraian Transaksi</th>
                        <th class="py-2.5 px-3 w-36">Pihak Terkait</th>
                        <th class="py-2.5 px-3 w-32 text-right">Uang Masuk (+ Rp)</th>
                        <th class="py-2.5 px-3 w-32 text-right">Uang Keluar (- Rp)</th>
                        <th class="py-2.5 px-3 w-36 text-right">Saldo Kas (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($ledger as $idx => $entry)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-2.5 px-3 text-center text-slate-400 font-mono text-[11px]">{{ $idx + 1 }}</td>
                        <td class="py-2.5 px-3 text-slate-600 whitespace-nowrap">
                            {{ $entry['datetime']->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-800 text-[11px]">
                            {{ $entry['ref'] }}
                        </td>
                        <td class="py-2.5 px-3 text-slate-700 leading-relaxed">
                            {{ $entry['description'] }}
                        </td>
                        <td class="py-2.5 px-3 text-slate-600 font-medium truncate max-w-[140px]">
                            {{ $entry['party'] }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700">
                            {{ $entry['amount_in'] > 0 ? '+ ' . number_format($entry['amount_in'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-rose-600">
                            {{ $entry['amount_out'] > 0 ? '- ' . number_format($entry['amount_out'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 bg-slate-50/50">
                            Rp {{ number_format($entry['running_balance'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">
                            Belum ada catatan mutasi arus kas penerimaan atau setoran bank.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-100 border-t-2 border-slate-300 font-bold text-slate-800">
                    <tr>
                        <td colspan="5" class="py-3 px-3 text-right uppercase text-[11px] tracking-wide">
                            Total Arus Kas Kumulatif:
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-emerald-800">
                            + Rp {{ number_format($summary['total_payments'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-rose-700">
                            - Rp {{ number_format($summary['bank_deposited'], 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-emerald-900 text-sm">
                            Rp {{ number_format($summary['current_village_cash'], 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Section Tables: Saldo Per Petugas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Kasir Breakdown -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Posisi Kas di Petugas Kasir / Kadus</span>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-600 uppercase font-semibold">
                        <tr>
                            <th class="py-2.5 px-4">Nama Petugas</th>
                            <th class="py-2.5 px-4">Wilayah / Dusun</th>
                            <th class="py-2.5 px-4 text-right">Total Dipungut</th>
                            <th class="py-2.5 px-4 text-right">Kas Belum Diserah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($cashiers as $c)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-4 font-medium text-slate-800">
                                {{ $c['user']->name }}
                            </td>
                            <td class="py-2.5 px-4 text-slate-500 capitalize">
                                {{ $c['user']->dusun ?? '-' }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-slate-700">
                                Rp {{ number_format($c['total_received'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono font-bold {{ $c['unhanded_cash'] > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                Rp {{ number_format($c['unhanded_cash'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">Belum ada kasir</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kolektor Breakdown -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Posisi Kas di Petugas Kolektor Desa</span>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-600 uppercase font-semibold">
                        <tr>
                            <th class="py-2.5 px-4">Nama Kolektor</th>
                            <th class="py-2.5 px-4">Role</th>
                            <th class="py-2.5 px-4 text-right">Telah Disetor ke Bank</th>
                            <th class="py-2.5 px-4 text-right">Saldo Kas Dipegang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($collectors as $col)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-4 font-medium text-slate-800">
                                {{ $col['user']->name }}
                            </td>
                            <td class="py-2.5 px-4 text-slate-500 capitalize">
                                Kolektor PBB
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono text-emerald-600 font-semibold">
                                Rp {{ number_format($col['total_deposited'], 0, ',', '.') }}
                            </td>
                            <td class="py-2.5 px-4 text-right font-mono font-bold {{ $col['held_balance'] > 0 ? 'text-teal-700' : 'text-slate-400' }}">
                                Rp {{ number_format($col['held_balance'], 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">Belum ada kolektor</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background-color: #fff !important; }
}
</style>
@endsection
