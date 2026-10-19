@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Tambah Objek Pajak (NOP)</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Daftarkan NOP resmi dan tautkan dengan Wajib Pajak penanggung jawab.</p>
        </div>
        <a href="{{ route('tax-objects.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 sm:p-8">
        <form method="POST" action="{{ route('tax-objects.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Objek Pajak (NOP) <span class="text-rose-500">*</span></label>
                <input type="text" name="nop" value="{{ old('nop') }}" required placeholder="Contoh: 32.04.120.001.005-0001.0 (18 digit angka)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-mono">
                <p class="text-[11px] text-slate-400 mt-1">Titik dan strip otomatis diselaraskan oleh sistem.</p>
                @error('nop')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-semibold text-slate-700">Wajib Pajak Penanggung Jawab <span class="text-rose-500">*</span></label>
                    <a href="{{ route('taxpayers.create') }}" target="_blank" class="text-[11px] text-emerald-600 hover:underline font-semibold">+ Buat WP Baru</a>
                </div>
                <select name="taxpayer_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="">-- Pilih Wajib Pajak --</option>
                    @foreach($taxpayers as $wp)
                    <option value="{{ $wp->id }}" {{ old('taxpayer_id') == $wp->id ? 'selected' : '' }}>
                        {{ $wp->name }} {{ $wp->nik ? '(' . $wp->nik . ')' : '' }} - {{ $wp->dusun ?? $wp->address }}
                    </option>
                    @endforeach
                </select>
                @error('taxpayer_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Letak Objek Pajak</label>
                    <input type="text" name="address" value="{{ old('address') }}" placeholder="Contoh: Blok Sawah Kulon / Jl. Raya" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">RT</label>
                    <input type="text" name="rt" value="{{ old('rt') }}" placeholder="01" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">RW</label>
                    <input type="text" name="rw" value="{{ old('rw') }}" placeholder="02" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Wilayah Dusun</label>
                    <select name="dusun_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        <option value="">-- Pilih Dusun --</option>
                        @foreach($dusuns as $ds)
                            <option value="{{ $ds->id }}" {{ old('dusun_id') == $ds->id ? 'selected' : '' }}>
                                {{ $ds->name }} {{ $ds->code ? '(' . $ds->code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Objek Pajak <span class="text-rose-500">*</span></label>
                <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                    <option value="normal" {{ old('status') === 'normal' ? 'selected' : '' }}>Normal (Objek & Pemilik Jelas)</option>
                    <option value="to" {{ old('status') === 'to' ? 'selected' : '' }}>Tanpa Objek (TO - Pemilik Tidak Jelas / Pindah Tangan)</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Status TO secara otomatis memblokir pembayaran pada loket kasir sampai dimutasi.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Objek Pajak</label>
                <textarea name="notes" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('notes') }}</textarea>
            </div>

            <!-- Accordion / Toggle Komponen & SPPT Opsional -->
            <div class="border border-emerald-200/80 rounded-2xl p-4 sm:p-5 bg-emerald-50/30 space-y-4 transition">
                <div class="flex items-start sm:items-center justify-between cursor-pointer gap-3 select-none" onclick="document.getElementById('include_components').click()">
                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="include_components" name="include_components" value="1" {{ old('include_components') ? 'checked' : '' }} onclick="event.stopPropagation()" onchange="toggleComponentsPanel(this.checked)" class="mt-0.5 sm:mt-0 w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer">
                        <div>
                            <span class="text-xs sm:text-sm font-bold text-slate-900 flex items-center gap-1.5">
                                Sertakan Rincian 4 Komponen & Buat SPPT Tahun Aktif (Opsional)
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-emerald-100 text-emerald-800">Opsional</span>
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">Centang jika ingin langsung merekam data fisik tanah/bangunan dan menerbitkan tagihan SPPT tahun {{ $village->active_tax_year ?? date('Y') }}.</p>
                        </div>
                    </div>
                    <span id="accordion_icon" class="text-emerald-700 text-xs font-semibold whitespace-nowrap">
                        {{ old('include_components') ? '▲ Tutup' : '▼ Buka Form' }}
                    </span>
                </div>

                <div id="components_panel" style="{{ old('include_components') ? '' : 'display: none;' }}" class="space-y-4 pt-4 border-t border-emerald-200/60">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Pajak SPPT <span class="text-rose-500">*</span></label>
                            <input type="number" name="tax_year" value="{{ old('tax_year', $village->active_tax_year ?? date('Y')) }}" min="2000" max="2099" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-mono font-bold text-emerald-700 bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Jatuh Tempo</label>
                            <input type="date" name="due_date" value="{{ old('due_date', ($village->active_tax_year ?? date('Y')) . '-09-30') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm bg-white">
                        </div>
                    </div>

                    <!-- 4 Komponen Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-2xs">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th class="py-2.5 px-3">Kategori Komponen</th>
                                    <th class="py-2.5 px-3 w-28">Luas (M²)</th>
                                    <th class="py-2.5 px-3 w-20 text-center">Kelas</th>
                                    <th class="py-2.5 px-3 w-32 text-right">NJOP/M² (Rp)</th>
                                    <th class="py-2.5 px-3 w-36 text-right">Total NJOP (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-slate-800">1. BUMI</td>
                                    <td class="py-2 px-2"><input type="number" step="0.01" min="0" name="bumi_area_m2" id="bumi_area" value="{{ old('bumi_area_m2', 0) }}" oninput="calcPbb()" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-right text-xs focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-2"><input type="text" name="bumi_class_code" value="{{ old('bumi_class_code', 'A') }}" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-center text-xs uppercase focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-2"><input type="number" step="1" min="0" name="bumi_njop_per_m2" id="bumi_njop" value="{{ old('bumi_njop_per_m2', 0) }}" oninput="calcPbb()" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-right text-xs focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-800" id="bumi_total">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-slate-800">2. BANGUNAN</td>
                                    <td class="py-2 px-2"><input type="number" step="0.01" min="0" name="bangunan_area_m2" id="bng_area" value="{{ old('bangunan_area_m2', 0) }}" oninput="calcPbb()" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-right text-xs focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-2"><input type="text" name="bangunan_class_code" value="{{ old('bangunan_class_code', 'B') }}" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-center text-xs uppercase focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-2"><input type="number" step="1" min="0" name="bangunan_njop_per_m2" id="bng_njop" value="{{ old('bangunan_njop_per_m2', 0) }}" oninput="calcPbb()" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-right text-xs focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-800" id="bng_total">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-3 font-medium text-slate-600">3. Bumi Bersama</td>
                                    <td class="py-2 px-2"><input type="number" step="0.01" min="0" name="bumi_bersama_area_m2" id="bumi_b_area" value="{{ old('bumi_bersama_area_m2', 0) }}" oninput="calcPbb()" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-right text-xs focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-2"><input type="text" name="bumi_bersama_class_code" value="{{ old('bumi_bersama_class_code') }}" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-center text-xs uppercase focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-2"><input type="number" step="1" min="0" name="bumi_bersama_njop_per_m2" id="bumi_b_njop" value="{{ old('bumi_bersama_njop_per_m2', 0) }}" oninput="calcPbb()" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-right text-xs focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-800" id="bumi_b_total">Rp 0</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-3 font-medium text-slate-600">4. Bangunan Bersama</td>
                                    <td class="py-2 px-2"><input type="number" step="0.01" min="0" name="bangunan_bersama_area_m2" id="bng_b_area" value="{{ old('bangunan_bersama_area_m2', 0) }}" oninput="calcPbb()" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-right text-xs focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-2"><input type="text" name="bangunan_bersama_class_code" value="{{ old('bangunan_bersama_class_code') }}" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-center text-xs uppercase focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-2"><input type="number" step="1" min="0" name="bangunan_bersama_njop_per_m2" id="bng_b_njop" value="{{ old('bangunan_bersama_njop_per_m2', 0) }}" oninput="calcPbb()" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300 text-right text-xs focus:ring-1 focus:ring-emerald-500 outline-none"></td>
                                    <td class="py-2 px-3 text-right font-mono font-bold text-slate-800" id="bng_b_total">Rp 0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Ringkasan Perhitungan SPPT -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white p-3.5 rounded-xl border border-slate-200 text-xs">
                        <div>
                            <span class="text-slate-500 block text-[11px] font-semibold">Total NJOP Dasar</span>
                            <span class="font-mono font-bold text-slate-900 text-sm" id="summary_njop_dasar">Rp 0</span>
                        </div>
                        <div>
                            <label class="text-slate-500 block text-[11px] font-semibold">NJOPTKP (Rp)</label>
                            <input type="number" name="njoptkp" id="input_njoptkp" value="{{ old('njoptkp', 10000000) }}" oninput="calcPbb()" class="w-full px-2.5 py-1 mt-0.5 rounded-lg border border-slate-300 font-mono text-xs">
                        </div>
                        <div class="sm:text-right">
                            <span class="text-slate-500 block text-[11px] font-semibold">Estimasi PBB Terutang ({{ $village->tax_rate_percent ?? '0.100' }}%)</span>
                            <span class="font-mono font-bold text-emerald-600 text-base" id="summary_pbb_terutang">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                <a href="{{ route('tax-objects.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-600/20 transition">
                    Simpan Objek Pajak
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleComponentsPanel(checked) {
    const panel = document.getElementById('components_panel');
    const icon = document.getElementById('accordion_icon');
    if (checked) {
        panel.style.display = 'block';
        icon.textContent = '▲ Tutup';
        calcPbb();
    } else {
        panel.style.display = 'none';
        icon.textContent = '▼ Buka Form';
    }
}

function formatRupiah(num) {
    return 'Rp ' + Math.round(num).toLocaleString('id-ID');
}

function calcPbb() {
    const taxRate = {{ (float)($village->tax_rate_percent ?? 0.100) }} / 100;

    const bArea = parseFloat(document.getElementById('bumi_area')?.value) || 0;
    const bNjop = parseFloat(document.getElementById('bumi_njop')?.value) || 0;
    const bTot = bArea * bNjop;
    const elBTot = document.getElementById('bumi_total');
    if (elBTot) elBTot.textContent = formatRupiah(bTot);

    const bgArea = parseFloat(document.getElementById('bng_area')?.value) || 0;
    const bgNjop = parseFloat(document.getElementById('bng_njop')?.value) || 0;
    const bgTot = bgArea * bgNjop;
    const elBgTot = document.getElementById('bng_total');
    if (elBgTot) elBgTot.textContent = formatRupiah(bgTot);

    const bbArea = parseFloat(document.getElementById('bumi_b_area')?.value) || 0;
    const bbNjop = parseFloat(document.getElementById('bumi_b_njop')?.value) || 0;
    const bbTot = bbArea * bbNjop;
    const elBbTot = document.getElementById('bumi_b_total');
    if (elBbTot) elBbTot.textContent = formatRupiah(bbTot);

    const bgbArea = parseFloat(document.getElementById('bng_b_area')?.value) || 0;
    const bgbNjop = parseFloat(document.getElementById('bng_b_njop')?.value) || 0;
    const bgbTot = bgbArea * bgbNjop;
    const elBgbTot = document.getElementById('bng_b_total');
    if (elBgbTot) elBgbTot.textContent = formatRupiah(bgbTot);

    const totalNjopDasar = bTot + bgTot + bbTot + bgbTot;
    const elSummaryDasar = document.getElementById('summary_njop_dasar');
    if (elSummaryDasar) elSummaryDasar.textContent = formatRupiah(totalNjopDasar);

    const njoptkp = parseFloat(document.getElementById('input_njoptkp')?.value) || 0;
    const njopPerhitungan = Math.max(0, totalNjopDasar - njoptkp);
    const pbbTerutang = Math.round(njopPerhitungan * taxRate);
    const elSummaryPbb = document.getElementById('summary_pbb_terutang');
    if (elSummaryPbb) elSummaryPbb.textContent = formatRupiah(pbbTerutang);
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('include_components')?.checked) {
        calcPbb();
    }
});
</script>
@endsection
