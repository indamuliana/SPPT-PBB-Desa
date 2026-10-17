@extends('layouts.app')

@section('title', 'Laporan Kasus TO & Mutasi Objek - SADAYA PBB')

@section('content')
<div class="space-y-6">
    <!-- Action Bar -->
    <div class="no-print bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('reports.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-slate-800">Laporan Kasus TO & Register Mutasi Objek</h1>
                <p class="text-xs text-slate-500">Rekapitulasi objek tanah bermasalah dan perubahan kepemilikan subjek pajak</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('reports.to-and-mutations', ['export' => 'excel']) }}" 
               class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Unduh Excel (.csv)</span>
            </a>
            <button onclick="window.print()" class="px-3 py-1.5 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-xs font-semibold">
                Cetak Laporan
            </button>
        </div>
    </div>

    <!-- Official Document Paper -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 sm:p-10 text-slate-800 printable-area">
        
        <div class="border-b-2 border-slate-800 pb-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs uppercase tracking-widest font-semibold text-slate-500">Pemerintah Kabupaten {{ $village->regency_name ?? 'Majalengka' }}</h3>
                    <h2 class="text-base uppercase font-bold text-slate-800">Kecamatan {{ $village->district_name ?? 'Sukahaji' }} &bull; Desa {{ $village->name ?? 'Sukahaji' }}</h2>
                    <p class="text-xs text-slate-500">{{ $village->address ?? 'Kantor Desa' }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-slate-100 border border-slate-300 font-mono text-xs font-bold uppercase rounded">
                        LAP-PBB / TO-MUT
                    </span>
                    <p class="text-[11px] text-slate-500 mt-1">{{ now()->translatedFormat('d/m/Y H:i') }}</p>
                </div>
            </div>
            <div class="text-center mt-5">
                <h1 class="text-base sm:text-lg font-bold uppercase tracking-wide underline">
                    LAPORAN STATUS TO & BUKU REGISTER MUTASI OBJEK PAJAK
                </h1>
                <p class="text-xs text-slate-600 mt-1">PEMERINTAH DESA {{ strtoupper($village->name) }}</p>
            </div>
        </div>

        <!-- Section 1: Daftar Kasus TO -->
        <div class="mb-8">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                I. Daftar Objek Pajak Bermasalah (Status TO / Tanpa Objek)
            </h3>
            <div class="overflow-x-auto border border-slate-300 rounded-lg">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead class="bg-slate-100 text-slate-700 uppercase font-bold border-b border-slate-300">
                        <tr>
                            <th class="py-2 px-3">No. Kasus</th>
                            <th class="py-2 px-3">NOP</th>
                            <th class="py-2 px-3">Subjek Tercatat</th>
                            <th class="py-2 px-3">Letak Objek</th>
                            <th class="py-2 px-3">Kategori Kendala</th>
                            <th class="py-2 px-3 text-center">Status</th>
                            <th class="py-2 px-3">Pelapor</th>
                            <th class="py-2 px-3">Petugas Lapangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($toCases as $tc)
                        <tr>
                            <td class="py-2 px-3 font-mono font-medium">{{ $tc->case_number }}</td>
                            <td class="py-2 px-3 font-mono">{{ $tc->taxObject->formatted_nop }}</td>
                            <td class="py-2 px-3 font-medium">{{ $tc->taxObject->taxpayer->name }}</td>
                            <td class="py-2 px-3 text-slate-600">{{ $tc->taxObject->formatted_address }}</td>
                            <td class="py-2 px-3 font-semibold text-rose-700">{{ $tc->reason_label }}</td>
                            <td class="py-2 px-3 text-center capitalize">{{ $tc->status }}</td>
                            <td class="py-2 px-3">{{ $tc->reportedBy->name }}</td>
                            <td class="py-2 px-3">{{ $tc->assignedTo ? $tc->assignedTo->name : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-4 text-center text-slate-400">Belum ada data kasus TO.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Buku Register Mutasi & Balik Nama -->
        <div class="mb-8">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                II. Buku Register Pengalihan Hak Kepemilikan (Mutasi SPPT / Balik Nama)
            </h3>
            <div class="overflow-x-auto border border-slate-300 rounded-lg">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead class="bg-slate-100 text-slate-700 uppercase font-bold border-b border-slate-300">
                        <tr>
                            <th class="py-2 px-3">No. Mutasi</th>
                            <th class="py-2 px-3">Tanggal</th>
                            <th class="py-2 px-3">NOP</th>
                            <th class="py-2 px-3">Jenis Mutasi</th>
                            <th class="py-2 px-3">Subjek Pajak Asal</th>
                            <th class="py-2 px-3">Subjek Pajak Baru</th>
                            <th class="py-2 px-3">Dokumen Dasar</th>
                            <th class="py-2 px-3 text-center">Status</th>
                            <th class="py-2 px-3">Pengesah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($mutations as $m)
                        <tr>
                            <td class="py-2 px-3 font-mono font-medium">{{ $m->mutation_number }}</td>
                            <td class="py-2 px-3 text-slate-500 whitespace-nowrap">{{ $m->created_at->format('d/m/Y') }}</td>
                            <td class="py-2 px-3 font-mono">{{ $m->taxObject->formatted_nop }}</td>
                            <td class="py-2 px-3">{{ $m->type_label }}</td>
                            <td class="py-2 px-3 line-through text-slate-500">{{ $m->oldTaxpayer->name }}</td>
                            <td class="py-2 px-3 font-bold text-emerald-800">{{ $m->newTaxpayer->name }}</td>
                            <td class="py-2 px-3">{{ $m->legal_doc_type }} {{ $m->legal_doc_number ? '('.$m->legal_doc_number.')' : '' }}</td>
                            <td class="py-2 px-3 text-center capitalize">{{ $m->status }}</td>
                            <td class="py-2 px-3 text-slate-600">{{ $m->approvedBy ? $m->approvedBy->name : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-4 text-center text-slate-400">Belum ada riwayat mutasi balik nama.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-10">
            <div>
                <p class="text-slate-500">Mengetahui,<br>Kepala Desa {{ $village->name }}</p>
                <div class="h-24 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900">{{ $village->head_name ?? '..................................' }}</p>
                </div>
            </div>
            <div>
                <p class="text-slate-500">{{ $village->name }}, {{ now()->translatedFormat('d F Y') }}<br>Administrator PBB Desa</p>
                <div class="h-24 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900">{{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    body { background-color: #fff !important; }
    .printable-area { border: none !important; box-shadow: none !important; padding: 0 !important; }
}
</style>
@endsection
