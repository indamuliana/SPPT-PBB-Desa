@extends('layouts.app')

@section('title', 'Kasus TO - ' . $toCase->case_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('to-cases.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-slate-800">Kasus TO #{{ $toCase->case_number }}</h1>
                <div class="flex items-center gap-2 mt-0.5">
                    @if($toCase->isResolved())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Kasus Selesai
                        </span>
                    @elseif($toCase->isInvestigating())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> Dalam Penelusuran
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Kasus Terbuka
                        </span>
                    @endif
                    <span class="text-xs text-slate-500">• Dilaporkan {{ $toCase->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if(!$toCase->isResolved())
            <a href="{{ route('mutations.create', ['to_case_id' => $toCase->id, 'tax_object_id' => $toCase->tax_object_id]) }}" 
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Ajukan Mutasi / Balik Nama</span>
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Grid Detail & Form Log -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left: Case & Object Details -->
        <div class="space-y-6 md:col-span-1">
            <!-- Object Info Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-4">
                <h3 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Informasi Objek Pajak</h3>
                <div>
                    <span class="text-[11px] text-slate-500 uppercase tracking-wider block font-semibold">NOP Terdaftar</span>
                    <span class="font-mono font-bold text-slate-900 text-sm">{{ $toCase->taxObject->formatted_nop }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 uppercase tracking-wider block font-semibold">Subjek Pajak Tercatat</span>
                    <span class="font-medium text-slate-800 text-xs">{{ $toCase->taxObject->taxpayer->name }}</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 uppercase tracking-wider block font-semibold">Letak Objek</span>
                    <span class="text-xs text-slate-600 block">{{ $toCase->taxObject->formatted_address }}</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-[11px] text-slate-500 uppercase tracking-wider block font-semibold">Kategori Kendala</span>
                    <span class="inline-block mt-1 px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded text-xs font-semibold">
                        {{ $toCase->reason_label }}
                    </span>
                </div>
            </div>

            <!-- Stakeholders Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 space-y-3">
                <h3 class="font-bold text-slate-800 text-sm border-b border-slate-100 pb-2">Petugas Terkait</h3>
                <div>
                    <span class="text-[11px] text-slate-500 uppercase tracking-wider block font-semibold">Pelapor Kasus</span>
                    <span class="font-medium text-slate-800 text-xs">{{ $toCase->reportedBy->name }} ({{ ucfirst($toCase->reportedBy->role) }})</span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-500 uppercase tracking-wider block font-semibold">Petugas Lapangan</span>
                    <span class="font-medium text-slate-800 text-xs">{{ $toCase->assignedTo ? $toCase->assignedTo->name : 'Belum Ditugaskan' }}</span>
                </div>
            </div>
        </div>

        <!-- Right: Description, Log History, and Add Update Form -->
        <div class="space-y-6 md:col-span-2">
            <!-- Initial Problem Description -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-3">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Uraian Kendala Awal</span>
                </h3>
                <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 text-xs leading-relaxed text-slate-700 whitespace-pre-wrap">{{ $toCase->description }}</div>
            </div>

            <!-- Investigation Timeline Log -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span>Histori Penelusuran Lapangan</span>
                </h3>

                @if($toCase->investigation_notes)
                    <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 text-xs leading-relaxed text-slate-800 font-mono whitespace-pre-wrap">{{ $toCase->investigation_notes }}</div>
                @else
                    <p class="text-xs text-slate-400 italic">Belum ada catatan investigasi lapangan yang dicatat.</p>
                @endif

                <!-- Form Append Investigation Note -->
                @if(!$toCase->isResolved())
                <form action="{{ route('to-cases.investigation', $toCase) }}" method="POST" class="pt-4 border-t border-slate-200 space-y-4">
                    @csrf
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Perbarui Status & Tambah Catatan Lapangan</h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Status Kasus
                            </label>
                            <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                                <option value="investigating" {{ $toCase->status === 'investigating' ? 'selected' : '' }}>Sedang Diselidiki (Investigasi)</option>
                                <option value="open" {{ $toCase->status === 'open' ? 'selected' : '' }}>Kasus Terbuka (Belum Selesai)</option>
                                <option value="resolved" {{ $toCase->status === 'resolved' ? 'selected' : '' }}>Selesai (Kasus Ditutup)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Ubah Petugas Lapangan
                            </label>
                            <select name="assigned_to_user_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500">
                                <option value="">-- Tetap / Pilih Kolektor --</option>
                                @foreach($collectors as $col)
                                    <option value="{{ $col->id }}" {{ $toCase->assigned_to_user_id == $col->id ? 'selected' : '' }}>
                                        {{ $col->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Catatan Temuan Lapangan Baru <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="new_notes" required rows="3" 
                                  placeholder="Contoh: Telah menemui Ketua RT 02. Tanah dikonfirmasi telah dibeli oleh Bpk. Hendra pada tahun 2024 dan siap dilakukan mutasi..."
                                  class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold transition">
                            Simpan Catatan Investigasi
                        </button>
                    </div>
                </form>
                @endif
            </div>

            <!-- Linked Mutations -->
            @if($toCase->mutations->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-3">
                <h3 class="font-bold text-slate-800 text-sm">Mutasi yang Terhubung dengan Kasus Ini</h3>
                <div class="divide-y divide-slate-100 border border-slate-200 rounded-lg overflow-hidden">
                    @foreach($toCase->mutations as $mut)
                    <div class="p-3 bg-slate-50 flex items-center justify-between text-xs">
                        <div>
                            <a href="{{ route('mutations.show', $mut) }}" class="font-mono font-bold text-emerald-700 hover:underline">
                                {{ $mut->mutation_number }}
                            </a>
                            <span class="text-slate-500 block">Dialihkan ke: <strong>{{ $mut->newTaxpayer->name }}</strong></span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $mut->isApproved() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ ucfirst($mut->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
