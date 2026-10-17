@extends('layouts.app')

@section('title', 'Berita Acara Mutasi - ' . $mutation->mutation_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ rejectModalOpen: false }">

    <!-- Action Bar (Hidden when printing) -->
    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
            <a href="{{ route('mutations.index') }}" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-lg font-bold text-slate-800">Mutasi #{{ $mutation->mutation_number }}</h1>
                <div class="flex items-center gap-2 mt-0.5">
                    @if($mutation->isApproved())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Disahkan Admin Desa
                        </span>
                    @elseif($mutation->isRejected())
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span> Pengajuan Ditolak
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span> Menunggu Pengesahan Admin
                        </span>
                    @endif
                    <span class="text-xs text-slate-500">• {{ $mutation->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg text-sm font-semibold transition flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Berita Acara</span>
            </button>

            @if($mutation->isPending() && auth()->user()->isAdmin())
            <form action="{{ route('mutations.approve', $mutation) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin akan mengesahkan mutasi ini? Kepemilikan NOP akan dialihkan dan status TO (jika ada) akan dibuka.')">
                @csrf
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Sahkan Mutasi</span>
                </button>
            </form>
            <button type="button" @click="rejectModalOpen = true" class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-semibold shadow-sm transition">
                Tolak
            </button>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="no-print p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Berita Acara Printable Document Paper -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 sm:p-10 text-slate-800 printable-area">
        
        <!-- Header Dokumen Resmi -->
        <div class="border-b-2 border-slate-800 pb-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs uppercase tracking-widest font-semibold text-slate-500">Pemerintah Kabupaten Majalengka</h3>
                    <h2 class="text-base uppercase font-bold text-slate-800">Kecamatan {{ $village->district_name ?? 'Kecamatan' }} - Desa {{ $village->name ?? 'Sukahaji' }}</h2>
                    <p class="text-xs text-slate-500">{{ $village->address ?? 'Kantor Desa' }} | Telp: {{ $village->phone ?? '-' }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 bg-slate-100 border border-slate-300 font-mono text-xs font-bold uppercase rounded">
                        BA-MUT / PBB-P2
                    </span>
                    <p class="text-[11px] text-slate-500 font-mono mt-1">{{ $mutation->mutation_number }}</p>
                </div>
            </div>
            <div class="text-center mt-4">
                <h1 class="text-lg font-bold uppercase tracking-wide underline">BERITA ACARA MUTASI OBJEK & SUBJEK PAJAK PBB-P2</h1>
                <p class="text-xs text-slate-600">Nomor Registrasi Desa: {{ $mutation->mutation_number }}</p>
            </div>
        </div>

        <!-- Uraian Pihak & Objek -->
        <div class="text-xs leading-relaxed space-y-4 mb-6">
            <p>Pada hari ini, bertempat di Kantor Desa {{ $village->name }}, telah dilakukan pencatatan perubahan data administrasi Pajak Bumi dan Bangunan Perdesaan dan Perkotaan (PBB-P2) sebagai berikut:</p>
            
            <!-- Tabel Data Objek Pajak -->
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-100 px-3 py-2 font-bold text-slate-700 uppercase tracking-wider text-[11px]">
                    1. Identitas Objek Pajak (NOP)
                </div>
                <table class="w-full text-xs">
                    <tr class="border-b border-slate-200">
                        <td class="py-2 px-3 font-semibold text-slate-600 w-1/3">Nomor Objek Pajak (NOP)</td>
                        <td class="py-2 px-3 font-mono font-bold text-slate-900">{{ $mutation->taxObject->formatted_nop }}</td>
                    </tr>
                    <tr class="border-b border-slate-200">
                        <td class="py-2 px-3 font-semibold text-slate-600">Letak Lokasi Objek</td>
                        <td class="py-2 px-3 text-slate-800">{{ $mutation->taxObject->formatted_address }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 font-semibold text-slate-600">Jenis Mutasi</td>
                        <td class="py-2 px-3 font-bold text-slate-900">{{ $mutation->type_label }}</td>
                    </tr>
                </table>
            </div>

            <!-- Tabel Pengalihan Kepemilikan -->
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-100 px-3 py-2 font-bold text-slate-700 uppercase tracking-wider text-[11px]">
                    2. Perubahan Subjek Pajak (Wajib Pajak)
                </div>
                <div class="grid grid-cols-2 divide-x divide-slate-200 p-3 bg-slate-50/50">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold block">Subjek Pajak Lama (Asal):</span>
                        <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $mutation->oldTaxpayer->name }}</p>
                        <p class="text-slate-500">NIK: {{ $mutation->oldTaxpayer->nik ?? '-' }}</p>
                        <p class="text-slate-500">Alamat: {{ $mutation->oldTaxpayer->address ?? '-' }}</p>
                    </div>
                    <div class="pl-4">
                        <span class="text-[10px] text-emerald-600 uppercase tracking-wider font-semibold block">Subjek Pajak Baru (Penerima Hak):</span>
                        <p class="font-bold text-emerald-900 text-sm mt-0.5">{{ $mutation->newTaxpayer->name }}</p>
                        <p class="text-slate-600">NIK: {{ $mutation->newTaxpayer->nik ?? '-' }}</p>
                        <p class="text-slate-600">Alamat: {{ $mutation->newTaxpayer->address ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Tabel Dokumen Hukum & Alasan -->
            <div class="border border-slate-200 rounded-lg overflow-hidden">
                <div class="bg-slate-100 px-3 py-2 font-bold text-slate-700 uppercase tracking-wider text-[11px]">
                    3. Dasar Hukum & Keterangan
                </div>
                <table class="w-full text-xs">
                    <tr class="border-b border-slate-200">
                        <td class="py-2 px-3 font-semibold text-slate-600 w-1/3">Jenis Dokumen Pendukung</td>
                        <td class="py-2 px-3 font-medium text-slate-900">{{ $mutation->legal_doc_type }}</td>
                    </tr>
                    <tr class="border-b border-slate-200">
                        <td class="py-2 px-3 font-semibold text-slate-600">Nomor Dokumen / Akta</td>
                        <td class="py-2 px-3 font-mono text-slate-800">{{ $mutation->legal_doc_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 px-3 font-semibold text-slate-600 align-top">Uraian / Alasan Mutasi</td>
                        <td class="py-2 px-3 text-slate-800 leading-relaxed">{{ $mutation->reason }}</td>
                    </tr>
                    @if($mutation->rejection_notes)
                    <tr class="bg-rose-50 border-t border-rose-200">
                        <td class="py-2 px-3 font-semibold text-rose-800 align-top">Catatan Penolakan</td>
                        <td class="py-2 px-3 text-rose-900 italic">{{ $mutation->rejection_notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            @if($mutation->toCase)
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-900">
                <span class="font-bold">Penyelesaian Kasus TO:</span>
                Mutasi ini secara resmi menyelesaikan status kendala pada <strong>Kasus TO #{{ $mutation->toCase->case_number }}</strong>.
            </div>
            @endif
        </div>

        <!-- Tanda Tangan Para Pihak -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-10">
            <div>
                <p class="text-slate-500">Petugas Yang Mengajukan,</p>
                <div class="h-20 flex items-end justify-center">
                    <p class="font-bold underline text-slate-900">{{ $mutation->requestedBy->name }}</p>
                </div>
                <p class="text-slate-500 text-[11px]">{{ ucfirst($mutation->requestedBy->role) }}</p>
            </div>

            <div>
                <p class="text-slate-500">Mengesahkan,<br>Administrator Desa {{ $village->name }}</p>
                <div class="h-20 flex items-end justify-center">
                    @if($mutation->isApproved())
                        <div class="text-center">
                            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-300 font-mono block mb-1">
                                DISAHKAN {{ $mutation->approved_at ? $mutation->approved_at->format('d/m/Y') : '' }}
                            </span>
                            <p class="font-bold underline text-slate-900">{{ $mutation->approvedBy ? $mutation->approvedBy->name : 'Administrator Desa' }}</p>
                        </div>
                    @elseif($mutation->isRejected())
                        <div class="text-center">
                            <span class="text-[10px] text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-300 font-mono block mb-1">
                                DITOLAK
                            </span>
                            <p class="font-bold underline text-slate-900">{{ $mutation->approvedBy ? $mutation->approvedBy->name : 'Administrator Desa' }}</p>
                        </div>
                    @else
                        <div class="text-center">
                            <span class="text-[10px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-300 font-mono block mb-1">
                                MENUNGGU APPROVAL
                            </span>
                            <p class="text-slate-400 italic">( Belum Disahkan )</p>
                        </div>
                    @endif
                </div>
                <p class="text-slate-500 text-[11px]">Admin Sistem PBB</p>
            </div>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div x-show="rejectModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 text-slate-800" @click.away="rejectModalOpen = false">
            <h3 class="text-lg font-bold text-slate-900 mb-2">Tolak Pengajuan Mutasi</h3>
            <p class="text-xs text-slate-600 mb-4">
                Berikan alasan penolakan untuk pengajuan mutasi <span class="font-mono font-bold">{{ $mutation->mutation_number }}</span>:
            </p>
            <form action="{{ route('mutations.reject', $mutation) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="rejection_notes" required rows="3" placeholder="Contoh: Dokumen pendukung AJB belum dilegalisir notaris / PPAT..."
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-rose-500"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm">
                        Konfirmasi Tolak Mutasi
                    </button>
                </div>
            </form>
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
