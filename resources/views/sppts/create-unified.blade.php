@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="spptCalculator({{ $taxRate }})">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sppts.index') }}" class="p-1 rounded-lg hover:bg-slate-200 text-slate-500 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Perekaman SPPT Terpadu</h1>
            </div>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Formulir lengkap perekaman Wajib Pajak, Letak NOP, 4 Komponen Objek, dan Perhitungan Ketetapan SPPT.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-semibold border border-emerald-300">
                Tarif Desa: {{ number_format($taxRate, 3) }}%
            </span>
        </div>
    </div>

    <!-- Alert Form Validation -->
    @if ($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
        <p class="font-bold mb-1">Terdapat kesalahan pada input:</p>
        <ul class="list-disc list-inside text-xs space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('sppts.store-unified') }}" class="space-y-6">
        @csrf

        <!-- 1. Identitas Wajib Pajak -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs">1</span>
                    Identitas Wajib Pajak (WP)
                </h2>
                <div class="flex items-center gap-4 text-xs font-medium">
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="taxpayer_mode" value="existing" x-model="taxpayerMode" class="text-emerald-600 focus:ring-emerald-500">
                        <span>Pilih WP yang Ada</span>
                    </label>
                    <label class="inline-flex items-center gap-1.5 cursor-pointer">
                        <input type="radio" name="taxpayer_mode" value="new" x-model="taxpayerMode" class="text-emerald-600 focus:ring-emerald-500">
                        <span>Daftarkan WP Baru</span>
                    </label>
                </div>
            </div>

            <!-- Mode Existing WP -->
            <div x-show="taxpayerMode === 'existing'" class="space-y-3">
                <label class="block text-xs font-semibold text-slate-700">Pilih Wajib Pajak Terdaftar <span class="text-rose-500">*</span></label>
                <select name="taxpayer_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="">-- Pilih Wajib Pajak --</option>
                    @foreach($taxpayers as $tp)
                        <option value="{{ $tp->id }}" {{ old('taxpayer_id') == $tp->id ? 'selected' : '' }}>
                            {{ $tp->name }} (NIK: {{ $tp->nik ?? '-' }}) - {{ $tp->address ?? 'Dusun ' . $tp->dusun }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400">Pilih dari basis data WP yang telah tersimpan di desa.</p>
            </div>

            <!-- Mode New WP -->
            <div x-show="taxpayerMode === 'new'" class="grid grid-cols-1 sm:grid-cols-3 gap-4" style="display: none;">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap WP <span class="text-rose-500">*</span></label>
                    <input type="text" name="new_taxpayer_name" value="{{ old('new_taxpayer_name') }}" placeholder="Contoh: INDA MULIANA" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NIK (16 Digit)</label>
                    <input type="text" name="new_taxpayer_nik" value="{{ old('new_taxpayer_nik') }}" maxlength="16" placeholder="320501..." class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Wajib Pajak</label>
                    <input type="text" name="new_taxpayer_address" value="{{ old('new_taxpayer_address') }}" placeholder="JL. RAYA UTAMA NO. 45" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">RT / RW</label>
                        <div class="flex items-center gap-1">
                            <input type="text" name="new_taxpayer_rt" value="{{ old('new_taxpayer_rt', '003') }}" placeholder="RT" class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-sm text-center">
                            <span>/</span>
                            <input type="text" name="new_taxpayer_rw" value="{{ old('new_taxpayer_rw', '005') }}" placeholder="RW" class="w-full px-2.5 py-2 rounded-xl border border-slate-300 text-sm text-center">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">No. HP / WA</label>
                        <input type="text" name="new_taxpayer_phone" value="{{ old('new_taxpayer_phone') }}" placeholder="08..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Letak Objek Pajak & NOP -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs">2</span>
                Letak Objek Pajak (NOP)
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Objek Pajak (NOP 18 Digit) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nop" value="{{ old('nop') }}" required placeholder="Contoh: 32.05.010.001.002-0034.0" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-mono font-bold tracking-wide focus:ring-2 focus:ring-emerald-500 outline-none">
                    <p class="text-[11px] text-slate-400 mt-1">Format resmi SPPT. Boleh diketik tanpa titik/strip (akan distandarisasi otomatis).</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dusun / Wilayah</label>
                    <select name="object_dusun_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
                        <option value="">-- Pilih Dusun --</option>
                        @foreach($dusuns as $ds)
                            <option value="{{ $ds->id }}" {{ old('object_dusun_id') == $ds->id ? 'selected' : '' }}>
                                {{ $ds->name }} {{ $ds->code ? '(' . $ds->code . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Letak Objek Pajak</label>
                    <input type="text" name="object_address" value="{{ old('object_address') }}" placeholder="JL. RAYA UTAMA NO. 45 APARTEMEN LT. 05 NO. 12" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">RT / RW Letak Objek</label>
                    <div class="flex items-center gap-1">
                        <input type="text" name="object_rt" value="{{ old('object_rt', '003') }}" placeholder="RT" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm text-center">
                        <span>/</span>
                        <input type="text" name="object_rw" value="{{ old('object_rw', '005') }}" placeholder="RW" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm text-center">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Rincian Objek Pajak (4 Kategori Sesuai sppt.html) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 overflow-x-auto">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs">3</span>
                Rincian Objek Pajak (4 Komponen Standar SPPT)
            </h2>
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-y border-slate-200 text-slate-700 font-semibold uppercase tracking-wider">
                        <th class="py-2.5 px-3">Objek</th>
                        <th class="py-2.5 px-3 w-32">Luas (M²)</th>
                        <th class="py-2.5 px-3 w-28 text-center">Kelas</th>
                        <th class="py-2.5 px-3 w-40 text-right">NJOP per M² (Rp)</th>
                        <th class="py-2.5 px-3 w-44 text-right">Total NJOP (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <!-- Bumi -->
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-slate-800">BUMI</td>
                        <td class="py-2 px-2">
                            <input type="number" step="0.01" min="0" x-model.number="components.bumi.area" name="bumi_area_m2" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-right">
                        </td>
                        <td class="py-2 px-2">
                            <input type="text" name="bumi_class_code" value="{{ old('bumi_class_code', 'A35') }}" placeholder="A35" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-center uppercase">
                        </td>
                        <td class="py-2 px-2">
                            <input type="number" step="1" min="0" x-model.number="components.bumi.njop" name="bumi_njop_per_m2" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-right font-mono">
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800" x-text="formatRupiah(components.bumi.area * components.bumi.njop)">
                            0
                        </td>
                    </tr>

                    <!-- Bangunan -->
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-slate-800">BANGUNAN</td>
                        <td class="py-2 px-2">
                            <input type="number" step="0.01" min="0" x-model.number="components.bangunan.area" name="bangunan_area_m2" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-right">
                        </td>
                        <td class="py-2 px-2">
                            <input type="text" name="bangunan_class_code" value="{{ old('bangunan_class_code', 'B20') }}" placeholder="B20" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-center uppercase">
                        </td>
                        <td class="py-2 px-2">
                            <input type="number" step="1" min="0" x-model.number="components.bangunan.njop" name="bangunan_njop_per_m2" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-right font-mono">
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800" x-text="formatRupiah(components.bangunan.area * components.bangunan.njop)">
                            0
                        </td>
                    </tr>

                    <!-- Bumi Bersama -->
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-slate-800">BUMI BERSAMA</td>
                        <td class="py-2 px-2">
                            <input type="number" step="0.01" min="0" x-model.number="components.bumi_bersama.area" name="bumi_bersama_area_m2" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-right">
                        </td>
                        <td class="py-2 px-2">
                            <input type="text" name="bumi_bersama_class_code" value="{{ old('bumi_bersama_class_code', 'A35') }}" placeholder="A35" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-center uppercase">
                        </td>
                        <td class="py-2 px-2">
                            <input type="number" step="1" min="0" x-model.number="components.bumi_bersama.njop" name="bumi_bersama_njop_per_m2" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-right font-mono">
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800" x-text="formatRupiah(components.bumi_bersama.area * components.bumi_bersama.njop)">
                            0
                        </td>
                    </tr>

                    <!-- Bang Bersama -->
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-slate-800">BANG BERSAMA</td>
                        <td class="py-2 px-2">
                            <input type="number" step="0.01" min="0" x-model.number="components.bangunan_bersama.area" name="bangunan_bersama_area_m2" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-right">
                        </td>
                        <td class="py-2 px-2">
                            <input type="text" name="bangunan_bersama_class_code" value="{{ old('bangunan_bersama_class_code', 'B20') }}" placeholder="B20" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-center uppercase">
                        </td>
                        <td class="py-2 px-2">
                            <input type="number" step="1" min="0" x-model.number="components.bangunan_bersama.njop" name="bangunan_bersama_njop_per_m2" class="w-full px-2 py-1.5 rounded-lg border border-slate-300 text-right font-mono">
                        </td>
                        <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800" x-text="formatRupiah(components.bangunan_bersama.area * components.bangunan_bersama.njop)">
                            0
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 4. Perhitungan Ketetapan SPPT -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs">4</span>
                Perhitungan PBB Terutang & Ketetapan SPPT
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Parameter SPPT -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tahun Pajak <span class="text-rose-500">*</span></label>
                        <input type="number" name="tax_year" value="{{ old('tax_year', $defaultYear) }}" required class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-bold text-emerald-700">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Jatuh Tempo</label>
                        <input type="date" name="due_date" value="{{ old('due_date', $defaultYear . '-09-30') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NJOPTKP (Rp)</label>
                        <input type="number" step="1" min="0" x-model.number="njoptkp" name="njoptkp" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-sm font-mono">
                        <p class="text-[11px] text-slate-400 mt-1">Standar penetapan daerah: Rp 10.000.000</p>
                    </div>
                </div>

                <!-- Kalkulasi Otomatis -->
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-5 space-y-3 text-xs">
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Total Nilai Jual Objek Pajak (NJOP):</span>
                        <span class="font-mono font-bold text-slate-900" x-text="'Rp ' + formatRupiah(totalNjop())">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>NJOPTKP:</span>
                        <span class="font-mono font-bold text-slate-700" x-text="'Rp ' + formatRupiah(njoptkp)">Rp 10.000.000</span>
                    </div>
                    <div class="h-px bg-slate-200"></div>
                    <div class="flex justify-between items-center text-slate-700 font-medium">
                        <span>NJOP untuk Penghitungan Pajak:</span>
                        <span class="font-mono font-bold text-slate-900" x-text="'Rp ' + formatRupiah(njopPerhitungan())">Rp 0</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Tarif Pajak PBB-P2 (<span x-text="taxRate + '%'">0.1%</span>):</span>
                        <span class="font-mono font-bold text-slate-800" x-text="'Rp ' + formatRupiah(pbbTerutang())">Rp 0</span>
                    </div>
                    <div class="h-0.5 bg-slate-300"></div>
                    <div class="flex justify-between items-center text-emerald-800 font-bold text-sm pt-1">
                        <span>PAJAK BUMI DAN BANGUNAN TERUTANG:</span>
                        <span class="font-mono text-base text-emerald-700" x-text="'Rp ' + formatRupiah(pbbTerutang())">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Simpan -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('sppts.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-md shadow-emerald-600/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Perekaman SPPT Terpadu</span>
            </button>
        </div>
    </form>
</div>

<script>
function spptCalculator(taxRatePercent) {
    return {
        taxpayerMode: '{{ old('taxpayer_mode', 'existing') }}',
        taxRate: taxRatePercent || 0.1,
        njoptkp: {{ old('njoptkp', 10000000) }},
        components: {
            bumi: {
                area: {{ old('bumi_area_m2', 45) }},
                njop: {{ old('bumi_njop_per_m2', 1200000) }},
            },
            bangunan: {
                area: {{ old('bangunan_area_m2', 36) }},
                njop: {{ old('bangunan_njop_per_m2', 2500000) }},
            },
            bumi_bersama: {
                area: {{ old('bumi_bersama_area_m2', 12) }},
                njop: {{ old('bumi_bersama_njop_per_m2', 1200000) }},
            },
            bangunan_bersama: {
                area: {{ old('bangunan_bersama_area_m2', 18) }},
                njop: {{ old('bangunan_bersama_njop_per_m2', 2500000) }},
            }
        },
        totalNjop() {
            let total = 0;
            for (const key in this.components) {
                total += (this.components[key].area || 0) * (this.components[key].njop || 0);
            }
            return total;
        },
        njopPerhitungan() {
            return Math.max(0, this.totalNjop() - (this.njoptkp || 0));
        },
        pbbTerutang() {
            return Math.round(this.njopPerhitungan() * (this.taxRate / 100));
        },
        formatRupiah(num) {
            return Math.round(num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endsection
