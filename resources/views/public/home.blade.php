@extends('layouts.public')

@section('content')
<div class="space-y-12">
    <!-- Hero Section -->
    <section class="bg-gradient-to-b from-emerald-950 via-emerald-900 to-slate-900 text-white py-16 sm:py-24 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <div class="max-w-5xl mx-auto text-center relative z-10 space-y-6">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-xs font-semibold text-emerald-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Pajak Bumi dan Bangunan Perdesaan dan Perkotaan (PBB-P2)</span>
            </div>
            
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white max-w-3xl mx-auto leading-tight">
                Transparansi & Pelayanan PBB {{ $village->name }}
            </h1>
            
            <p class="text-emerald-100/90 text-sm sm:text-base max-w-2xl mx-auto font-normal leading-relaxed">
                Pusat pencatatan administrasi, pemantauan realisasi penerimaan kas, dan verifikasi kuitansi pembayaran resmi berbasis QR Code untuk warga desa.
            </p>

            <div class="pt-4 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('login') }}" class="px-6 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-sm shadow-lg shadow-emerald-500/30 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    <span>Masuk Portal Petugas</span>
                </a>
            </div>
        </div>

        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_var(--tw-gradient-stops))] from-emerald-500/10 via-transparent to-transparent pointer-events-none"></div>
    </section>

    <!-- Aggregated Village Realization Stats -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 sm:-mt-20 relative z-20">
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200/80 p-6 sm:p-10">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 mb-6 border-b border-slate-100 gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Capaian Penerimaan PBB Tahun {{ $stats['active_tax_year'] }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Data agregat resmi pergerakan penerimaan kas PBB {{ $village->name }}.</p>
                </div>
                <div class="text-xs text-slate-500 font-medium">
                    Diperbarui real-time dari buku kas loket desa
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 sm:gap-8">
                <!-- Target Baku -->
                <div class="space-y-1">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Target Resmi Baku Desa</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900">
                        Rp {{ number_format($stats['target_amount'], 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-500">Ketetapan DHKP Bapenda</p>
                </div>

                <!-- Realisasi Sah -->
                <div class="space-y-1">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Realisasi Diterima</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-emerald-600">
                        Rp {{ number_format($stats['realization_amount'], 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-500">{{ $stats['total_paid_sppts'] }} Lembar SPPT Terbayar</p>
                </div>

                <!-- Persentase -->
                <div class="space-y-2">
                    <div class="flex justify-between items-baseline">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Persentase Capaian</p>
                        <span class="text-lg font-bold text-slate-800">{{ $stats['realization_percent'] }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden">
                        <div class="bg-emerald-600 h-3 rounded-full transition-all duration-500" style="width: {{ $stats['realization_percent'] }}%"></div>
                    </div>
                    <p class="text-xs text-slate-500">Dari target total per {{ date('d M Y') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features & Info Pillars -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-xs space-y-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Kuitansi Sah Ber-QR Code</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Setiap pembayaran tunai melalui Kadus atau loket kantor desa menghasilkan kuitansi resmi yang dapat dipindai untuk membuktikan keaslian transaksi.
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-xs space-y-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Mendukung Cicilan Pembayaran</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Wajib pajak dapat mencicil pembayaran pokok tagihan secara fleksibel. Sisa tagihan tercatat otomatis tanpa risiko kekeliruan perhitungan manual.
                </p>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-xs space-y-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Akuntabilitas Kas 3 Tingkat</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Penerimaan dari kasir, serah terima ke kolektor desa, hingga setoran resmi ke rekening Bank/Bapenda diaudit secara berlapis dan transparan.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
