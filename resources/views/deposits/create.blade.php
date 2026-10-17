@extends('layouts.app')

@section('title', 'Catat Setoran Bank - SADAYA PBB')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('deposits.index') }}" class="hover:underline">Setoran Bank</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Catat Setoran Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Catat Setoran Bank / Bapenda</h1>
            <p class="text-sm text-slate-600 mt-1">Dokumentasikan bukti penyetoran uang kas PBB ke kas daerah atau Bank Persepsi</p>
        </div>
        <a href="{{ route('deposits.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
            Kembali
        </a>
    </div>

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg">
        <div class="flex items-center gap-2 font-semibold text-sm mb-1">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Terdapat kendala saat mencatat setoran:</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Balance Status Banner -->
    <div class="bg-slate-900 text-white rounded-xl shadow-md p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <span class="text-xs uppercase tracking-wider text-slate-400 font-semibold block">Maksimal Saldo Fisik yang Dapat Disetorkan</span>
            <div class="text-3xl font-extrabold font-mono text-emerald-400 mt-1">
                Rp {{ number_format($availableBalance, 0, ',', '.') }}
            </div>
            <p class="text-xs text-slate-400 mt-1">
                Batas maksimal berdasarkan akumulasi fisik serah terima kasir yang telah Anda terima.
            </p>
        </div>
        <div class="sm:text-right">
            <span class="text-xs text-slate-400 block">Kolektor Bertanggung Jawab:</span>
            <span class="text-sm font-bold text-white">{{ auth()->user()->name }}</span>
            <span class="block text-[11px] text-slate-400">Desa {{ $village->name ?? 'Sukahaji' }}</span>
        </div>
    </div>

    @if($availableBalance <= 0)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 p-6 rounded-xl text-center space-y-3">
        <svg class="w-10 h-10 text-amber-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <h3 class="font-bold text-base">Saldo Kas Kolektor Kosong (Rp 0)</h3>
        <p class="text-xs max-w-md mx-auto">
            Anda belum memiliki saldo kas fisik untuk disetorkan ke bank. Silakan pastikan Anda telah menerima dan mengonfirmasi serah terima fisik dari Kasir/Kadus terlebih dahulu.
        </p>
        <div class="pt-2">
            <a href="{{ route('handovers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold">
                Lihat Serah Terima Kas
            </a>
        </div>
    </div>
    @else

    <!-- Form Setoran -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8"
         x-data="{
            depositAmount: {{ old('deposit_amount', $availableBalance) }},
            maxBalance: {{ $availableBalance }},
            setAll() {
                this.depositAmount = this.maxBalance;
            }
         }">
        <form action="{{ route('deposits.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Tanggal Setor -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Penyetoran <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="deposit_date" required value="{{ old('deposit_date', date('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <!-- Bank / Lembaga Tujuan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Rekening / Bank Tujuan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="destination_bank" required list="bank_suggestions" 
                           value="{{ old('destination_bank', 'Bank bjb Cabang Majalengka') }}"
                           placeholder="Contoh: Bank bjb Cabang Majalengka"
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <datalist id="bank_suggestions">
                        <option value="Bank bjb Cabang Majalengka">
                        <option value="Bank bjb KCP Sukahaji">
                        <option value="Kas Daerah / Bapenda Majalengka">
                        <option value="Pos Indonesia">
                    </datalist>
                </div>
            </div>

            <!-- Nominal Setoran -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Nominal Uang yang Disetor <span class="text-rose-500">*</span>
                    </label>
                    <button type="button" @click="setAll()" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold underline">
                        Setor Seluruh Saldo (Rp {{ number_format($availableBalance, 0, ',', '.') }})
                    </button>
                </div>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 font-mono font-bold text-sm">Rp</span>
                    <input type="number" name="deposit_amount" x-model="depositAmount" required min="1000" :max="maxBalance" step="100"
                           class="w-full pl-12 pr-4 py-3 border border-slate-300 rounded-lg text-slate-900 font-mono font-bold text-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Sisa saldo kas yang akan tersisa setelah setoran ini: 
                    <span class="font-mono font-bold text-slate-700" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(0, maxBalance - (depositAmount || 0)))"></span>
                </p>
            </div>

            <!-- Nomor Referensi / Slip Bank -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nomor Bukti Validasi / Slip Bank (Opsional)
                </label>
                <input type="text" name="bank_ref_number" value="{{ old('bank_ref_number') }}" 
                       placeholder="Contoh: BJB-2026-0049281 / No. Jurnal Transaksi"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                <p class="text-[11px] text-slate-500 mt-1">Dapat diisi nomor registrasi atau nomor bukti transfer/slip setoran cetak dari teller bank.</p>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('deposits.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan & Ajukan Pengesahan Setoran</span>
                </button>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection
