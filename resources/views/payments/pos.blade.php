@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    selectedSpptId: '{{ $unpaidSppts->first()?->id ?? '' }}',
    selectedRemaining: {{ $unpaidSppts->first()?->remaining_bill ?? 0 }},
    payAmount: {{ $unpaidSppts->first()?->remaining_bill ?? 0 }},
    fineAmount: 0,
    setSppt(id, remaining) {
        this.selectedSpptId = id;
        this.selectedRemaining = remaining;
        this.payAmount = remaining;
    },
    totalPayment() {
        return (parseFloat(this.payAmount) || 0) + (parseFloat(this.fineAmount) || 0);
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Loket Kasir POS & Penerimaan PBB</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pencarian NOP, penerimaan pembayaran tunai, cicilan, dan pencetakan kuitansi ber-QR Code.</p>
        </div>
        <a href="{{ route('payments.history') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs shadow-xs transition self-start sm:self-auto">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Riwayat Kuitansi</span>
        </a>
    </div>

    <!-- Search NOP Box -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('payments.pos') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       required 
                       autofocus 
                       placeholder="Scan Barcode atau masukkan Nomor Objek Pajak (NOP) / Nama Wajib Pajak..." 
                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-mono font-medium">
            </div>
            <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2 shrink-0">
                <span>Cari Objek Pajak</span>
            </button>
            @if($search)
            <a href="{{ route('payments.pos') }}" class="text-xs text-slate-500 hover:text-slate-800 underline shrink-0">Reset</a>
            @endif
        </form>
    </div>

    @if($search && ! $taxObject)
    <div class="rounded-2xl bg-amber-50 border border-amber-200 p-8 text-center space-y-3">
        <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto font-bold text-lg">?</div>
        <h3 class="text-base font-bold text-slate-900">Objek Pajak Tidak Ditemukan</h3>
        <p class="text-xs text-slate-600 max-w-md mx-auto">
            Tidak ditemukan data Objek Pajak atau Wajib Pajak dengan kata kunci <strong class="font-mono text-slate-900">"{{ $search }}"</strong>. Pastikan NOP sudah diimpor melalui menu Data SPPT & DHKP.
        </p>
    </div>
    @endif

    @if($taxObject)
    <!-- Tax Object & Owner Info Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/60">
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Nomor Objek Pajak (NOP)</span>
                <h2 class="text-xl sm:text-2xl font-bold font-mono text-slate-900 tracking-wide mt-0.5">
                    {{ $taxObject->formatted_nop }}
                </h2>
            </div>
            <div>
                @if($taxObject->isTo())
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-rose-600 animate-ping"></span>
                    STATUS: TANPA OBJEK (TO) - PEMILIK BELUM JELAS
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    STATUS: OBJEK AKTIF NORMAL
                </span>
                @endif
            </div>
        </div>

        <!-- Strict Warning if status is TO -->
        @if($taxObject->isTo())
        <div class="p-4 bg-rose-50 border-b border-rose-200 flex items-start gap-3">
            <svg class="w-6 h-6 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <h4 class="text-sm font-bold text-rose-900">Pembayaran Dinonaktifkan (Status TO)</h4>
                <p class="text-xs text-rose-700 mt-0.5 leading-relaxed">
                    Sesuai ketentuan, SPPT berstatus <strong>Tanpa Objek (TO)</strong> diblokir dari transaksi loket sampai proses mutasi dan identitas pemilik baru diverifikasi oleh Administrator/Kolektor Desa.
                </p>
            </div>
        </div>
        @endif

        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 font-semibold uppercase block">Wajib Pajak (SPPT)</span>
                <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $taxObject->taxpayer?->name ?? '-' }}</span>
                <span class="text-slate-500 font-mono">{{ $taxObject->taxpayer?->nik ?? 'NIK: -' }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase block">Letak Objek Pajak</span>
                <span class="font-medium text-slate-800 mt-0.5 block">{{ $taxObject->address ?? '-' }}</span>
                <span class="text-slate-500">RT {{ $taxObject->rt ?? '-' }} / RW {{ $taxObject->rw ?? '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase block">Wilayah Dusun</span>
                <span class="font-semibold text-slate-800 mt-0.5 block">{{ $taxObject->dusun ?? '-' }}</span>
                <span class="text-slate-500">{{ $village->name }}</span>
            </div>
            <div>
                <span class="text-slate-400 font-semibold uppercase block">Kontak Penanggung Jawab</span>
                <span class="font-medium text-slate-800 mt-0.5 block">{{ $taxObject->taxpayer?->phone ?? '-' }}</span>
                <span class="text-slate-500">{{ $taxObject->taxpayer?->address ?? '' }}</span>
            </div>
        </div>
    </div>

    <!-- Payment Execution Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Tagihan Unpaid SPPT List (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center justify-between">
                    <span>Pilih Tahun Pajak yang Akan Dibayar</span>
                    <span class="text-xs font-normal text-slate-500">{{ $unpaidSppts->count() }} Tagihan Belum Lunas</span>
                </h3>

                <div class="space-y-3">
                    @forelse($unpaidSppts as $sppt)
                    <div class="p-4 rounded-xl border transition cursor-pointer flex items-center justify-between"
                         :class="selectedSpptId == {{ $sppt->id }} ? 'border-emerald-500 bg-emerald-50/40 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:border-slate-300'"
                         @click="setSppt({{ $sppt->id }}, {{ $sppt->remaining_bill }})">
                        <div class="flex items-center gap-3.5">
                            <input type="radio" 
                                   name="sppt_select" 
                                   :value="{{ $sppt->id }}" 
                                   :checked="selectedSpptId == {{ $sppt->id }}"
                                   class="text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 text-sm">Tahun Pajak {{ $sppt->tax_year }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        {{ $sppt->status === 'partial' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $sppt->status_label }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Pokok: Rp {{ number_format($sppt->total_bill, 0, ',', '.') }} 
                                    @if($sppt->paid_amount > 0)
                                    &bull; Terbayar: Rp {{ number_format($sppt->paid_amount, 0, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] font-semibold text-slate-400 uppercase block">Sisa Kewajiban</span>
                            <span class="font-bold text-sm font-mono text-emerald-700">
                                Rp {{ number_format($sppt->remaining_bill, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400">
                        <svg class="w-10 h-10 text-emerald-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="font-semibold text-slate-700 text-sm">Seluruh Tagihan Lunas</p>
                        <p class="text-xs text-slate-400 mt-0.5">Tidak ada tunggakan SPPT untuk NOP ini.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Riwayat Pembayaran Sebelumnya -->
            @if($paidHistory->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Histori Pembayaran NOP Ini</h3>
                <div class="divide-y divide-slate-100 text-xs">
                    @foreach($paidHistory as $hist)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <span class="font-mono font-semibold text-slate-900">{{ $hist->receipt_number }}</span>
                            <span class="text-slate-400 ml-2">{{ $hist->payment_date->format('d/m/Y H:i') }}</span>
                            <div class="text-[11px] text-slate-500">Kasir: {{ $hist->cashier?->name }} (Th. {{ $hist->sppt?->tax_year }})</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono font-bold text-emerald-700">Rp {{ number_format($hist->total_amount, 0, ',', '.') }}</span>
                            <a href="{{ route('payments.receipt', $hist) }}" target="_blank" class="px-2 py-1 rounded bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-[11px] font-semibold text-slate-600 transition">
                                Cetak
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Right: Payment Input Form (5 cols) -->
        <div class="lg:col-span-5">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sticky top-20">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Form Penerimaan Tunai</span>
                </h3>

                @if($taxObject->isTo())
                <div class="p-6 bg-slate-50 rounded-xl border border-dashed border-slate-300 text-center text-slate-500 text-xs">
                    Tombol transaksi dinonaktifkan karena objek pajak berstatus <strong>Tanpa Objek (TO)</strong>.
                </div>
                @elseif($unpaidSppts->isEmpty())
                <div class="p-6 bg-emerald-50 rounded-xl border border-emerald-200 text-center text-emerald-800 text-xs">
                    Tidak ada tagihan yang perlu dibayar. Seluruh SPPT telah berstatus lunas.
                </div>
                @else
                <form method="POST" action="{{ route('payments.store') }}" class="space-y-4" onsubmit="return confirm('Konfirmasi: Pastikan uang fisik sebesar Rp ' + document.getElementById('display_total').innerText + ' telah diterima kasir secara tunai.')">
                    @csrf
                    <input type="hidden" name="sppt_id" :value="selectedSpptId">

                    <!-- Quick buttons -->
                    <div class="flex gap-2">
                        <button type="button" @click="payAmount = selectedRemaining" class="flex-1 py-1.5 px-3 rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs font-semibold hover:bg-emerald-100 transition">
                            Bayar Lunas (100%)
                        </button>
                        <button type="button" @click="payAmount = Math.round(selectedRemaining / 2)" class="py-1.5 px-3 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition">
                            Cicil 50%
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nominal Pokok Pajak Dibayar (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" 
                               name="amount_principal" 
                               x-model.number="payAmount" 
                               required 
                               min="1" 
                               :max="selectedRemaining"
                               class="w-full px-3.5 py-3 rounded-xl border border-slate-300 text-base font-bold font-mono text-slate-900 focus:ring-2 focus:ring-emerald-500 outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Maksimal: Rp <span x-text="new Intl.NumberFormat('id-ID').format(selectedRemaining)"></span></p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Denda Keterlambatan (Rp - Opsional)</label>
                        <input type="number" 
                               name="amount_fine" 
                               x-model.number="fineAmount" 
                               min="0"
                               placeholder="0"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-mono text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Pembayaran (Opsional)</label>
                        <input type="text" name="notes" placeholder="Misal: Titipan ahli waris" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <!-- Total Confirmation Box -->
                    <div class="p-4 rounded-xl bg-slate-900 text-white space-y-1">
                        <div class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold">Total Uang Tunai Diterima</div>
                        <div class="text-2xl font-extrabold font-mono text-emerald-400">
                            Rp <span id="display_total" x-text="new Intl.NumberFormat('id-ID').format(totalPayment())"></span>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/30 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Terima Uang & Terbitkan Kuitansi</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
