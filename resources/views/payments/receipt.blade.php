<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kuitansi {{ $payment->receipt_number }} - {{ $village->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            @page {
                size: A4 portrait;
                margin: 6mm;
            }
        }
        .cut-line {
            border-top: 1.5px dashed #64748b;
            position: relative;
            margin: 18px 0;
            text-align: center;
        }
        .cut-line span {
            background: white;
            padding: 0 10px;
            font-size: 10px;
            color: #64748b;
            position: relative;
            top: -9px;
            font-family: monospace;
        }
        .thermal-slip {
            width: 78mm;
            margin: 0 auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            line-height: 1.3;
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 min-h-screen p-4 sm:p-6 antialiased">
    <!-- Screen Control Bar (Hidden on print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('payments.pos') }}" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Loket Kasir</span>
            </a>
            <div class="h-4 w-px bg-slate-200"></div>
            <div class="inline-flex rounded-xl p-1 bg-slate-100 text-xs font-semibold">
                <a href="{{ route('payments.receipt', ['payment' => $payment, 'format' => 'a4']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $format === 'a4' ? 'bg-white text-emerald-800 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                    Format A4 (2-Rangkap A5)
                </a>
                <a href="{{ route('payments.receipt', ['payment' => $payment, 'format' => 'thermal']) }}" 
                   class="px-3 py-1.5 rounded-lg transition {{ $format === 'thermal' ? 'bg-white text-emerald-800 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900' }}">
                    Format Thermal (58/80mm)
                </a>
            </div>
        </div>

        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Kuitansi Sekarang</span>
        </button>
    </div>

    @if($format === 'a4')
    <!-- ========================================== -->
    <!-- FORMAT A4: 2 RANGKAP (ATAS WP, BAWAH ARSIP) -->
    <!-- ========================================== -->
    <div class="max-w-4xl mx-auto bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm print:border-none print:shadow-none print:p-0">
        <!-- RANGKAP 1: LEMBAR WAJIB PAJAK -->
        <div class="border border-slate-800 p-4 rounded-xl relative">
            <div class="flex items-center justify-between border-b-2 border-slate-800 pb-2.5 mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-sm">
                        SP
                    </div>
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 leading-tight">Pemerintah {{ $village->regency }} &bull; {{ $village->district }}</h2>
                        <h1 class="text-sm font-black uppercase text-slate-900 leading-tight">PEMERINTAH {{ $village->name }}</h1>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-slate-900 text-white">
                        LEMBAR WAJIB PAJAK
                    </span>
                    <p class="font-mono text-[11px] font-bold text-slate-800 mt-1">{{ $payment->receipt_number }}</p>
                </div>
            </div>

            <!-- Receipt Content Table -->
            <div class="grid grid-cols-12 gap-3 text-xs">
                <div class="col-span-9 space-y-1.5">
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Nomor Objek Pajak (NOP)</span>
                        <span class="col-span-8 font-mono font-bold text-slate-900">: {{ $payment->sppt->taxObject->formatted_nop }}</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Nama Wajib Pajak</span>
                        <span class="col-span-8 font-bold text-slate-900">: {{ $payment->sppt->taxObject->taxpayer?->name }}</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Letak Objek Pajak</span>
                        <span class="col-span-8 text-slate-800">: {{ $payment->sppt->taxObject->address ?? '-' }} (RT {{ $payment->sppt->taxObject->rt ?? '-' }}/RW {{ $payment->sppt->taxObject->rw ?? '-' }})</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Wilayah / Dusun</span>
                        <span class="col-span-8 text-slate-800">: {{ $payment->sppt->taxObject->dusun ?? '-' }}, {{ $village->name }}</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Tahun Pajak SPPT</span>
                        <span class="col-span-8 font-bold text-slate-900">: {{ $payment->sppt->tax_year }}</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Pokok Tagihan PBB</span>
                        <span class="col-span-8 font-mono font-semibold text-slate-900">: Rp {{ number_format($payment->amount_principal, 0, ',', '.') }}</span>
                    </div>
                    @if($payment->amount_fine > 0)
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Denda Keterlambatan</span>
                        <span class="col-span-8 font-mono text-slate-900">: Rp {{ number_format($payment->amount_fine, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="grid grid-cols-12 py-0.5 bg-slate-50 font-bold border-y border-slate-200">
                        <span class="col-span-4 text-slate-900 uppercase">JUMLAH DIBAYAR</span>
                        <span class="col-span-8 font-mono text-emerald-800">: Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Sisa Tagihan Setelah Ini</span>
                        <span class="col-span-8 font-mono font-semibold text-slate-800">: Rp {{ number_format($payment->remaining_after, 0, ',', '.') }} ({{ $payment->remaining_after == 0 ? 'LUNAS' : 'STATUS CICILAN' }})</span>
                    </div>
                    @if($payment->notes)
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Keterangan / Penyetor</span>
                        <span class="col-span-8 text-slate-800 font-medium">: {{ $payment->notes }}</span>
                    </div>
                    @endif
                </div>

                <!-- QR Code & Signatures -->
                <div class="col-span-3 flex flex-col items-center justify-between text-center border-l border-slate-200 pl-3">
                    <div class="space-y-1">
                        <div class="w-24 h-24 mx-auto p-1 bg-white border border-slate-300 rounded-lg flex items-center justify-center">
                            {!! $qrCodeSvg !!}
                        </div>
                        <p class="text-[9px] text-slate-500 font-mono">Scan verifikasi keaslian</p>
                    </div>

                    <div class="text-[10px] text-slate-700 w-full pt-2">
                        <p>{{ $payment->payment_date->format('d/m/Y H:i') }}</p>
                        <p class="font-bold text-slate-900">Kasir: {{ $payment->cashier?->name }}</p>
                        <div class="h-6"></div>
                        <p class="text-[9px] text-slate-400 italic">( Tanda Tangan & Cap Sah )</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CUT LINE SEPARATOR -->
        <div class="cut-line">
            <span>&cross; GUNTING / POTONG DI SINI UNTUK ARSIP &cross;</span>
        </div>

        <!-- RANGKAP 2: LEMBAR ARSIP DESA -->
        <div class="border border-slate-800 p-4 rounded-xl relative">
            <div class="flex items-center justify-between border-b-2 border-slate-800 pb-2.5 mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-sm">
                        SP
                    </div>
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600 leading-tight">Pemerintah {{ $village->regency }} &bull; {{ $village->district }}</h2>
                        <h1 class="text-sm font-black uppercase text-slate-900 leading-tight">PEMERINTAH {{ $village->name }}</h1>
                    </div>
                </div>
                <div class="text-right">
                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-800 text-white">
                        LEMBAR ARSIP PEMERINTAH DESA
                    </span>
                    <p class="font-mono text-[11px] font-bold text-slate-800 mt-1">{{ $payment->receipt_number }}</p>
                </div>
            </div>

            <!-- Receipt Content Table -->
            <div class="grid grid-cols-12 gap-3 text-xs">
                <div class="col-span-9 space-y-1.5">
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Nomor Objek Pajak (NOP)</span>
                        <span class="col-span-8 font-mono font-bold text-slate-900">: {{ $payment->sppt->taxObject->formatted_nop }}</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Nama Wajib Pajak</span>
                        <span class="col-span-8 font-bold text-slate-900">: {{ $payment->sppt->taxObject->taxpayer?->name }}</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Letak Objek Pajak</span>
                        <span class="col-span-8 text-slate-800">: {{ $payment->sppt->taxObject->address ?? '-' }} (RT {{ $payment->sppt->taxObject->rt ?? '-' }}/RW {{ $payment->sppt->taxObject->rw ?? '-' }})</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Wilayah / Dusun</span>
                        <span class="col-span-8 text-slate-800">: {{ $payment->sppt->taxObject->dusun ?? '-' }}, {{ $village->name }}</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Tahun Pajak SPPT</span>
                        <span class="col-span-8 font-bold text-slate-900">: {{ $payment->sppt->tax_year }}</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5 bg-slate-50 font-bold border-y border-slate-200">
                        <span class="col-span-4 text-slate-900 uppercase">JUMLAH DIBAYAR</span>
                        <span class="col-span-8 font-mono text-emerald-800">: Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Sisa Tagihan SPPT</span>
                        <span class="col-span-8 font-mono font-semibold text-slate-800">: Rp {{ number_format($payment->remaining_after, 0, ',', '.') }} ({{ $payment->remaining_after == 0 ? 'LUNAS' : 'CICILAN' }})</span>
                    </div>
                    @if($payment->notes)
                    <div class="grid grid-cols-12 py-0.5">
                        <span class="col-span-4 text-slate-600 font-medium">Catatan / Penyetor</span>
                        <span class="col-span-8 text-slate-800 font-medium">: {{ $payment->notes }}</span>
                    </div>
                    @endif
                </div>

                <!-- QR Code & Signatures -->
                <div class="col-span-3 flex flex-col items-center justify-between text-center border-l border-slate-200 pl-3">
                    <div class="space-y-1">
                        <div class="w-24 h-24 mx-auto p-1 bg-white border border-slate-300 rounded-lg flex items-center justify-center">
                            {!! $qrCodeSvg !!}
                        </div>
                        <p class="text-[9px] text-slate-500 font-mono">Scan verifikasi</p>
                    </div>

                    <div class="text-[10px] text-slate-700 w-full pt-2">
                        <p>{{ $payment->payment_date->format('d/m/Y H:i') }}</p>
                        <p class="font-bold text-slate-900">Penyetor / WP</p>
                        <div class="h-6"></div>
                        <p class="text-[9px] text-slate-400 italic">( .............................. )</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- ========================================== -->
    <!-- FORMAT THERMAL: 58mm / 80mm PORTABLE PRINT -->
    <!-- ========================================== -->
    <div class="thermal-slip bg-white p-4 border border-slate-300 shadow-sm print:border-none print:shadow-none">
        <div class="text-center pb-2 border-b border-dashed border-slate-400">
            <h2 class="font-bold text-xs uppercase">{{ $village->name }}</h2>
            <p class="text-[10px]">PBB-P2 KEC. {{ strtoupper($village->district) }}</p>
            <p class="text-[10px] text-slate-500">{{ $village->phone ?? '' }}</p>
        </div>

        <div class="py-2 border-b border-dashed border-slate-400 space-y-1 text-[11px]">
            <div class="flex justify-between">
                <span>NO:</span>
                <span class="font-bold">{{ $payment->receipt_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>TGL:</span>
                <span>{{ $payment->payment_date->format('d/m/y H:i') }}</span>
            </div>
            <div class="flex justify-between">
                <span>KASIR:</span>
                <span>{{ $payment->cashier?->name }}</span>
            </div>
        </div>

        <div class="py-2 border-b border-dashed border-slate-400 space-y-1 text-[11px]">
            <div>NOP: <span class="font-bold">{{ $payment->sppt->taxObject->formatted_nop }}</span></div>
            <div>WP : <span class="font-bold">{{ $payment->sppt->taxObject->taxpayer?->name }}</span></div>
            <div>DSN: {{ $payment->sppt->taxObject->dusun ?? '-' }}</div>
            <div>THN: <span class="font-bold">{{ $payment->sppt->tax_year }}</span></div>
            @if($payment->notes)
            <div>KET: <span class="font-bold">{{ $payment->notes }}</span></div>
            @endif
        </div>

        <div class="py-2 border-b border-dashed border-slate-400 space-y-1 text-[11px]">
            <div class="flex justify-between">
                <span>POKOK:</span>
                <span>Rp {{ number_format($payment->amount_principal, 0, ',', '.') }}</span>
            </div>
            @if($payment->amount_fine > 0)
            <div class="flex justify-between">
                <span>DENDA:</span>
                <span>Rp {{ number_format($payment->amount_fine, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="flex justify-between font-bold text-xs pt-1 border-t border-dotted border-slate-400">
                <span>TOTAL:</span>
                <span>Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-[10px] text-slate-600">
                <span>SISA TAGIHAN:</span>
                <span>Rp {{ number_format($payment->remaining_after, 0, ',', '.') }}</span>
            </div>
            <div class="text-center font-bold text-[11px] pt-1">
                *** {{ $payment->remaining_after == 0 ? 'LUNAS' : 'STATUS CICILAN' }} ***
            </div>
        </div>

        <div class="pt-3 text-center space-y-1">
            <div class="w-24 h-24 mx-auto p-1 bg-white flex items-center justify-center">
                {!! $qrCodeSvg !!}
            </div>
            <p class="text-[9px]">Scan QR di atas untuk verifikasi keaslian kuitansi resmi desa.</p>
            <p class="text-[9px] pt-1">Terima kasih atas partisipasi Anda membangun desa.</p>
        </div>
    </div>
    @endif
</body>
</html>
