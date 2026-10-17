@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Edit Data Objek Pajak (NOP)</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Perbarui rincian NOP, penanggung jawab, letak objek, dan status.</p>
        </div>
        <a href="{{ route('tax-objects.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('tax-objects.update', $taxObject) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Objek Pajak (NOP) <span class="text-rose-500">*</span></label>
                <input type="text" name="nop" value="{{ old('nop', $taxObject->formatted_nop) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-mono">
                @error('nop')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-semibold text-slate-700">Wajib Pajak Penanggung Jawab <span class="text-rose-500">*</span></label>
                    <a href="{{ route('taxpayers.create') }}" target="_blank" class="text-[11px] text-emerald-600 hover:underline font-semibold">+ Buat WP Baru</a>
                </div>
                <select name="taxpayer_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    @foreach($taxpayers as $wp)
                    <option value="{{ $wp->id }}" {{ old('taxpayer_id', $taxObject->taxpayer_id) == $wp->id ? 'selected' : '' }}>
                        {{ $wp->name }} {{ $wp->nik ? '(' . $wp->nik . ')' : '' }} - {{ $wp->dusun ?? $wp->address }}
                    </option>
                    @endforeach
                </select>
                @error('taxpayer_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Letak Objek Pajak</label>
                    <input type="text" name="address" value="{{ old('address', $taxObject->address) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">RT</label>
                    <input type="text" name="rt" value="{{ old('rt', $taxObject->rt) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">RW</label>
                    <input type="text" name="rw" value="{{ old('rw', $taxObject->rw) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Wilayah Dusun</label>
                    <select name="dusun_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        <option value="">-- Pilih Dusun --</option>
                        @foreach($dusuns as $ds)
                            <option value="{{ $ds->id }}" {{ (string)old('dusun_id', $taxObject->dusun_id) === (string)$ds->id ? 'selected' : '' }}>
                                {{ $ds->name }} {{ $ds->code ? '(' . $ds->code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Objek Pajak <span class="text-rose-500">*</span></label>
                <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="normal" {{ old('status', $taxObject->status) === 'normal' ? 'selected' : '' }}>Normal (Objek & Pemilik Jelas)</option>
                    <option value="to" {{ old('status', $taxObject->status) === 'to' ? 'selected' : '' }}>Tanpa Objek (TO - Pemilik Tidak Jelas / Pindah Tangan)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Objek Pajak</label>
                <textarea name="notes" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('notes', $taxObject->notes) }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('tax-objects.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-600/20 transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
