@extends('layouts.app')

@section('title', 'Pusat Laporan & Rekapitulasi - SADAYA PBB')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Pusat Laporan & Rekapitulasi PBB-P2</h1>
            <p class="text-sm text-slate-600 mt-1">
                Laporan resmi administrasi penerimaan, realisasi per dusun, arus kas 3-tier, serta dokumen ekspor Excel/CSV Desa {{ $village->name }}
            </p>
        </div>
        <div class="text-xs font-mono bg-slate-100 text-slate-600 px-3 py-1.5 rounded-lg border border-slate-200">
            Tahun Pajak Aktif: <strong>{{ $village->active_tax_year }}</strong>
        </div>
    </div>

    <!-- 4 Report Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- 1. Laporan Realisasi per Dusun -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between hover:border-emerald-500/50 hover:shadow-md transition">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">1. Laporan Realisasi per Wilayah / Dusun</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Rekapitulasi target baku ketetapan DHKP, jumlah SPPT lunas/belum, realisasi penerimaan uang, dan sisa tunggakan per dusun atau blok wilayah desa.
                    </p>
                </div>
            </div>
            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('reports.realization') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                    Buka Laporan
                </a>
                <a href="{{ route('reports.realization', ['export' => 'excel']) }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Ekspor Excel (.csv)</span>
                </a>
            </div>
        </div>

        <!-- 2. Laporan Kuitansi Penerimaan Kasir -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between hover:border-blue-500/50 hover:shadow-md transition">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">2. Laporan Kuitansi Penerimaan Kasir</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Daftar rincian seluruh kuitansi pembayaran yang diterbitkan di loket kasir desa, dapat difilter berdasarkan rentang tanggal dan nama petugas kasir/kadus.
                    </p>
                </div>
            </div>
            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('reports.payments') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition">
                    Buka Laporan
                </a>
                <a href="{{ route('reports.payments', ['export' => 'excel']) }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Ekspor Excel (.csv)</span>
                </a>
            </div>
        </div>

        <!-- 3. Laporan Arus Kas & Setoran Bank -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between hover:border-teal-500/50 hover:shadow-md transition">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">3. Laporan Arus Kas & Setoran Bank</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Rekapitulasi pengalihan uang fisik 3-tier: bukti serah terima kasir ke kolektor, pencatatan selisih kas fisik, dan riwayat setoran kolektor ke kas daerah / Bank BJB.
                    </p>
                </div>
            </div>
            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('reports.cash-flow') }}" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-xs font-bold transition">
                    Buka Laporan
                </a>
                <a href="{{ route('reports.cash-flow', ['export' => 'excel']) }}" class="text-xs text-teal-600 hover:text-teal-700 font-semibold underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Ekspor Excel (.csv)</span>
                </a>
            </div>
        </div>

        <!-- 4. Laporan Kasus TO & Mutasi Objek -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between hover:border-amber-500/50 hover:shadow-md transition">
            <div class="space-y-3">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">4. Laporan Status TO & Mutasi SPPT</h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Daftar objek pajak berstatus TO (Tanpa Objek / Pemilik Belum Jelas), status investigasi lapangan, serta buku register Berita Acara mutasi balik nama NOP.
                    </p>
                </div>
            </div>
            <div class="pt-5 mt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('reports.to-and-mutations') }}" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition">
                    Buka Laporan
                </a>
                <a href="{{ route('reports.to-and-mutations', ['export' => 'excel']) }}" class="text-xs text-amber-600 hover:text-amber-700 font-semibold underline flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Ekspor Excel (.csv)</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
