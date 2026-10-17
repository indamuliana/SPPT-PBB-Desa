@extends('layouts.public')

@section('content')
<div class="max-w-md mx-auto px-4 py-12 sm:py-16 text-center space-y-6">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-8 space-y-4">
        <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>

        <h1 class="text-xl font-bold text-slate-900">Kuitansi Tidak Ditemukan</h1>
        
        <p class="text-xs text-slate-600 leading-relaxed">
            Kode token verifikasi yang Anda pindai tidak terdaftar di sistem basis data resmi <strong class="text-slate-800">{{ $village->name }}</strong>. Harap pastikan keaslian tanda bukti pembayaran Anda ke kantor desa.
        </p>

        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-[11px] font-mono text-slate-500 break-all">
            Token: {{ $token }}
        </div>

        <div class="pt-2">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs transition">
                &larr; Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
