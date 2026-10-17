@extends('layouts.app')

@section('title', 'Buat Serah Terima Kas - SADAYA PBB')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" 
     x-data="{
        selectedPayments: {{ json_encode($unhandedPayments->pluck('id')) }},
        paymentsData: {{ json_encode($unhandedPayments->map(fn($p) => ['id' => $p->id, 'amount' => (float)$p->total_amount])) }},
        physicalAmount: {{ (float)$unhandedPayments->sum('total_amount') }},
        differenceReason: '',

        get systemAmount() {
            return this.paymentsData
                .filter(p => this.selectedPayments.includes(p.id))
                .reduce((sum, p) => sum + p.amount, 0);
        },

        get difference() {
            return (parseFloat(this.physicalAmount) || 0) - this.systemAmount;
        },

        toggleAll(e) {
            if (e.target.checked) {
                this.selectedPayments = this.paymentsData.map(p => p.id);
            } else {
                this.selectedPayments = [];
            }
        },

        setPhysicalToSystem() {
            this.physicalAmount = this.systemAmount;
        },

        formatRp(val) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(val || 0);
        }
     }">

    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('handovers.index') }}" class="hover:underline">Serah Terima Kas</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Buat Pengajuan Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Form Serah Terima Kas Kasir</h1>
            <p class="text-sm text-slate-600 mt-1">Pilih transaksi penerimaan PBB yang akan diserahkan kepada Petugas Kolektor Desa</p>
        </div>
        <a href="{{ route('handovers.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
            Batal
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg">
        <div class="flex items-center gap-2 font-semibold text-sm mb-1">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Terdapat kendala pada pengajuan serah terima:</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($unhandedPayments->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h2 class="text-lg font-bold text-slate-800">Semua Kas Telah Diserahterimakan</h2>
        <p class="text-sm text-slate-500 max-w-md mx-auto mt-2">
            Tidak ada transaksi pembayaran aktif yang belum diserahterimakan pada akun Anda. Transaksi baru yang dicatat di loket kasir akan muncul di sini.
        </p>
        <div class="mt-6 flex justify-center gap-3">
            <a href="{{ route('payments.pos') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition">
                Buka Loket Kasir
            </a>
            <a href="{{ route('handovers.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
                Kembali ke Daftar
            </a>
        </div>
    </div>
    @else

    <form action="{{ route('handovers.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Grid: Collector Selection & Cash Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left: Kolektor Target & Notes -->
            <div class="md:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-5">
                <h2 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Pihak Penerima Kas</span>
                </h2>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Pilih Kolektor Desa yang Menerima <span class="text-rose-500">*</span>
                    </label>
                    <select name="collector_user_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        <option value="">-- Pilih Petugas Kolektor --</option>
                        @foreach($collectors as $c)
                            <option value="{{ $c->id }}" {{ old('collector_user_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} ({{ $c->username }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">Uang fisik kasir akan dialihkan ke tanggung jawab kolektor ini setelah dikonfirmasi.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Catatan Tambahan (Opsional)
                    </label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Contoh: Titipan pembayaran kadus blok timur" 
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <!-- Right: Real-time Cash Accounting Card -->
            <div class="bg-slate-900 text-white rounded-xl shadow-md p-6 flex flex-col justify-between">
                <div>
                    <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold mb-4">
                        Kalkulasi Kas
                    </div>

                    <div class="space-y-4">
                        <div>
                            <span class="text-xs text-slate-400 block">Total Nominal Sistem (Terpilih)</span>
                            <span class="text-2xl font-bold font-mono text-emerald-400" x-text="formatRp(systemAmount)"></span>
                            <span class="text-[11px] text-slate-400 block mt-0.5" x-text="selectedPayments.length + ' transaksi dipilih'"></span>
                        </div>

                        <div class="pt-3 border-t border-slate-800">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs text-slate-300 font-medium">Uang Fisik Diserahkan</label>
                                <button type="button" @click="setPhysicalToSystem()" class="text-[11px] text-emerald-400 hover:text-emerald-300 underline">
                                    Set Sama
                                </button>
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-mono text-sm">Rp</span>
                                <input type="number" name="physical_amount" x-model="physicalAmount" required min="0" step="100"
                                       class="w-full pl-10 pr-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white font-mono font-bold text-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <!-- Selisih Status -->
                        <div class="pt-3 border-t border-slate-800">
                            <span class="text-xs text-slate-400 block">Selisih Kas Fisik</span>
                            <div class="flex items-center gap-2 mt-1">
                                <template x-if="difference === 0">
                                    <span class="px-2.5 py-1 bg-emerald-950 text-emerald-400 border border-emerald-800 rounded text-xs font-semibold">
                                        Pas / Sesuai (Rp 0)
                                    </span>
                                </template>
                                <template x-if="difference > 0">
                                    <span class="px-2.5 py-1 bg-blue-950 text-blue-400 border border-blue-800 rounded text-xs font-semibold"
                                          x-text="'Lebih: ' + formatRp(difference)">
                                    </span>
                                </template>
                                <template x-if="difference < 0">
                                    <span class="px-2.5 py-1 bg-rose-950 text-rose-400 border border-rose-800 rounded text-xs font-semibold"
                                          x-text="'Kurang: ' + formatRp(Math.abs(difference))">
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Difference Reason Field (Shows if difference != 0) -->
                <div x-show="difference !== 0" x-cloak class="mt-4 pt-4 border-t border-slate-800">
                    <label class="block text-xs font-semibold text-amber-400 uppercase tracking-wider mb-1">
                        Alasan Selisih Kas <span class="text-rose-400">*</span>
                    </label>
                    <textarea name="difference_reason" x-model="differenceReason" rows="2" 
                              :required="difference !== 0"
                              placeholder="Wajib dijelaskan penyebab selisih kas fisik..."
                              class="w-full px-2.5 py-1.5 bg-slate-800 border border-amber-600 rounded text-xs text-white placeholder-slate-500 focus:ring-2 focus:ring-amber-500"></textarea>
                </div>
            </div>
        </div>

        <!-- Section: Itemized Payments Checklist -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="check-all" @click="toggleAll($event)" :checked="selectedPayments.length === paymentsData.length && paymentsData.length > 0"
                           class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <label for="check-all" class="text-sm font-bold text-slate-800 cursor-pointer">
                        Pilih Semua Transaksi ({{ $unhandedPayments->count() }} Kuitansi)
                    </label>
                </div>
                <div class="text-xs text-slate-500">
                    Centang kuitansi yang uang fisiknya Anda serahkan pada sesi ini
                </div>
            </div>

            <div class="overflow-x-auto max-h-96">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-100/75 text-slate-600 text-xs font-semibold sticky top-0 border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-4 w-10 text-center">Pilih</th>
                            <th class="py-2.5 px-4">No. Kuitansi</th>
                            <th class="py-2.5 px-4">Waktu Bayar</th>
                            <th class="py-2.5 px-4">NOP & Objek</th>
                            <th class="py-2.5 px-4">Wajib Pajak</th>
                            <th class="py-2.5 px-4 text-center">Tahun</th>
                            <th class="py-2.5 px-4 text-right">Nominal Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($unhandedPayments as $payment)
                        <tr class="hover:bg-slate-50 transition" :class="{'bg-emerald-50/40': selectedPayments.includes({{ $payment->id }})}">
                            <td class="py-2.5 px-4 text-center">
                                <input type="checkbox" name="payment_ids[]" value="{{ $payment->id }}" 
                                       x-model="selectedPayments" :value="{{ $payment->id }}"
                                       class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            </td>
                            <td class="py-2.5 px-4 font-mono font-medium text-slate-900 text-xs">
                                {{ $payment->receipt_number }}
                            </td>
                            <td class="py-2.5 px-4 text-xs text-slate-500">
                                {{ $payment->payment_date->format('d/m/Y H:i') }}
                            </td>
                            <td class="py-2.5 px-4 text-xs font-mono text-slate-700">
                                {{ $payment->sppt->taxObject->formatted_nop }}
                            </td>
                            <td class="py-2.5 px-4 text-xs font-medium text-slate-800">
                                {{ $payment->sppt->taxObject->taxpayer->name }}
                            </td>
                            <td class="py-2.5 px-4 text-xs font-mono text-center">
                                {{ $payment->sppt->tax_year }}
                            </td>
                            <td class="py-2.5 px-4 text-xs font-mono font-semibold text-right text-slate-900">
                                Rp {{ number_format($payment->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Action Button -->
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <div class="text-xs text-slate-500">
                    Pastikan uang fisik telah dihitung bersama sebelum menekan tombol serahkan.
                </div>
                <button type="submit" 
                        :disabled="selectedPayments.length === 0"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white rounded-lg font-semibold text-sm shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Serahkan Kas ke Kolektor</span>
                </button>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection
