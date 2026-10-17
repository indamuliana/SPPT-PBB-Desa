@extends('layouts.app')

@section('title', 'Tandai Objek sebagai TO - SADAYA PBB')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('to-cases.index') }}" class="hover:underline">Kasus TO</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Lapor Kasus Baru</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Tandai Objek Pajak Sebagai TO</h1>
            <p class="text-sm text-slate-600 mt-1">Mengunci NOP dari pembayaran kasir dan menugaskan penelusuran pemilik sebenarnya</p>
        </div>
        <a href="{{ route('to-cases.index') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition">
            Batal
        </a>
    </div>

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="text-sm font-medium">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg">
        <div class="flex items-center gap-2 font-semibold text-sm mb-1">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Terdapat kendala pengisian formulir:</span>
        </div>
        <ul class="list-disc list-inside text-xs space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
        <form action="{{ route('to-cases.store') }}" method="POST" class="space-y-6"
              x-data="{
                  nopQuery: '{{ old('nop', $selectedObject ? $selectedObject->formatted_nop : '') }}',
                  suggestions: [],
                  loading: false,
                  selectedObj: {{ $selectedObject ? json_encode([
                      'formatted_nop' => $selectedObject->formatted_nop,
                      'taxpayer_name' => $selectedObject->taxpayer->name,
                      'dusun' => $selectedObject->dusunRelation?->name ?? $selectedObject->dusun ?? '-',
                      'address' => $selectedObject->formatted_address,
                  ]) : 'null' }},
                  searchNop() {
                      if (this.nopQuery.length < 2) {
                          this.suggestions = [];
                          return;
                      }
                      this.loading = true;
                      fetch('{{ route('api.tax-objects.search') }}?q=' + encodeURIComponent(this.nopQuery))
                          .then(res => res.json())
                          .then(data => {
                              this.suggestions = data;
                              this.loading = false;
                          })
                          .catch(() => {
                              this.loading = false;
                          });
                  },
                  selectItem(item) {
                      this.nopQuery = item.nop;
                      this.selectedObj = item;
                      this.suggestions = [];
                  }
              }">
            @csrf

            @if($selectedSppt)
                <input type="hidden" name="sppt_id" value="{{ $selectedSppt->id }}">
            @endif

            <!-- Input NOP dengan Autocomplete -->
            <div class="relative">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nomor Objek Pajak (NOP) atau Nama Wajib Pajak <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" name="nop" required 
                           x-model="nopQuery"
                           @input.debounce.300ms="searchNop()"
                           placeholder="Ketik NOP atau Nama Wajib Pajak..."
                           autocomplete="off"
                           class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                    <div x-show="loading" class="absolute right-3 top-3 text-xs text-slate-400">
                        <svg class="animate-spin w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                </div>

                <!-- Dropdown Saran -->
                <div x-show="suggestions.length > 0" 
                     @click.outside="suggestions = []"
                     x-cloak
                     class="absolute z-30 left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto divide-y divide-slate-100">
                    <template x-for="item in suggestions" :key="item.id">
                        <div @click="selectItem(item)" class="p-3 hover:bg-slate-50 cursor-pointer transition">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-slate-800 text-xs" x-text="item.formatted_nop"></span>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" 
                                      :class="item.is_to ? 'bg-rose-100 text-rose-800' : (item.is_inactive ? 'bg-slate-100 text-slate-700' : 'bg-emerald-100 text-emerald-800')"
                                      x-text="item.status.toUpperCase()"></span>
                            </div>
                            <div class="text-xs font-semibold text-emerald-800 mt-0.5" x-text="item.taxpayer_name"></div>
                            <div class="text-[11px] text-slate-500 flex items-center justify-between mt-0.5">
                                <span x-text="item.dusun"></span>
                                <span class="font-mono text-slate-700" x-text="'Tagihan: Rp ' + new Intl.NumberFormat('id-ID').format(item.active_bill)"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Preview Objek Terpilih -->
                <div x-show="selectedObj" x-cloak class="mt-2 p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs space-y-1">
                    <p class="font-bold text-slate-800">Wajib Pajak: <span class="text-emerald-800" x-text="selectedObj?.taxpayer_name"></span></p>
                    <p class="text-slate-600">Wilayah / Dusun: <span class="font-semibold text-slate-700" x-text="selectedObj?.dusun"></span> &bull; <span x-text="selectedObj?.address"></span></p>
                </div>
            </div>

            <!-- Kategori Alasan TO -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Kategori Kendala / Alasan TO <span class="text-rose-500">*</span>
                </label>
                <select name="reason_type" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500 bg-white">
                    <option value="">-- Pilih Kategori Alasan TO --</option>
                    <option value="pemilik_tidak_dikenal" {{ old('reason_type') == 'pemilik_tidak_dikenal' ? 'selected' : '' }}>Subjek Pajak Tidak Dikenal / Tidak Ditemukan</option>
                    <option value="pindah_tanpa_kabar" {{ old('reason_type') == 'pindah_tanpa_kabar' ? 'selected' : '' }}>Pemilik Pindah Keluar Kota Tanpa Alamat Jelas</option>
                    <option value="sengketa" {{ old('reason_type') == 'sengketa' ? 'selected' : '' }}>Lahan Sengketa Kepemilikan / Waris</option>
                    <option value="objek_tidak_ada" {{ old('reason_type') == 'objek_tidak_ada' ? 'selected' : '' }}>Fisik Tanah / Bangunan Tidak Ditemukan</option>
                    <option value="terjual_belum_mutasi" {{ old('reason_type') == 'terjual_belum_mutasi' ? 'selected' : '' }}>Sudah Dijual tapi Belum Balik Nama Resmi</option>
                    <option value="lainnya" {{ old('reason_type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            <!-- Petugas Kolektor Penanggung Jawab -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Tugaskan Penelusuran Lapangan ke Kolektor (Opsional)
                </label>
                <select name="assigned_to_user_id" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500 bg-white">
                    <option value="">-- Tanpa Penugasan Khusus --</option>
                    @foreach($collectors as $col)
                        <option value="{{ $col->id }}" {{ old('assigned_to_user_id') == $col->id ? 'selected' : '' }}>
                            {{ $col->name }} ({{ $col->username }})
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Petugas terpilih akan melakukan investigasi ke lapangan untuk mencari pemilik baru/ahli waris.</p>
            </div>

            <!-- Deskripsi Masalah Lapangan -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Uraian Detail Masalah Lapangan <span class="text-rose-500">*</span>
                </label>
                <textarea name="description" required rows="4" 
                          placeholder="Jelaskan kondisi riil di lapangan, misalnya: Rumah kosong sejak tahun 2020, tetangga tidak mengetahui kontak pemilik, atau riwayat jual beli yang belum rampung..."
                          class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-rose-500">{{ old('description') }}</textarea>
            </div>

            <!-- Warning Notice -->
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-900 text-xs flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <span class="font-bold block">Pemberitahuan Sistem:</span>
                    NOP yang ditandai TO tidak akan dapat ditransaksikan pada loket kasir hingga kasus diselesaikan dan disahkan mutasinya oleh Administrator Desa.
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end gap-3">
                <a href="{{ route('to-cases.index') }}" class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-bold shadow-sm transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Kunci NOP & Buat Kasus TO</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
