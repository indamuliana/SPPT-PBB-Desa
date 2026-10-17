@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    selectedSpptId: '{{ $selectedSppt?->id ?? '' }}',
    selectedRemaining: {{ $selectedSppt?->remaining_bill ?? 0 }},
    payAmount: {{ $selectedSppt?->remaining_bill ?? 0 }},
    fineAmount: 0,
    quickEditModalOpen: false,
    taxRate: {{ $village->tax_rate_percent ?? 0.1 }},
    editData: {
        actionUrl: '',
        taxpayerName: '',
        taxpayerNik: '',
        taxpayerPhone: '',
        taxpayerAddress: '',
        nop: '',
        objectAddress: '',
        objectRt: '',
        objectRw: '',
        dusunId: '',
        bumiArea: 0,
        bumiNjop: 0,
        bangunanArea: 0,
        bangunanNjop: 0,
        bumiBersamaArea: 0,
        bumiBersamaNjop: 0,
        bangunanBersamaArea: 0,
        bangunanBersamaNjop: 0,
        pbbTerutang: 0,
        paidAmount: 0,
        dueDate: '',
        dhkpRowRef: ''
    },
    totalPayment() {
        return (parseFloat(this.payAmount) || 0) + (parseFloat(this.fineAmount) || 0);
    },
    formatRupiah(num) {
        return Math.round(num || 0).toLocaleString('id-ID');
    },
    openQuickEdit(data) {
        this.editData = JSON.parse(JSON.stringify(data));
        this.quickEditModalOpen = true;
    },
    calculateTotalNjop() {
        let t = (parseFloat(this.editData.bumiArea) || 0) * (parseFloat(this.editData.bumiNjop) || 0)
              + (parseFloat(this.editData.bangunanArea) || 0) * (parseFloat(this.editData.bangunanNjop) || 0)
              + (parseFloat(this.editData.bumiBersamaArea) || 0) * (parseFloat(this.editData.bumiBersamaNjop) || 0)
              + (parseFloat(this.editData.bangunanBersamaArea) || 0) * (parseFloat(this.editData.bangunanBersamaNjop) || 0);
        return Math.round(t);
    },
    autoCalculatePbb() {
        let totalNjop = this.calculateTotalNjop();
        let njopPerhitungan = Math.max(0, totalNjop - 10000000);
        let calcPbb = Math.round(njopPerhitungan * (parseFloat(this.taxRate) / 100));
        this.editData.pbbTerutang = calcPbb;
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
                            <select name="dusun" class="px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                                @if($isKadus && !empty($assignedDusunIds))
                                    <option value="my" {{ $dusun === 'my' ? 'selected' : '' }}>📍 Wilayah Saya</option>
                                @endif
                                <option value="all" {{ $dusun === 'all' || ($dusun === '' && !$isKadus) ? 'selected' : '' }}>Semua Dusun</option>
                                @foreach($dusunList as $d)
                                    <option value="{{ $d->id }}" {{ (string)$dusun === (string)$d->id || $dusun === $d->name ? 'selected' : '' }}>{{ $d->name }}</option>
                                @endforeach
                            </select>
                            <select name="status" class="px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
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
                                <td class="py-2.5 px-3 text-[11px]">
                                    @php
                                        $dusunName = $item->taxObject->dusunRelation?->name ?? $item->taxObject->dusun ?? '-';
                                        $isMyDusun = $isKadus && $item->taxObject->dusun_id && in_array($item->taxObject->dusun_id, $assignedDusunIds);
                                    @endphp
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-600 font-medium">{{ $dusunName }}</span>
                                        @if($isMyDusun)
                                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[9px] font-bold" title="Wilayah Kelolaan Anda">
                                                Saya
                                            </span>
                                        @endif
                                    </div>
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
                                    @php
                                        $itemBumi = $item->taxObject->components->firstWhere('type', 'bumi');
                                        $itemBangunan = $item->taxObject->components->firstWhere('type', 'bangunan');
                                        $itemBumiBersama = $item->taxObject->components->firstWhere('type', 'bumi_bersama');
                                        $itemBangunanBersama = $item->taxObject->components->firstWhere('type', 'bangunan_bersama');

                                        $itemPayload = [
                                            'actionUrl' => route('payments.pos.quick-edit', $item),
                                            'taxpayerName' => $item->taxObject->taxpayer?->name ?? '',
                                            'taxpayerNik' => $item->taxObject->taxpayer?->nik ?? '',
                                            'taxpayerPhone' => $item->taxObject->taxpayer?->phone ?? '',
                                            'taxpayerAddress' => $item->taxObject->taxpayer?->address ?? '',
                                            'nop' => $item->taxObject->nop,
                                            'objectAddress' => $item->taxObject->address ?? '',
                                            'objectRt' => $item->taxObject->rt ?? '',
                                            'objectRw' => $item->taxObject->rw ?? '',
                                            'dusunId' => $item->taxObject->dusun_id ?? '',
                                            'bumiArea' => (float)($itemBumi?->area_m2 ?? 0),
                                            'bumiNjop' => (float)($itemBumi?->njop_per_m2 ?? 0),
                                            'bangunanArea' => (float)($itemBangunan?->area_m2 ?? 0),
                                            'bangunanNjop' => (float)($itemBangunan?->njop_per_m2 ?? 0),
                                            'bumiBersamaArea' => (float)($itemBumiBersama?->area_m2 ?? 0),
                                            'bumiBersamaNjop' => (float)($itemBumiBersama?->njop_per_m2 ?? 0),
                                            'bangunanBersamaArea' => (float)($itemBangunanBersama?->area_m2 ?? 0),
                                            'bangunanBersamaNjop' => (float)($itemBangunanBersama?->njop_per_m2 ?? 0),
                                            'pbbTerutang' => (float)$item->pbb_terutang,
                                            'paidAmount' => (float)$item->paid_amount,
                                            'dueDate' => $item->due_date?->format('Y-m-d') ?? '',
                                            'dhkpRowRef' => $item->dhkp_row_ref ?? '',
                                        ];
                                    @endphp
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('payments.pos', array_merge(request()->query(), ['selected_sppt_id' => $item->id])) }}" 
                                           class="px-2.5 py-1 rounded-lg {{ $selectedSppt?->id == $item->id ? 'bg-emerald-700 text-white font-bold' : 'bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700' }} text-[11px] transition">
                                            {{ $selectedSppt?->id == $item->id ? 'Dipilih' : 'Pilih' }}
                                        </a>
                                        <button type="button" 
                                                @click.stop="openQuickEdit({{ json_encode($itemPayload) }})"
                                                title="Koreksi NOP, WP, Objek & Nominal"
                                                class="p-1 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                    </div>
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
            @php
                $selBumi = $selectedSppt->taxObject->components->firstWhere('type', 'bumi');
                $selBangunan = $selectedSppt->taxObject->components->firstWhere('type', 'bangunan');
                $selBumiBersama = $selectedSppt->taxObject->components->firstWhere('type', 'bumi_bersama');
                $selBangunanBersama = $selectedSppt->taxObject->components->firstWhere('type', 'bangunan_bersama');

                $selectedEditData = [
                    'actionUrl' => route('payments.pos.quick-edit', $selectedSppt),
                    'taxpayerName' => $selectedSppt->taxObject->taxpayer?->name ?? '',
                    'taxpayerNik' => $selectedSppt->taxObject->taxpayer?->nik ?? '',
                    'taxpayerPhone' => $selectedSppt->taxObject->taxpayer?->phone ?? '',
                    'taxpayerAddress' => $selectedSppt->taxObject->taxpayer?->address ?? '',
                    'nop' => $selectedSppt->taxObject->nop,
                    'objectAddress' => $selectedSppt->taxObject->address ?? '',
                    'objectRt' => $selectedSppt->taxObject->rt ?? '',
                    'objectRw' => $selectedSppt->taxObject->rw ?? '',
                    'dusunId' => $selectedSppt->taxObject->dusun_id ?? '',
                    'bumiArea' => (float)($selBumi?->area_m2 ?? 0),
                    'bumiNjop' => (float)($selBumi?->njop_per_m2 ?? 0),
                    'bangunanArea' => (float)($selBangunan?->area_m2 ?? 0),
                    'bangunanNjop' => (float)($selBangunan?->njop_per_m2 ?? 0),
                    'bumiBersamaArea' => (float)($selBumiBersama?->area_m2 ?? 0),
                    'bumiBersamaNjop' => (float)($selBumiBersama?->njop_per_m2 ?? 0),
                    'bangunanBersamaArea' => (float)($selBangunanBersama?->area_m2 ?? 0),
                    'bangunanBersamaNjop' => (float)($selBangunanBersama?->njop_per_m2 ?? 0),
                    'pbbTerutang' => (float)$selectedSppt->pbb_terutang,
                    'paidAmount' => (float)$selectedSppt->paid_amount,
                    'dueDate' => $selectedSppt->due_date?->format('Y-m-d') ?? '',
                    'dhkpRowRef' => $selectedSppt->dhkp_row_ref ?? '',
                ];
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <!-- Header Info Objek Pajak -->
                <div class="p-5 border-b border-slate-100 bg-slate-50/70">
                    <div class="flex items-start justify-between gap-3">
                        <div>
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
                        </div>
                        <button type="button" 
                                @click="openQuickEdit({{ json_encode($selectedEditData) }})"
                                class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition shadow-xs">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Koreksi Data</span>
                        </button>
                    </div>

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
    <!-- Modal Koreksi Cepat Data SPPT, WP & Objek Pajak -->
    <div x-show="quickEditModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
        <div @click.away="quickEditModalOpen = false" 
             class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Koreksi Cepat SPPT, Wajib Pajak & Objek Pajak</h3>
                        <p class="text-xs text-slate-500">Edit NOP, Wajib Pajak, Komponen Bumi/Bangunan, dan Nominal Pajak langsung dari loket kasir.</p>
                    </div>
                </div>
                <button type="button" @click="quickEditModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form :action="editData.actionUrl" method="POST" class="flex flex-col flex-1 overflow-hidden">
                @csrf
                @method('PUT')

                <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs">
                    
                    <!-- Alert Peringatan Pembayaran -->
                    <template x-if="editData.paidAmount > 0">
                        <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>
                                <p class="font-bold">Perhatian Finansial Kasir:</p>
                                <p class="mt-0.5">SPPT ini telah memiliki transaksi pembayaran sebesar <span class="font-mono font-bold" x-text="'Rp ' + formatRupiah(editData.paidAmount)"></span>. Nominal tagihan baru tidak boleh diubah lebih kecil dari nominal yang telah disetorkan.</p>
                            </div>
                        </div>
                    </template>

                    <!-- 1. Identitas Wajib Pajak -->
                    <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200 space-y-3">
                        <div class="flex items-center gap-2 text-slate-800 font-bold text-xs uppercase tracking-wider">
                            <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">1</span>
                            Identitas Wajib Pajak (WP)
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-slate-700 mb-1">Nama Wajib Pajak <span class="text-rose-500">*</span></label>
                                <input type="text" name="taxpayer_name" x-model="editData.taxpayerName" required class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none font-semibold">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">NIK (Nomor Induk Kependudukan)</label>
                                <input type="text" name="taxpayer_nik" x-model="editData.taxpayerNik" maxlength="20" placeholder="16 digit NIK" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Nomor Telepon / WhatsApp</label>
                                <input type="text" name="taxpayer_phone" x-model="editData.taxpayerPhone" placeholder="08..." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-slate-700 mb-1">Alamat Wajib Pajak</label>
                                <input type="text" name="taxpayer_address" x-model="editData.taxpayerAddress" placeholder="Alamat domisili WP..." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                            </div>
                        </div>
                    </div>

                    <!-- 2. Letak Objek Pajak (NOP) -->
                    <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200 space-y-3">
                        <div class="flex items-center gap-2 text-slate-800 font-bold text-xs uppercase tracking-wider">
                            <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">2</span>
                            Letak Objek Pajak (NOP)
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-slate-700 mb-1">Nomor Objek Pajak (NOP) <span class="text-rose-500">*</span></label>
                                <input type="text" name="nop" x-model="editData.nop" required placeholder="Contoh: 32.05.010.001.002-0034.0" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-mono font-bold text-emerald-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Dusun / Wilayah</label>
                                <select name="dusun_id" x-model="editData.dusunId" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs bg-white">
                                    <option value="">-- Tanpa Dusun --</option>
                                    @foreach($allDusuns as $ds)
                                        <option value="{{ $ds->id }}">{{ $ds->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-slate-700 mb-1">Alamat Letak Objek</label>
                                <input type="text" name="object_address" x-model="editData.objectAddress" placeholder="Lokasi bidang tanah/bangunan..." class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">RT / RW</label>
                                <div class="flex items-center gap-1.5">
                                    <input type="text" name="object_rt" x-model="editData.objectRt" placeholder="RT" class="w-full px-2.5 py-2 rounded-lg border border-slate-300 text-xs text-center font-mono">
                                    <span>/</span>
                                    <input type="text" name="object_rw" x-model="editData.objectRw" placeholder="RW" class="w-full px-2.5 py-2 rounded-lg border border-slate-300 text-xs text-center font-mono">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Rincian 4 Komponen Objek (Bumi & Bangunan) -->
                    <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-slate-800 font-bold text-xs uppercase tracking-wider">
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">3</span>
                                Komponen Objek Pajak (Bumi, Bangunan, dll)
                            </div>
                            <span class="text-[11px] text-slate-500">Opsional: isi untuk perbarui luas & NJOP</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-200/70 text-slate-700 font-bold uppercase tracking-wider text-[10px]">
                                        <th class="py-2 px-2.5">Komponen</th>
                                        <th class="py-2 px-2.5 w-28">Luas (M²)</th>
                                        <th class="py-2 px-2.5 w-36 text-right">NJOP/M² (Rp)</th>
                                        <th class="py-2 px-2.5 w-40 text-right">Subtotal NJOP</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 font-medium">
                                    <!-- Bumi -->
                                    <tr>
                                        <td class="py-2 px-2.5 font-bold text-slate-800">BUMI</td>
                                        <td class="py-1.5 px-2">
                                            <input type="number" step="0.01" min="0" name="bumi_area_m2" x-model.number="editData.bumiArea" class="w-full px-2 py-1 rounded border border-slate-300 text-right font-mono">
                                        </td>
                                        <td class="py-1.5 px-2">
                                            <input type="number" step="1" min="0" name="bumi_njop_per_m2" x-model.number="editData.bumiNjop" class="w-full px-2 py-1 rounded border border-slate-300 text-right font-mono">
                                        </td>
                                        <td class="py-2 px-2.5 text-right font-mono font-bold text-slate-800" x-text="'Rp ' + formatRupiah((editData.bumiArea || 0) * (editData.bumiNjop || 0))">
                                            Rp 0
                                        </td>
                                    </tr>
                                    <!-- Bangunan -->
                                    <tr>
                                        <td class="py-2 px-2.5 font-bold text-slate-800">BANGUNAN</td>
                                        <td class="py-1.5 px-2">
                                            <input type="number" step="0.01" min="0" name="bangunan_area_m2" x-model.number="editData.bangunanArea" class="w-full px-2 py-1 rounded border border-slate-300 text-right font-mono">
                                        </td>
                                        <td class="py-1.5 px-2">
                                            <input type="number" step="1" min="0" name="bangunan_njop_per_m2" x-model.number="editData.bangunanNjop" class="w-full px-2 py-1 rounded border border-slate-300 text-right font-mono">
                                        </td>
                                        <td class="py-2 px-2.5 text-right font-mono font-bold text-slate-800" x-text="'Rp ' + formatRupiah((editData.bangunanArea || 0) * (editData.bangunanNjop || 0))">
                                            Rp 0
                                        </td>
                                    </tr>
                                    <!-- Bumi Bersama -->
                                    <tr>
                                        <td class="py-2 px-2.5 font-bold text-slate-800">BUMI BERSAMA</td>
                                        <td class="py-1.5 px-2">
                                            <input type="number" step="0.01" min="0" name="bumi_bersama_area_m2" x-model.number="editData.bumiBersamaArea" class="w-full px-2 py-1 rounded border border-slate-300 text-right font-mono">
                                        </td>
                                        <td class="py-1.5 px-2">
                                            <input type="number" step="1" min="0" name="bumi_bersama_njop_per_m2" x-model.number="editData.bumiBersamaNjop" class="w-full px-2 py-1 rounded border border-slate-300 text-right font-mono">
                                        </td>
                                        <td class="py-2 px-2.5 text-right font-mono font-bold text-slate-800" x-text="'Rp ' + formatRupiah((editData.bumiBersamaArea || 0) * (editData.bumiBersamaNjop || 0))">
                                            Rp 0
                                        </td>
                                    </tr>
                                    <!-- Bangunan Bersama -->
                                    <tr>
                                        <td class="py-2 px-2.5 font-bold text-slate-800">BANG BERSAMA</td>
                                        <td class="py-1.5 px-2">
                                            <input type="number" step="0.01" min="0" name="bangunan_bersama_area_m2" x-model.number="editData.bangunanBersamaArea" class="w-full px-2 py-1 rounded border border-slate-300 text-right font-mono">
                                        </td>
                                        <td class="py-1.5 px-2">
                                            <input type="number" step="1" min="0" name="bangunan_bersama_njop_per_m2" x-model.number="editData.bangunanBersamaNjop" class="w-full px-2 py-1 rounded border border-slate-300 text-right font-mono">
                                        </td>
                                        <td class="py-2 px-2.5 text-right font-mono font-bold text-slate-800" x-text="'Rp ' + formatRupiah((editData.bangunanBersamaArea || 0) * (editData.bangunanBersamaNjop || 0))">
                                            Rp 0
                                        </td>
                                    </tr>
                                    <tr class="bg-emerald-50/70 font-bold text-slate-900">
                                        <td colspan="3" class="py-2 px-2.5 text-right">TOTAL NJOP DASAR:</td>
                                        <td class="py-2 px-2.5 text-right font-mono text-emerald-800" x-text="'Rp ' + formatRupiah(calculateTotalNjop())">
                                            Rp 0
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 4. Nominal Tagihan & Ketetapan SPPT -->
                    <div class="bg-emerald-50/50 p-4 rounded-xl border border-emerald-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-slate-800 font-bold text-xs uppercase tracking-wider">
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">4</span>
                                Nominal Tagihan & Ketetapan SPPT
                            </div>
                            <button type="button" 
                                    @click="autoCalculatePbb()" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <span>Hitung Otomatis dari NJOP</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">PBB Terutang / Tagihan Pokok (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" 
                                       step="1" 
                                       min="0" 
                                       name="pbb_terutang" 
                                       x-model.number="editData.pbbTerutang" 
                                       required 
                                       class="w-full px-3 py-2 rounded-lg border border-slate-300 text-sm font-mono font-bold text-emerald-800 focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">Tanggal Jatuh Tempo</label>
                                <input type="date" name="due_date" x-model="editData.dueDate" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">No. Urut / Ref DHKP</label>
                                <input type="text" name="dhkp_row_ref" x-model="editData.dhkpRowRef" placeholder="Contoh: DHKP-012" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-500">
                            💡 <em>Petugas dapat mengetik nominal tagihan secara manual sesuai SPPT fisik atau memanfaatkan tombol "Hitung Otomatis dari NJOP" (NJOP - NJOPTKP 10jt × Tarif {{ number_format($village->tax_rate_percent ?? 0.1, 3) }}%).</em>
                        </p>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3 shrink-0">
                    <button type="button" 
                            @click="quickEditModalOpen = false" 
                            class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan & Perbarui Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
