@extends('layouts.app')

@section('title', 'Setoran Bank/Bapenda - SADAYA PBB')

@section('content')
<div class="space-y-6" x-data="{ rejectModalOpen: false, rejectUrl: '', depositNumber: '' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Setoran Bank / Bapenda</h1>
            <p class="text-sm text-slate-600 mt-1">Penyetoran kas fisik penerimaan PBB dari Kolektor Desa ke Rekening Kas Daerah / Bank BJB</p>
        </div>
        @if(auth()->user()->isKolektor() || auth()->user()->isAdmin())
        <div>
            <a href="{{ route('deposits.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Catat Setoran Bank</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Collector Cash Balance Card (If Kolektor) -->
    @if(auth()->user()->isKolektor())
    <div class="bg-gradient-to-r from-emerald-800 to-teal-900 text-white p-6 rounded-xl shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs uppercase tracking-wider text-emerald-200 font-semibold block">Kas Fisik Siap Disetor ke Bank</span>
            <div class="text-3xl font-extrabold font-mono text-white mt-1">
                Rp {{ number_format($availableBalance, 0, ',', '.') }}
            </div>
            <p class="text-xs text-emerald-100/80 mt-1">
                Total kas fisik yang telah Anda terima dari kasir dikurangi setoran yang sudah disahkan Admin.
            </p>
        </div>
        @if($availableBalance > 0)
        <a href="{{ route('deposits.create') }}" class="px-5 py-2.5 bg-white text-emerald-900 hover:bg-emerald-50 rounded-lg text-sm font-bold shadow transition self-start sm:self-auto">
            Setorkan Sekarang
        </a>
        @endif
    </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-700">
                <thead class="bg-slate-50 text-slate-700 text-xs font-semibold uppercase border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">No. Setoran</th>
                        <th class="py-3 px-4">Tanggal Setor</th>
                        <th class="py-3 px-4">Kolektor</th>
                        <th class="py-3 px-4">Tujuan Setoran</th>
                        <th class="py-3 px-4">No. Ref Bank</th>
                        <th class="py-3 px-4 text-right">Nominal Setoran</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Pengesah</th>
                        @if(auth()->user()->isAdmin())
                        <th class="py-3 px-4 text-center">Aksi Verifikasi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($deposits as $d)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-mono font-medium text-slate-900 text-xs">
                            {{ $d->deposit_number }}
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-600 whitespace-nowrap">
                            {{ $d->deposit_date->format('d/m/Y') }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="font-medium text-slate-800 text-xs">{{ $d->collector->name }}</span>
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-700">
                            {{ $d->destination_bank }}
                        </td>
                        <td class="py-3 px-4 text-xs font-mono text-slate-600">
                            {{ $d->bank_ref_number ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-sm font-bold text-slate-900 whitespace-nowrap">
                            Rp {{ number_format($d->deposit_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($d->isApproved())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Disahkan
                                </span>
                            @elseif($d->isRejected())
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Ditolak
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> Menunggu Approval
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-600">
                            @if($d->verifier)
                                <span class="font-medium text-slate-800">{{ $d->verifier->name }}</span>
                                <span class="block text-[10px] text-slate-400">{{ $d->approved_at ? $d->approved_at->format('d/m/y H:i') : '' }}</span>
                            @else
                                <span class="text-slate-400 italic">Belum</span>
                            @endif
                        </td>

                        @if(auth()->user()->isAdmin())
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @if($d->isSubmitted())
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('deposits.approve', $d) }}" method="POST" onsubmit="return confirm('Sahkan setoran bank sebesar Rp {{ number_format($d->deposit_amount, 0, ',', '.') }}?')">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold shadow-sm transition">
                                        Sahkan
                                    </button>
                                </form>
                                <button type="button" 
                                        @click="rejectUrl = '{{ route('deposits.reject', $d) }}'; depositNumber = '{{ $d->deposit_number }}'; rejectModalOpen = true"
                                        class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-xs font-semibold shadow-sm transition">
                                    Tolak
                                </button>
                            </div>
                            @elseif($d->isRejected())
                            <span class="text-[11px] text-rose-600 italic" title="{{ $d->rejection_notes }}">Alasan: {{ Str::limit($d->rejection_notes, 20) }}</span>
                            @else
                            <span class="text-emerald-600 text-xs font-medium">Selesai</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? 9 : 8 }}" class="py-12 text-center text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <p class="font-medium text-slate-600">Belum ada catatan setoran bank</p>
                            <p class="text-xs text-slate-400 mt-1">Kolektor desa dapat mencatat setoran setelah menerima kas fisik dari kasir.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($deposits->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50">
            {{ $deposits->links() }}
        </div>
        @endif
    </div>

    <!-- Rejection Modal -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 text-slate-800" @click.away="rejectModalOpen = false">
            <h3 class="text-lg font-bold text-slate-900 mb-2">Tolak Setoran Bank</h3>
            <p class="text-xs text-slate-600 mb-4">
                Berikan alasan penolakan untuk setoran <span class="font-mono font-bold" x-text="depositNumber"></span>:
            </p>
            <form :action="rejectUrl" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="rejection_notes" required rows="3" placeholder="Contoh: Bukti slip validasi bank buram atau nominal tidak cocok..."
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm">
                        Konfirmasi Tolak Setoran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
