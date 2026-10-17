@extends('layouts.app')

@section('title', 'Detail Objek Pajak - ' . $taxObject->formatted_nop)

@section('content')
<div class="space-y-6" x-data="{ showDeactivateModal: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tax-objects.index') }}" class="text-xs text-slate-500 hover:text-slate-800 transition">&larr; Kembali ke Daftar Objek</a>
            </div>
            <h1 class="text-2xl font-bold font-mono text-slate-900 tracking-tight mt-1">{{ $taxObject->formatted_nop }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">Letak: {{ $taxObject->formatted_address }} &bull; Wilayah: <span class="font-semibold text-slate-700">{{ $taxObject->dusunRelation?->name ?? $taxObject->dusun ?? '-' }}</span></p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('tax-objects.edit', $taxObject) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit Objek
                </a>

                @if($taxObject->isInactive())
                    <form action="{{ route('tax-objects.activate', $taxObject) }}" method="POST" onsubmit="return confirm('Aktifkan kembali Objek Pajak ini?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl transition shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Aktifkan Kembali
                        </button>
                    </form>
                @else
                    <button type="button" @click="showDeactivateModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold rounded-xl hover:bg-amber-100 transition shadow-2xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Nonaktifkan / Arsipkan
                    </button>
                @endif

                @if(! $hasPayments)
                    <form action="{{ route('tax-objects.destroy', $taxObject) }}" method="POST" onsubmit="return confirm('Peringatan: Objek Pajak ini belum memiliki transaksi dan akan dihapus permanen. Lanjutkan?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold rounded-xl hover:bg-rose-100 transition shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus Permanen
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm flex items-center justify-between">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Status Banner if TO or Inactive -->
    @if($taxObject->isInactive())
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <h3 class="text-sm font-bold text-amber-900">Objek Pajak Ini Berstatus NONAKTIF / DIARSIPKAN</h3>
                <p class="text-xs text-amber-800 mt-0.5">Alasan: <strong>{{ $taxObject->deactivation_reason_label }}</strong></p>
                @if($taxObject->deactivation_notes)
                    <p class="text-xs text-amber-700 mt-1 italic">"{{ $taxObject->deactivation_notes }}"</p>
                @endif
            </div>
        </div>
    @elseif($taxObject->isTo())
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3">
            <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                <h3 class="text-sm font-bold text-rose-900">Objek Pajak Ini Berstatus Tanpa Objek (TO)</h3>
                <p class="text-xs text-rose-800 mt-0.5">Pemilik tidak ditemukan atau sengketa batas objek. Transaksi pembayaran ditangguhkan di loket kasir.</p>
            </div>
        </div>
    @endif

    <!-- 2 Column Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Card 1: Data Objek Pajak -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Informasi Letak & Wilayah</h2>
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block">Nomor NOP</span>
                    <span class="font-mono font-bold text-slate-800 text-sm">{{ $taxObject->formatted_nop }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Status Objek</span>
                    @if($taxObject->isInactive())
                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">Nonaktif</span>
                    @elseif($taxObject->isTo())
                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Tanpa Objek (TO)</span>
                    @else
                        <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Normal</span>
                    @endif
                </div>
                <div class="col-span-2">
                    <span class="text-slate-400 block">Alamat Objek</span>
                    <span class="font-semibold text-slate-700">{{ $taxObject->address ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">RT / RW</span>
                    <span class="font-semibold text-slate-700">{{ $taxObject->rt ?: '-' }} / {{ $taxObject->rw ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Wilayah Dusun</span>
                    <span class="font-semibold text-slate-700">{{ $taxObject->dusunRelation?->name ?? $taxObject->dusun ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Card 2: Data Wajib Pajak -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Wajib Pajak (Pemilik Tercatat)</h2>
            @if($taxObject->taxpayer)
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div class="col-span-2">
                    <span class="text-slate-400 block">Nama Wajib Pajak</span>
                    <span class="font-bold text-emerald-800 text-sm">{{ $taxObject->taxpayer->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">NIK</span>
                    <span class="font-mono font-semibold text-slate-700">{{ $taxObject->taxpayer->nik ?: '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">No. Telepon / WA</span>
                    <span class="font-semibold text-slate-700">{{ $taxObject->taxpayer->phone ?: '-' }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-400 block">Alamat Wajib Pajak</span>
                    <span class="text-slate-600">{{ $taxObject->taxpayer->address ?: '-' }}</span>
                </div>
            </div>
            @else
            <p class="text-xs text-slate-400 italic">Belum ada Wajib Pajak yang ditautkan ke NOP ini.</p>
            @endif
        </div>
    </div>

    <!-- 4 Komponen Objek Pajak -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Rincian Komponen Objek (Bumi & Bangunan)</h3>
            <span class="text-xs text-slate-500">{{ $taxObject->components->count() }} Komponen Terdaftar</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200 text-[10px]">
                    <tr>
                        <th class="px-5 py-3">Komponen</th>
                        <th class="px-5 py-3 text-right">Luas (m²)</th>
                        <th class="px-5 py-3 text-center">Kelas</th>
                        <th class="px-5 py-3 text-right">NJOP per m²</th>
                        <th class="px-5 py-3 text-right">Total NJOP Dasar</th>
                        <th class="px-5 py-3 text-center">Tahun Pajak</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($taxObject->components as $comp)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-slate-800">
                            {{ match($comp->type) {
                                'bumi' => 'Bumi (Tanah)',
                                'bangunan' => 'Bangunan',
                                'bumi_bersama' => 'Bumi Bersama',
                                'bangunan_bersama' => 'Bangunan Bersama',
                                default => ucfirst($comp->type)
                            } }}
                        </td>
                        <td class="px-5 py-3 text-right font-mono">{{ number_format($comp->area_m2, 2, ',', '.') }}</td>
                        <td class="px-5 py-3 text-center font-mono">{{ $comp->class_code ?: '-' }}</td>
                        <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($comp->njop_per_m2, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono font-bold text-slate-700">Rp {{ number_format($comp->total_njop, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-center font-mono">{{ $comp->tax_year }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-6 text-center text-slate-400">Belum ada rincian komponen objek yang tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat SPPT per Tahun Pajak -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Riwayat Ketetapan SPPT</h3>
            <span class="text-xs text-slate-500">{{ $taxObject->sppts->count() }} Tahun Pajak</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200 text-[10px]">
                    <tr>
                        <th class="px-5 py-3">Tahun Pajak</th>
                        <th class="px-5 py-3 text-right">Tagihan Resmi</th>
                        <th class="px-5 py-3 text-right">Terbayar</th>
                        <th class="px-5 py-3 text-right">Sisa Tagihan</th>
                        <th class="px-5 py-3">Jatuh Tempo</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        @if(auth()->user()->isAdmin())
                        <th class="px-5 py-3 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($taxObject->sppts as $sppt)
                    <tr>
                        <td class="px-5 py-3 font-mono font-bold text-slate-800">{{ $sppt->tax_year }}</td>
                        <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($sppt->total_bill, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono text-emerald-700">Rp {{ number_format($sppt->paid_amount, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono font-bold {{ $sppt->remaining_bill > 0 ? 'text-amber-700' : 'text-slate-400' }}">
                            Rp {{ number_format($sppt->remaining_bill, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-slate-600">{{ $sppt->due_date ? $sppt->due_date->format('d/m/Y') : '-' }}</td>
                        <td class="px-5 py-3 text-center">
                            @if($sppt->isPaid())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Lunas</span>
                            @elseif($sppt->isPartial())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Cicil</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Belum Bayar</span>
                            @endif
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('sppts.edit', $sppt) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-[11px]">Edit SPPT</a>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-6 text-center text-slate-400">Belum ada SPPT yang diterbitkan untuk NOP ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat Pembayaran / Kuitansi -->
    @php
        $allPayments = \App\Models\Payment::whereIn('sppt_id', $taxObject->sppts->pluck('id'))->with(['cashier', 'sppt'])->latest('payment_date')->get();
    @endphp
    <div class="bg-white border border-slate-200 rounded-2xl shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Riwayat Pembayaran Kasir</h3>
            <span class="text-xs text-slate-500">{{ $allPayments->count() }} Transaksi Kuitansi</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase font-semibold border-b border-slate-200 text-[10px]">
                    <tr>
                        <th class="px-5 py-3">No. Kuitansi</th>
                        <th class="px-5 py-3">Tahun Pajak</th>
                        <th class="px-5 py-3">Tanggal Bayar</th>
                        <th class="px-5 py-3">Petugas Kasir</th>
                        <th class="px-5 py-3 text-right">Pokok</th>
                        <th class="px-5 py-3 text-right">Denda</th>
                        <th class="px-5 py-3 text-right">Total Bayar</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($allPayments as $pay)
                    <tr>
                        <td class="px-5 py-3 font-mono font-bold text-slate-800">{{ $pay->receipt_number }}</td>
                        <td class="px-5 py-3 font-mono">{{ $pay->sppt?->tax_year }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $pay->payment_date ? $pay->payment_date->format('d/m/Y H:i') : '-' }}</td>
                        <td class="px-5 py-3 text-slate-700">{{ $pay->cashier?->name ?? 'Kasir' }}</td>
                        <td class="px-5 py-3 text-right font-mono">Rp {{ number_format($pay->amount_principal, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono text-amber-700">Rp {{ number_format($pay->amount_fine, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-mono font-bold text-emerald-700">Rp {{ number_format($pay->total_amount, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-center">
                            @if($pay->isValid())
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Sah</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">{{ strtoupper($pay->status) }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('payments.receipt', $pay) }}" target="_blank" class="text-emerald-700 hover:text-emerald-900 font-semibold text-[11px]">Cetak Kuitansi</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-6 text-center text-slate-400">Belum ada riwayat pembayaran untuk NOP ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Deactivate / Arsipkan -->
    <div x-show="showDeactivateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl space-y-4" @click.outside="showDeactivateModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800 text-sm">Nonaktifkan / Arsipkan Objek Pajak</h3>
                <button type="button" @click="showDeactivateModal = false" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <p class="text-xs text-slate-500">NOP ini akan ditandai nonaktif sehingga tidak muncul dalam tagihan aktif loket, namun seluruh riwayat keuangan dan kuitansi tetap terjaga.</p>
            <form action="{{ route('tax-objects.deactivate', $taxObject) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penonaktifan <span class="text-rose-500">*</span></label>
                    <select name="reason" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        <option value="mutasi">Telah Dimutasi / Balik Nama</option>
                        <option value="pecah">Objek Dipecah / Dimekarkan</option>
                        <option value="to">Tanpa Objek (TO)</option>
                        <option value="hilang">Hilang / Tidak Ditemukan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Keterangan Tambahan</label>
                    <textarea name="notes" rows="3" placeholder="Catatan berkas, nomor surat, atau keterangan penonaktifan..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="showDeactivateModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-semibold transition">Simpan & Arsipkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
