@extends('layouts.app')

@section('title', 'Tambah Master Dusun - SADAYA PBB')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Tambah Master Dusun Baru</h1>
            <p class="text-sm text-slate-500 mt-1">Daftarkan pembagian wilayah dusun dan tentukan Kepala Dusun (Kadus) pengelola.</p>
        </div>
        <a href="{{ route('dusuns.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
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

    <form action="{{ route('dusuns.store') }}" method="POST" class="bg-white border border-slate-200 rounded-2xl shadow-xs p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2">
                <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Nama Dusun <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Contoh: Dusun I Babakan" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
            </div>

            <div>
                <label for="code" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Kode Wilayah (Opsional)
                </label>
                <input type="text" name="code" id="code" value="{{ old('code') }}" placeholder="Contoh: DS-01" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
            </div>

            <div>
                <label for="rw" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Cakupan RW
                </label>
                <input type="text" name="rw" id="rw" value="{{ old('rw') }}" placeholder="Contoh: RW 01, RW 02" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Keterangan / Batas Wilayah
                </label>
                <textarea name="description" id="description" rows="2" placeholder="Catatan informasi wilayah dusun..." class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100">
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                Tugaskan Kepala Dusun (Kadus / Kasir)
            </label>
            <p class="text-xs text-slate-500 mb-3">Pilih satu atau lebih Kadus penanggung jawab untuk wilayah ini. Kadus dapat mengelola lebih dari satu Dusun.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-56 overflow-y-auto p-1">
                @forelse($kadusUsers as $kadus)
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl hover:bg-slate-50 cursor-pointer transition">
                        <input type="checkbox" name="user_ids[]" value="{{ $kadus->id }}" {{ in_array($kadus->id, old('user_ids', [])) ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">{{ $kadus->name }}</div>
                            <div class="text-xs text-slate-500">{{ $kadus->username }} &bull; {{ $kadus->phone ?: 'Tanpa HP' }}</div>
                        </div>
                    </label>
                @empty
                    <div class="sm:col-span-2 text-center py-4 text-sm text-slate-400">
                        Belum ada akun dengan peran Kadus yang aktif.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('dusuns.index') }}" class="px-4 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition">
                Simpan Dusun
            </button>
        </div>
    </form>
</div>
@endsection
