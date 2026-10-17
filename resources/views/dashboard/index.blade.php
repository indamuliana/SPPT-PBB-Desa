@extends('layouts.app')

@section('title', 'Dashboard Eksekutif - SADAYA PBB ' . $village->name)

@section('content')
<div class="space-y-6">
    <!-- Top Welcome Card -->
    <div class="bg-gradient-to-r from-emerald-800 to-teal-900 rounded-2xl p-6 sm:p-8 text-white shadow-lg shadow-emerald-950/20 relative overflow-hidden">
        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-xs font-semibold text-emerald-200 mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Dashboard Eksekutif & Monitoring Realisasi PBB-P2</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mb-2">
                Pemerintah Desa {{ $village->name }}
            </h1>
            <p class="text-emerald-100 text-sm leading-relaxed">
                Selamat datang, <strong>{{ $user->name }}</strong> ({{ $user->role_label }} {{ $user->dusun ? '('.$user->dusun.')' : '' }}). 
                Tahun Pajak Aktif: <strong class="underline decoration-emerald-400">{{ $stats['active_tax_year'] }}</strong>.
                Kecamatan {{ $village->district_name ?? 'Sukahaji' }}, Kabupaten {{ $village->regency_name ?? 'Majalengka' }}.
            </p>
        </div>
        <!-- Decorative Background Circle -->
        <div class="absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-emerald-500/10 pointer-events-none blur-2xl"></div>
    </div>

    <!-- 4 Main KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- 1. Ketetapan Baku SPPT -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Ketetapan Baku DHKP</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div>
                <p class="text-xl sm:text-2xl font-bold font-mono text-slate-900">Rp {{ number_format($stats['total_bills'], 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ number_format($stats['total_sppts'], 0, ',', '.') }} Objek Pajak (SPPT)</p>
            </div>
        </div>

        <!-- 2. Realisasi Penerimaan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Realisasi Penerimaan</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div>
                <p class="text-xl sm:text-2xl font-bold font-mono text-emerald-700">Rp {{ number_format($stats['realization_amount'], 0, ',', '.') }}</p>
                <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ min(100, $stats['realization_percent']) }}%"></div>
                </div>
                <p class="text-xs text-slate-500 mt-1 flex justify-between font-medium">
                    <span>{{ $stats['paid_sppts'] }} Lunas &bull; {{ $stats['partial_sppts'] }} Cicil</span>
                    <span class="font-bold text-emerald-700">{{ $stats['realization_percent'] }}%</span>
                </p>
            </div>
        </div>

        <!-- 3. Sisa Piutang / Tunggakan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Sisa Belum Tertagih</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div>
                <p class="text-xl sm:text-2xl font-bold font-mono text-amber-600">Rp {{ number_format($stats['remaining_amount'], 0, ',', '.') }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ number_format($stats['unpaid_sppts'], 0, ',', '.') }} SPPT Belum Lunas</p>
            </div>
        </div>

        <!-- 4. Target APBDes & Capaian -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Target Resmi Desa</span>
                <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <div>
                <p class="text-xl sm:text-2xl font-bold font-mono text-slate-900">Rp {{ number_format($stats['target_amount'], 0, ',', '.') }}</p>
                <div class="mt-2 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-teal-600 h-1.5 rounded-full" style="width: {{ min(100, $stats['target_percent']) }}%"></div>
                </div>
                <p class="text-xs text-slate-500 mt-1 flex justify-between font-medium">
                    <span>Target APBDes</span>
                    <span class="font-bold text-teal-700">{{ $stats['target_percent'] }}%</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Charts & Financial Pipeline Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Collections Trend Chart (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Tren Penerimaan Pembayaran Bulanan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Grafik realisasi kas masuk tahun {{ $stats['active_tax_year'] }}</p>
                </div>
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-semibold">
                    Tahun {{ $stats['active_tax_year'] }}
                </span>
            </div>

            <div class="relative h-64 w-full">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        <!-- 3-Tier Cash Ledger Summary (1 Col) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between space-y-4">
            <div>
                <h3 class="font-bold text-slate-800 text-base">Posisi Kas 3-Tier & Status TO</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pengawasan likuiditas kas desa real-time</p>
            </div>

            <div class="space-y-3">
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-amber-700 font-semibold block uppercase">1. Kas Dipegang Kasir</span>
                        <span class="text-xs text-amber-900">Belum diserahterimakan</span>
                    </div>
                    <span class="font-mono font-bold text-amber-900 text-sm">
                        Rp {{ number_format($stats['cashiers_held'], 0, ',', '.') }}
                    </span>
                </div>

                <div class="p-3 bg-teal-50 rounded-xl border border-teal-200 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-teal-700 font-semibold block uppercase">2. Kas Dipegang Kolektor</span>
                        <span class="text-xs text-teal-900">Fisik diterima, siap disetor</span>
                    </div>
                    <span class="font-mono font-bold text-teal-900 text-sm">
                        Rp {{ number_format($stats['collectors_held'], 0, ',', '.') }}
                    </span>
                </div>

                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-emerald-700 font-semibold block uppercase">3. Setoran Masuk Bank</span>
                        <span class="text-xs text-emerald-900">Resmi kas daerah tervalidasi</span>
                    </div>
                    <span class="font-mono font-bold text-emerald-900 text-sm">
                        Rp {{ number_format($stats['bank_deposited'], 0, ',', '.') }}
                    </span>
                </div>

                <div class="p-3 bg-rose-50 rounded-xl border border-rose-200 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-rose-700 font-semibold block uppercase">Objek Berstatus TO</span>
                        <span class="text-xs text-rose-900">{{ $stats['active_to_cases'] }} kasus dalam penelusuran</span>
                    </div>
                    <span class="font-mono font-bold text-rose-900 text-sm">
                        {{ $stats['total_to_objects'] }} NOP
                    </span>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('cashbook.index') }}" class="text-xs text-emerald-700 hover:text-emerald-800 font-bold underline flex items-center gap-1">
                    <span>Buka Buku Kas & Saldo</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('to-cases.index') }}" class="text-xs text-rose-600 hover:text-rose-700 font-medium">
                    Kasus TO &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Realization per Dusun & Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Realization per Dusun Table (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Capaian Realisasi per Wilayah Dusun</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Rincian target, pencapaian kasir, dan sisa tunggakan per dusun</p>
                </div>
                <a href="{{ route('reports.realization') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold underline">
                    Lihat Laporan Lengkap
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-slate-600 uppercase font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Wilayah Dusun</th>
                            <th class="py-3 px-4 text-center">Objek (SPPT)</th>
                            <th class="py-3 px-4 text-right">Target Baku</th>
                            <th class="py-3 px-4 text-right">Realisasi</th>
                            <th class="py-3 px-4 text-right">Sisa Piutang</th>
                            <th class="py-3 px-4 text-center">Capaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($dusunStats as $d)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $d['dusun'] }}
                            </td>
                            <td class="py-3 px-4 text-center font-mono">
                                {{ number_format($d['total_sppts'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                Rp {{ number_format($d['total_bill'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                                Rp {{ number_format($d['paid_amount'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-right font-mono text-amber-600">
                                Rp {{ number_format($d['remaining'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold {{ $d['percent'] >= 80 ? 'bg-emerald-100 text-emerald-800' : ($d['percent'] >= 50 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                    {{ $d['percent'] }}%
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data DHKP/SPPT yang diimpor.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payments List (1 Col) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Kuitansi Terbaru</span>
                </h3>
                <a href="{{ route('payments.history') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Semua &rarr;</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentPayments as $rp)
                <div class="py-2.5 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-mono font-bold text-slate-800 block">{{ $rp->receipt_number }}</span>
                        <span class="text-slate-500 text-[11px]">{{ $rp->sppt->taxObject->taxpayer->name }} &bull; {{ $rp->payment_date->format('d/m H:i') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-mono font-bold text-emerald-700 block">Rp {{ number_format($rp->total_amount, 0, ',', '.') }}</span>
                        <span class="text-[10px] text-slate-400 capitalize">{{ $rp->cashier->name }}</span>
                    </div>
                </div>
                @empty
                <p class="py-6 text-center text-xs text-slate-400">Belum ada transaksi pembayaran yang tercatat.</p>
                @endforelse
            </div>

            <div class="pt-2 border-t border-slate-100">
                <a href="{{ route('payments.pos') }}" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold text-center block shadow-sm transition">
                    Buka Loket Kasir / POS
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('monthlyTrendChart');
    if (!ctx) return;

    const labels = @json($monthlyLabels);
    const data = @json($monthlyTrend);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Penerimaan Kas (Rp)',
                data: data,
                backgroundColor: 'rgba(16, 185, 129, 0.75)',
                borderColor: 'rgb(5, 150, 105)',
                borderWidth: 1.5,
                borderRadius: 6,
                maxBarThickness: 36,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            if (value >= 1000000) {
                                return (value / 1000000) + ' Jt';
                            } else if (value >= 1000) {
                                return (value / 1000) + ' Rb';
                            }
                            return value;
                        },
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        color: 'rgba(241, 245, 249, 1)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 11
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endsection
