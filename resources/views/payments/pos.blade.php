@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    selectedSpptId: '{{ $selectedSppt?->id ?? '' }}',
    selectedRemaining: {{ $selectedSppt?->remaining_bill ?? 0 }},
    payAmount: {{ $selectedSppt?->remaining_bill ?? 0 }},
    fineAmount: 0,
    totalPayment() {
        return (parseFloat(this.payAmount) || 0) + (parseFloat(this.fineAmount) || 0);
    },
    formatRupiah(num) {
        return Math.round(num || 0).toLocaleString('id-ID');
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Loket Kasir POS & Penerimaan PBB</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Cari dan pilih tagihan SPPT dari tabel, terima pembayaran tunai/cicilan, dan terbitkan kuitansi ber-QR.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('payments.history') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 font-semibold text-xs shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Riwayat Kuitansi</span>
            </a>
        </div>
    </div>

    <!-- Split Screen Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- KOLOM KIRI: Tabel SPPT & Pencarian (lg:col-span-7) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-4">
                <form method="GET" action="{{ route('payments.pos') }}" class="space-y-3">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ $search }}" 
                                   placeholder="Cari NOP / Nama Wajib Pajak..." 
                                   class="w-full pl-10 pr-3.5 py-2 rounded-xl border border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-medium">
                        </div>
                        <div class="flex gap-2">
                            <select name="dusun" class="px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                                <option value="">Semua Dusun</option>
                                @foreach($dusunList as $d)
                                    <option value="{{ $d }}" {{ $dusun == $d ? 'selected' : '' }}>{{ $d }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                                <option value="unpaid" {{ $statusFilter == 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                                <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>Semua Status</option>
                                <option value="paid" {{ $statusFilter == 'paid' ? 'selected' : '' }}>Lunas</option>
                            </select>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition">
                                Filter
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Tabel SPPT Interaktif -->
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-y border-slate-200 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-2.5 px-3">NOP & Wajib Pajak</th>
                                <th class="py-2.5 px-3">Dusun</th>
                                <th class="py-2.5 px-3 text-right">Tagihan</th>
                                <th class="py-2.5 px-3 text-right">Sisa</th>
                                <th class="py-2.5 px-3 text-center">Status</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            @forelse($spptList as $item)
                            <tr class="hover:bg-emerald-50/50 transition {{ $selectedSppt?->id == $item->id ? 'bg-emerald-50/80 border-l-4 border-l-emerald-600' : '' }}">
                                <td class="py-2.5 px-3">
                                    <span class="font-mono font-bold text-slate-800 block text-[11px]">{{ $item->taxObject->formatted_nop }}</span>
                                    <span class="text-slate-600 text-xs">{{ $item->taxObject->taxpayer->name }}</span>
                                    @if($item->taxObject->isTo())
                                    <span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 text-[10px] font-bold">TO</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-slate-500 text-[11px]">
                                    {{ $item->taxObject->dusun ?? '-' }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono text-slate-700">
                                    Rp {{ number_format($item->total_bill, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-right font-mono font-bold {{ $item->remaining_bill > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                    Rp {{ number_format($item->remaining_bill, 0, ',', '.') }}
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    @if($item->isPaid())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">LUNAS</span>
                                    @elseif($item->isPartial())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">CICIL</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">BELUM</span>
                                    @endif
                                </td>
                                <td class="py-2.5 px-3 text-center">
                                    <a href="{{ route('payments.pos', array_merge(request()->query(), ['selected_sppt_id' => $item->id])) }}" 
                                       class="px-2.5 py-1 rounded-lg {{ $selectedSppt?->id == $item->id ? 'bg-emerald-700 text-white font-bold' : 'bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700' }} text-[11px] transition">
                                        {{ $selectedSppt?->id == $item->id ? 'Dipilih' : 'Pilih' }}
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400">
                                    Tidak ada data SPPT yang sesuai kriteria filter.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($spptList->hasPages())
                <div class="pt-2">
                    {{ $spptList->links() }}
                </div>
                @endif
            </div>
        </div>

        <!-- KOLOM KANAN: Panel Pembayaran Kasir (lg:col-span-5) -->
        <div class="lg:col-span-5 space-y-4">
            @if($selectedSppt)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <!-- Header Info Objek Pajak -->
                <div class="p-5 border-b border-slate-100 bg-slate-50/70">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Tagihan Terpilih (Tahun {{ $selectedSppt->tax_year }})</span>
                    <h2 class="text-lg font-bold font-mono text-slate-900 tracking-wide mt-0.5">
                        {{ $selectedSppt->taxObject->formatted_nop }}
                    </h2>
                    <p class="text-sm font-semibold text-emerald-800 mt-0.5">
                        {{ $selectedSppt->taxObject->taxpayer->name }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        {{ $selectedSppt->taxObject->formatted_address }}
                    </p>

                    @if($selectedSppt->taxObject->isTo() || $selectedSppt->is_to_blocked)
                    <div class="mt-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2">
                        <svg class="w-4 h-4 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span><strong>Terkunci Status TO:</strong> Objek pajak ini dalam penanganan TO / sengketa kepemilikan. Pembayaran ditangguhkan sampai mutasi disahkan.</span>
                    </div>
                    @endif
                </div>

                <!-- Form Kasir Pembayaran -->
                @if(!$selectedSppt->isPaid() && !$selectedSppt->taxObject->isTo() && !$selectedSppt->is_to_blocked)
                <form method="POST" action="{{ route('payments.store') }}" class="p-5 space-y-4">
                    @csrf
                    <input type="hidden" name="sppt_id" value="{{ $selectedSppt->id }}">

                    <!-- Detail Tagihan Ringkas -->
                    <div class="grid grid-cols-2 gap-3 text-xs bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Total Ketetapan</span>
                            <span class="font-mono font-bold text-slate-800 text-sm">Rp {{ number_format($selectedSppt->total_bill, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Sisa Tagihan</span>
                            <span class="font-mono font-bold text-amber-700 text-sm">Rp {{ number_format($selectedSppt->remaining_bill, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Input Nominal Bayar -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-semibold text-slate-700">Nominal Pembayaran Tunai (Rp) <span class="text-rose-500">*</span></label>
                            <button type="button" 
                                    @click="payAmount = {{ $selectedSppt->remaining_bill }}" 
                                    class="text-[11px] font-semibold text-emerald-700 hover:underline">
                                Lunasi Penuh
                            </button>
                        </div>
                        <input type="number" 
                               name="paid_amount" 
                               x-model.number="payAmount" 
                               min="1" 
                               max="{{ $selectedSppt->remaining_bill }}" 
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-bold font-mono text-emerald-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Boleh diubah untuk pembayaran cicilan / sebagian.</p>
                    </div>

                    <!-- Input Denda -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Denda Keterlambatan (Rp)</label>
                        <input type="number" 
                               name="fine_amount" 
                               x-model.number="fineAmount" 
                               min="0" 
                               value="0"
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <!-- Input Nama Penyetor -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Penyetor / Pembawa Uang <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="payer_name" 
                               value="{{ old('payer_name', $selectedSppt->taxObject->taxpayer->name) }}" 
                               required 
                               class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-medium">
                    </div>

                    <!-- Total Diterima Pill -->
                    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                        <div>
                            <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wide block">Total Diterima Kasir</span>
                            <span class="text-xs text-emerald-600">Pokok + Denda</span>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold font-mono text-emerald-800" x-text="'Rp ' + formatRupiah(totalPayment())">
                                Rp {{ number_format($selectedSppt->remaining_bill, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>Terima Pembayaran & Cetak Kuitansi</span>
                    </button>
                </form>
                @elseif($selectedSppt->isPaid())
                <div class="p-6 text-center space-y-2">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 font-bold">✓</span>
                    <h3 class="text-sm font-bold text-slate-800">Tagihan SPPT Tahun Ini Sudah Lunas</h3>
                    <p class="text-xs text-slate-500">Tidak ada sisa kewajiban pembayaran untuk SPPT ini.</p>
                </div>
                @endif
            </div>

            <!-- Riwayat Pembayaran Sebelumnya -->
            @if($paidHistory->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Riwayat Pembayaran Terakhir</h3>
                <div class="space-y-2">
                    @foreach($paidHistory as $hist)
                    <div class="p-2.5 rounded-xl border border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-mono font-bold text-slate-800 block text-[11px]">{{ $hist->receipt_number }}</span>
                            <span class="text-slate-400 text-[10px]">{{ $hist->payment_date->format('d/m/Y H:i') }} &bull; Oleh {{ $hist->cashier->name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold text-emerald-700 block">Rp {{ number_format($hist->total_amount, 0, ',', '.') }}</span>
                            <a href="{{ route('payments.receipt', $hist) }}" target="_blank" class="text-[10px] text-emerald-600 hover:underline font-semibold">Cetak Kuitansi</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @else
            <!-- Empty state: Belum memilih SPPT -->
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Pilih Tagihan SPPT</h3>
                <p class="text-xs text-slate-500 max-w-xs mx-auto">
                    Klik tombol <strong>"Pilih"</strong> pada salah satu baris di tabel sebelah kiri atau cari berdasarkan NOP untuk memproses kuitansi kasir.
                </p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
