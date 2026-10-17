@extends('layouts.app')

@section('title', 'Berita Acara Serah Terima - ' . $handover->handover_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Action Bar (Hidden when printing) -->
    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('handovers.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-slate-800">Serah Terima Kas #{{ $handover->handover_number }}</h1>
                <div class="flex items-center gap-2 mt-0.5">
                    @if($handover->isReceived())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Diterima & Dikonfirmasi
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> Menunggu Konfirmasi Kolektor
                        </span>
                    @endif
                    <span class="text-xs text-slate-500">• {{ $handover->handover_date->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Berita Acara</span>
            </button>

            @if(!$handover->isReceived() && (auth()->user()->isKolektor() || auth()->user()->isAdmin()))
            <form action="{{ route('handovers.receive', $handover) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin telah menerima dan memeriksa uang fisik sebesar Rp {{ number_format($handover->physical_amount, 0, ',', '.') }}?')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Konfirmasi Penerimaan Fisik</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="no-print p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Berita Acara Printable Document Paper -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 sm:p-10 text-slate-800 printable-area">
        
        <!-- Header Dokumen Resmi -->
        <div class="border-b-2 border-slate-800 pb-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs uppercase tracking-widest font-semibold text-slate-500">Pemerintah Kabupaten Majalengka</h3>
                    <h2 class="text-base uppercase font-bold text-slate-800">Kecamatan {{ $village->district_name ?? 'Kecamatan' }} - Desa {{ $village->name ?? 'Sukahaji' }}</h2>
                    <p class="text-xs text-slate-500">{{ $village->address ?? 'Kantor Desa' }} | Telp: {{ $village->phone ?? '-' }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-slate-100 border border-slate-300 font-mono text-xs font-bold uppercase rounded">
                        BA-STK / PBB-P2
                    </span>
                    <p class="text-[11px] text-slate-500 font-mono mt-1">{{ $handover->handover_number }}</p>
                </div>
            </div>
            <div class="text-center mt-4">
                <h1 class="text-lg font-bold uppercase tracking-wide underline">BERITA ACARA SERAH TERIMA KAS PENERIMAAN PBB-P2</h1>
                <p class="text-xs text-slate-600">Tanggal: {{ $handover->handover_date->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <!-- Uraian Pihak -->
        <div class="text-xs leading-relaxed space-y-3 mb-6">
            <p>Pada hari ini, <strong>{{ $handover->handover_date->translatedFormat('l, d F Y') }}</strong> bertempat di Kantor Desa {{ $village->name }}, telah dilakukan serah terima uang fisik kas penerimaan Pajak Bumi dan Bangunan Perdesaan dan Perkotaan (PBB-P2) antara:</p>
            
            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-lg border border-slate-200">
                <div>
                    <span class="text-[11px] text-slate-500 uppercase tracking-wider block font-semibold">Pihak Pertama (Yang Menyerahkan):</span>
                    <p class="font-bold text-slate-900 mt-0.5">{{ $handover->cashier->name }}</p>
                    <p class="text-slate-600">Jabatan: Kasir / {{ ucfirst($handover->cashier->role) }} {{ $handover->cashier->dusun ? '('.$handover->cashier->dusun.')' : '' }}</p>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 uppercase tracking-wider block font-semibold">Pihak Kedua (Yang Menerima):</span>
                    <p class="font-bold text-slate-900 mt-0.5">{{ $handover->collector->name }}</p>
                    <p class="text-slate-600">Jabatan: Kolektor PBB Desa {{ $village->name }}</p>
                </div>
            </div>

            <p>Pihak Pertama telah menyerahkan kepada Pihak Kedua, dan Pihak Kedua menyatakan telah menerima secara fisik uang hasil pemungutan PBB-P2 dengan rincian rekonsiliasi sebagai berikut:</p>
        </div>

        <!-- Tabel Ringkasan Kas -->
        <div class="mb-6 border border-slate-200 rounded-lg overflow-hidden">
            <table class="w-full text-xs">
                <tr class="border-b border-slate-200 bg-slate-50">
                    <td class="py-2 px-3 font-semibold text-slate-700 w-1/2">Total Nominal Transaksi pada Sistem ({{ $handover->payments->count() }} Kuitansi)</td>
                    <td class="py-2 px-3 text-right font-mono font-semibold text-slate-900">Rp {{ number_format($handover->system_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="py-2 px-3 font-semibold text-slate-700">Jumlah Uang Fisik yang Diserahkan & Diterima</td>
                    <td class="py-2 px-3 text-right font-mono font-bold text-emerald-700 text-sm">Rp {{ number_format($handover->physical_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-slate-200 {{ $handover->difference_amount != 0 ? 'bg-amber-50' : '' }}">
                    <td class="py-2 px-3 font-semibold text-slate-700">Selisih Kas Fisik (Fisik - Sistem)</td>
                    <td class="py-2 px-3 text-right font-mono font-semibold {{ $handover->difference_amount == 0 ? 'text-slate-600' : ($handover->difference_amount > 0 ? 'text-blue-700' : 'text-rose-700') }}">
                        @if($handover->difference_amount == 0)
                            Rp 0 (Sesuai / Pas)
                        @else
                            {{ $handover->difference_amount > 0 ? '+' : '' }}Rp {{ number_format($handover->difference_amount, 0, ',', '.') }}
                        @endif
                    </td>
                </tr>
                @if($handover->difference_reason)
                <tr class="bg-amber-50/50">
                    <td class="py-2 px-3 font-semibold text-slate-700 align-top">Keterangan / Alasan Selisih</td>
                    <td class="py-2 px-3 text-slate-800 italic">{{ $handover->difference_reason }}</td>
                </tr>
                @endif
                @if($handover->notes)
                <tr>
                    <td class="py-2 px-3 font-semibold text-slate-700 align-top">Catatan Tambahan</td>
                    <td class="py-2 px-3 text-slate-800">{{ $handover->notes }}</td>
                </tr>
                @endif
            </table>
        </div>

        <!-- Rincian Kuitansi Pembayaran Terlampir -->
        <div class="mb-8">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Lampiran: Rincian Transaksi Kuitansi Terkait</h4>
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 text-slate-600 font-semibold uppercase">
                        <tr>
                            <th class="py-2 px-3 w-8 text-center">No</th>
                            <th class="py-2 px-3">No. Kuitansi</th>
                            <th class="py-2 px-3">Tgl Bayar</th>
                            <th class="py-2 px-3">NOP</th>
                            <th class="py-2 px-3">Wajib Pajak</th>
                            <th class="py-2 px-3 text-center">Tahun</th>
                            <th class="py-2 px-3 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($handover->payments as $idx => $p)
                        <tr>
                            <td class="py-1.5 px-3 text-center text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-1.5 px-3 font-mono font-medium text-slate-800">{{ $p->receipt_number }}</td>
                            <td class="py-1.5 px-3 text-slate-500 whitespace-nowrap">{{ $p->payment_date->format('d/m/Y H:i') }}</td>
                            <td class="py-1.5 px-3 font-mono text-slate-700">{{ $p->sppt->taxObject->formatted_nop }}</td>
                            <td class="py-1.5 px-3 text-slate-800">{{ $p->sppt->taxObject->taxpayer->name }}</td>
                            <td class="py-1.5 px-3 font-mono text-center">{{ $p->sppt->tax_year }}</td>
                            <td class="py-1.5 px-3 font-mono text-right font-medium text-slate-900">Rp {{ number_format($p->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                        <tr>
                            <td colspan="6" class="py-2 px-3 text-right uppercase">Total Sistem:</td>
                            <td class="py-2 px-3 text-right font-mono text-slate-900">Rp {{ number_format($handover->system_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Tanda Tangan Para Pihak -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-10">
            <div>
                <p class="text-slate-500">Pihak Pertama (Yang Menyerahkan),</p>
                <div class="h-20 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900">{{ $handover->cashier->name }}</p>
                </div>
                <p class="text-slate-500 text-[11px]">{{ ucfirst($handover->cashier->role) }}</p>
            </div>

            <div>
                <p class="text-slate-500">Pihak Kedua (Yang Menerima),</p>
                <div class="h-20 flex items-end justify-center">
                    @if($handover->isReceived())
                        <div class="text-center">
                            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-300 font-mono block mb-1">
                                TERIMA FISIK {{ $handover->received_at ? $handover->received_at->format('d/m/Y H:i') : '' }}
                            </span>
                            <p class="font-bold underline text-slate-900">{{ $handover->collector->name }}</p>
                        </div>
                    @else
                        <div class="text-center">
                            <span class="text-[10px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-300 font-mono block mb-1">
                                BELUM DIKONFIRMASI
                            </span>
                            <p class="font-bold underline text-slate-900">{{ $handover->collector->name }}</p>
                        </div>
                    @endif
                </div>
                <p class="text-slate-500 text-[11px]">Kolektor PBB Desa</p>
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
