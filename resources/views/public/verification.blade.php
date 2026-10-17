@extends('layouts.public')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8 sm:py-12 space-y-6">
    <div class="text-center space-y-2">
        <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider font-mono">PORTAL RESMI VERIFIKASI PBB DESA</span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Hasil Verifikasi Kuitansi</h1>
        <p class="text-xs text-slate-500">Pemeriksaan keaslian tanda bukti pembayaran Pajak Bumi dan Bangunan.</p>
    </div>

    <!-- Main Verification Result Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
        <!-- Status Banner -->
        @if($payment->isValid())
        <div class="bg-emerald-600 text-white p-6 text-center space-y-1">
            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-2 text-white">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="text-lg font-black tracking-wide uppercase">DOKUMEN RESMI DAN SAH</h2>
            <p class="text-xs text-emerald-100">Kuitansi ini tercatat secara sah di buku kas penerimaan {{ $village->name }}.</p>
        </div>
        @elseif($payment->isCancelled())
        <div class="bg-rose-600 text-white p-6 text-center space-y-1">
            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-2 text-white">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <h2 class="text-lg font-black tracking-wide uppercase">DOKUMEN TELAH DIBATALKAN</h2>
            <p class="text-xs text-rose-100">Transaksi kuitansi ini telah dibatalkan resmi oleh Administrator Desa.</p>
        </div>
        @else
        <div class="bg-amber-500 text-white p-6 text-center space-y-1">
            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-2 text-white">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h2 class="text-lg font-black tracking-wide uppercase">DALAM PENGAJUAN PEMBATALAN</h2>
            <p class="text-xs text-amber-100">Dokumen sedang dalam proses pemeriksaan koreksi oleh pihak berwenang.</p>
        </div>
        @endif

        <!-- Document Details -->
        <div class="p-6 sm:p-8 space-y-4 text-xs">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <span class="text-slate-400 font-semibold uppercase">Nomor Kuitansi</span>
                <span class="font-mono font-black text-slate-900 text-sm sm:text-base">{{ $payment->receipt_number }}</span>
            </div>

            <div class="flex items-center justify-between py-1">
                <span class="text-slate-500">Waktu Pembayaran</span>
                <span class="font-medium text-slate-800">{{ $payment->payment_date->format('d F Y, H:i') }} WIB</span>
            </div>

            <div class="flex items-center justify-between py-1">
                <span class="text-slate-500">Pemerintah Desa</span>
                <span class="font-bold text-slate-900">{{ $village->name }}</span>
            </div>

            <div class="flex items-center justify-between py-1">
                <span class="text-slate-500">Tahun Pajak SPPT</span>
                <span class="font-bold text-slate-900">Tahun {{ $payment->sppt->tax_year }}</span>
            </div>

            <!-- Privacy Masked Data -->
            <div class="flex items-center justify-between py-1">
                <span class="text-slate-500">Nama Wajib Pajak</span>
                <span class="font-mono font-bold text-slate-900">{{ $maskedWpName }}</span>
            </div>

            <div class="flex items-center justify-between py-1">
                <span class="text-slate-500">Nomor Objek Pajak (NOP)</span>
                <span class="font-mono font-bold text-slate-900">{{ $maskedNop }}</span>
            </div>

            <div class="flex items-center justify-between py-1">
                <span class="text-slate-500">Petugas Penerima (Kasir)</span>
                <span class="font-semibold text-slate-800">{{ $payment->cashier?->name ?? 'Kasir Desa' }}</span>
            </div>

            <!-- Total Amount Paid Box -->
            <div class="pt-4 border-t border-slate-100">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Total Nominal Diterima</span>
                        <span class="text-xl sm:text-2xl font-black font-mono text-emerald-700">
                            Rp {{ number_format($payment->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase
                        {{ $payment->remaining_after == 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $payment->remaining_after == 0 ? 'Ketetapan Lunas' : 'Cicilan Pajak' }}
                    </span>
                </div>
            </div>

            <div class="text-[11px] text-slate-400 text-center pt-2">
                Identitas NOP dan Wajib Pajak disamarkan untuk mematuhi regulasi perlindungan data pribadi publik.
            </div>
        </div>
    </div>
</div>
@endsection
