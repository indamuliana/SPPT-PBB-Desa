@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Tambah SPPT Manual</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Input ketetapan tagihan SPPT baru untuk NOP dan tahun pajak tertentu.</p>
        </div>
        <a href="{{ route('sppts.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('sppts.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Objek Pajak (NOP) <span class="text-rose-500">*</span></label>
                <select name="tax_object_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="">-- Pilih NOP Objek Pajak --</option>
                    @foreach($taxObjects as $op)
                    <option value="{{ $op->id }}" {{ old('tax_object_id') == $op->id ? 'selected' : '' }}>
                        {{ $op->formatted_nop }} - {{ $op->taxpayer?->name }} ({{ $op->dusun ?? $op->address }})
                    </option>
                    @endforeach
                </select>
                @error('tax_object_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Pajak <span class="text-rose-500">*</span></label>
                    <input type="number" name="tax_year" value="{{ old('tax_year', $defaultYear) }}" required min="2000" max="2099" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-bold text-emerald-700">
                    @error('tax_year')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">PBB Terutang Resmi (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" name="pbb_terutang" value="{{ old('pbb_terutang') }}" required placeholder="Contoh: 150000" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-mono font-bold text-slate-900">
                    <p class="text-[11px] text-slate-400 mt-1">Nominal ketetapan resmi pada dokumen SPPT.</p>
                    @error('pbb_terutang')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Jatuh Tempo</label>
                <input type="date" name="due_date" value="{{ old('due_date') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('sppts.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-600/20 transition">
                    Simpan SPPT
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
