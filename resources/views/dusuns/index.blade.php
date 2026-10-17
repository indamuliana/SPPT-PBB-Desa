@extends('layouts.app')

@section('title', 'Pengaturan Master Dusun - SADAYA PBB')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Pengaturan Master Dusun</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola pembagian wilayah dusun dan penugasan Kepala Dusun (Kadus) untuk pengelolaan SPPT.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('dusuns.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Dusun Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Kode & Nama Dusun</th>
                        <th class="px-6 py-4">Cakupan Wilayah / RW</th>
                        <th class="px-6 py-4">Kadus Penanggung Jawab</th>
                        <th class="px-6 py-4 text-center">Objek Pajak (NOP)</th>
                        <th class="px-6 py-4 text-center">Total SPPT</th>
                        <th class="px-6 py-4">Keterangan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dusuns as $dusun)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $dusun->name }}</div>
                                @if($dusun->code)
                                    <span class="inline-block mt-0.5 text-xs font-mono px-2 py-0.5 bg-slate-100 text-slate-600 rounded">
                                        {{ $dusun->code }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-slate-700">{{ $dusun->rw ?: '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($dusun->users->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($dusun->users as $kadus)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-medium">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                {{ $kadus->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-md border border-amber-200">
                                        Belum Ada Penugasan
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-700">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ number_format($dusun->tax_objects_count) }} NOP
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-semibold text-slate-700">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ number_format($dusun->sppts_count) }} Lembar
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate">
                                {{ $dusun->description ?: '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('dusuns.edit', $dusun) }}" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition" title="Edit Dusun & Penugasan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    @if($dusun->tax_objects_count === 0)
                                        <form action="{{ route('dusuns.destroy', $dusun) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Master Dusun {{ $dusun->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition" title="Hapus Dusun">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <p class="text-base font-semibold text-slate-700">Belum ada Master Dusun terdaftar</p>
                                <p class="text-xs text-slate-400 mt-1">Tambahkan dusun untuk mengelompokkan SPPT dan menugaskan Kadus.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($dusuns->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $dusuns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
