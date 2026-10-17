@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{
    cancelModalOpen: false,
    cancelPaymentId: null,
    cancelReceiptNumber: '',
    openCancelModal(id, receipt) {
        this.cancelPaymentId = id;
        this.cancelReceiptNumber = receipt;
        this.cancelModalOpen = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Riwayat Kuitansi Transaksi</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftar penerimaan uang pembayaran PBB, cetak ulang kuitansi, dan pengajuan pembatalan.</p>
        </div>
        <a href="{{ route('payments.pos') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-md shadow-emerald-600/20 transition self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Buka Loket Kasir Baru</span>
        </a>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('payments.history') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:w-72">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari No Kuitansi, NOP, atau WP..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div class="w-full sm:w-44">
                <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="">Semua Status</option>
                    <option value="valid" {{ request('status') === 'valid' ? 'selected' : '' }}>Sah / Aktif</option>
                    <option value="cancellation_requested" {{ request('status') === 'cancellation_requested' ? 'selected' : '' }}>Pengajuan Batal</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="w-full sm:w-44">
                <input type="date" name="date" value="{{ request('date') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition">
                Filter
            </button>
            @if(request('search') || request('status') || request('date'))
            <a href="{{ route('payments.history') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">Reset</a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">No Kuitansi & Waktu</th>
                        <th class="px-6 py-3.5">NOP & Objek Pajak</th>
                        <th class="px-6 py-3.5">Wajib Pajak</th>
                        <th class="px-6 py-3.5">Petugas Kasir</th>
                        <th class="px-6 py-3.5 text-right">Nominal Bayar (Rp)</th>
                        <th class="px-6 py-3.5 text-center">Status</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($payments as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono font-bold text-slate-900 text-xs block">{{ $p->receipt_number }}</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $p->payment_date->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono font-semibold text-slate-900 block">{{ $p->sppt->taxObject->formatted_nop }}</span>
                            <span class="text-[11px] text-slate-400">Th. {{ $p->sppt->tax_year }} &bull; {{ $p->sppt->taxObject->dusun ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-slate-900 block">{{ $p->sppt->taxObject->taxpayer?->name }}</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $p->sppt->taxObject->taxpayer?->nik ?? '' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                            {{ $p->cashier?->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-bold text-slate-900">
                            Rp {{ number_format($p->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($p->isValid())
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Sah / Aktif
                            </span>
                            @elseif($p->isCancellationRequested())
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                Pengajuan Batal
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                Dibatalkan
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-1.5">
                            <a href="{{ route('payments.receipt', $p) }}" target="_blank" class="inline-flex items-center px-2.5 py-1 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 font-semibold text-[11px] transition">
                                Cetak
                            </a>

                            @if($p->isValid())
                            <button type="button" @click="openCancelModal({{ $p->id }}, '{{ $p->receipt_number }}')" class="inline-flex items-center px-2 py-1 rounded-lg text-rose-600 hover:bg-rose-50 font-semibold text-[11px] transition">
                                Ajukan Batal
                            </button>
                            @endif

                            @if($p->isCancellationRequested() && auth()->user()->isAdmin())
                            <!-- Admin Approval Buttons -->
                            <form action="{{ route('payments.cancel.approve', $p) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin menyetujui pembatalan kuitansi ini? Sisa tagihan SPPT akan dipulihkan.')">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-2 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] transition">
                                    Setujui Batal
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                            Belum ada riwayat transaksi pembayaran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $payments->links() }}
        </div>
    </div>

    <!-- Modal Pengajuan Pembatalan (Alpine.js) -->
    <div x-show="cancelModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60" 
         x-cloak>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-200" @click.away="cancelModalOpen = false">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Pengajuan Pembatalan Kuitansi</h3>
                <button type="button" @click="cancelModalOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>

            <p class="text-xs text-slate-600">
                Anda mengajukan pembatalan kuitansi <strong class="font-mono text-slate-900" x-text="cancelReceiptNumber"></strong>. Pengajuan ini akan diteruskan ke Administrator Desa untuk diverifikasi.
            </p>

            <form :action="'/payments/' + cancelPaymentId + '/cancel'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Pembatalan <span class="text-rose-500">*</span></label>
                    <textarea name="reason" required rows="3" placeholder="Misal: Salah input NOP, uang fisik tidak mencukupi, pembayar menarik kembali transaksi..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="cancelModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
