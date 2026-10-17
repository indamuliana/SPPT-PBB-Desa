@extends('layouts.app')

@section('title', 'Edit Ketetapan SPPT - SADAYA PBB')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Edit Ketetapan SPPT</h1>
            <p class="text-sm text-slate-500 mt-1">Perbarui nominal ketetapan resmi atau jatuh tempo SPPT Tahun {{ $sppt->tax_year }}.</p>
        </div>
        <a href="{{ route('sppts.index', ['year' => $sppt->tax_year]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <!-- Ringkasan Objek & Pembayaran -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-2xs space-y-3">
        <div class="flex items-start justify-between border-b border-slate-100 pb-3">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Nomor Objek Pajak</span>
                <span class="font-mono font-bold text-slate-900 text-lg">{{ $sppt->taxObject->formatted_nop }}</span>
            </div>
            <div class="text-right">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Wajib Pajak</span>
                <span class="font-bold text-emerald-800 text-sm">{{ $sppt->taxObject->taxpayer->name }}</span>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3 text-xs pt-1">
            <div>
                <span class="text-slate-400 block">Status Pembayaran</span>
                @if($sppt->isPaid())
                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Lunas</span>
                @elseif($sppt->isPartial())
                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">Cicilan</span>
                @else
                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Belum Bayar</span>
                @endif
            </div>
            <div>
                <span class="text-slate-400 block">Telah Dibayar (Cicilan)</span>
                <span class="font-mono font-bold text-emerald-700 text-sm">Rp {{ number_format($sppt->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">Sisa Tagihan Saat Ini</span>
                <span class="font-mono font-bold text-amber-700 text-sm">Rp {{ number_format($sppt->remaining_bill, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm space-y-1">
            <div class="font-semibold">Mohon perbaiki kesalahan berikut:</div>
            <ul class="list-disc list-inside text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sppts.update', $sppt) }}" method="POST" class="bg-white border border-slate-200 rounded-2xl shadow-2xs p-6 space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="pbb_terutang" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                Ketetapan Pajak Resmi (PBB Terutang / Rp) <span class="text-rose-500">*</span>
            </label>
            <input type="number" name="pbb_terutang" id="pbb_terutang" value="{{ old('pbb_terutang', (int)$sppt->pbb_terutang) }}" required min="0" step="1" class="w-full px-3.5 py-2.5 text-base font-mono font-bold bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
            <p class="text-[11px] text-slate-400 mt-1">Nominal resmi yang tercetak pada lembar SPPT dari Bapenda.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="due_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Tanggal Jatuh Tempo
                </label>
                <input type="date" name="due_date" id="due_date" value="{{ old('due_date', $sppt->due_date ? $sppt->due_date->format('Y-m-d') : '') }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
            </div>

            <div>
                <label for="dhkp_row_ref" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    No. Urut DHKP (Buku/Hal)
                </label>
                <input type="text" name="dhkp_row_ref" id="dhkp_row_ref" value="{{ old('dhkp_row_ref', $sppt->dhkp_row_ref) }}" placeholder="Contoh: BK-1 / 0045" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
            </div>
        </div>

        <div>
            <label for="njoptkp" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                NJOPTKP (Rp)
            </label>
            <input type="number" name="njoptkp" id="njoptkp" value="{{ old('njoptkp', (int)$sppt->njoptkp) }}" min="0" step="1000" class="w-full px-3.5 py-2.5 text-sm font-mono bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('sppts.index', ['year' => $sppt->tax_year]) }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection
