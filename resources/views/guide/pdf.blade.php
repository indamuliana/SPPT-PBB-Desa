<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Buku Panduan & SOP SADAYA PBB - {{ $village->name }}</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 10pt;
            line-height: 1.45;
        }
        .header-kop {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header-kop h2 {
            font-size: 13pt;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
            color: #047857;
            letter-spacing: 0.5px;
        }
        .header-kop h1 {
            font-size: 15pt;
            margin: 2px 0;
            text-transform: uppercase;
            font-weight: 800;
            color: #0f172a;
        }
        .header-kop p {
            font-size: 8.5pt;
            margin: 2px 0 0;
            color: #475569;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title h3 {
            font-size: 12pt;
            font-weight: 800;
            text-transform: uppercase;
            margin: 0;
            color: #0f172a;
        }
        .doc-title p {
            font-size: 9pt;
            color: #64748b;
            margin: 3px 0 0;
            font-weight: bold;
        }
        h4.section-title {
            font-size: 10.5pt;
            font-weight: bold;
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 5px 8px;
            border-left: 4px solid #059669;
            margin-top: 18px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        p, li {
            font-size: 9pt;
            color: #334155;
            text-align: justify;
        }
        ol, ul {
            margin-top: 4px;
            margin-bottom: 8px;
            padding-left: 20px;
        }
        li {
            margin-bottom: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 5px 7px;
            vertical-align: top;
        }
        th {
            background-color: #e2e8f0;
            font-weight: bold;
            color: #0f172a;
            text-align: left;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 7.5pt;
            font-weight: bold;
            border-radius: 3px;
            background-color: #e0f2fe;
            color: #0369a1;
        }
        .page-break {
            page-break-before: always;
        }
        .signatures {
            margin-top: 25px;
            width: 100%;
        }
        .signatures td {
            border: none;
            padding: 0;
            width: 50%;
            text-align: center;
            font-size: 9pt;
        }
        .sign-space {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- KOP RESMI DESA -->
    <div class="header-kop">
        <h2>Pemerintah Kabupaten {{ $village->regency ?? 'Sumedang' }}</h2>
        <h1>Pemerintah Desa {{ $village->name }}</h1>
        <p>Kecamatan {{ $village->district ?? 'Tanjungsari' }}, Kode Pos: {{ $village->postal_code ?? '45362' }} &bull; Alamat: {{ $village->address ?? 'Kantor Desa' }}</p>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title">
        <h3>PEDOMAN OPERASIONAL STANDAR (SOP) & PANDUAN PENGGUNA<br>SISTEM INFORMASI SADAYA PBB DESA {{ strtoupper($village->name) }}</h3>
        <p>Tahun Pajak Ketetapan: {{ $village->active_tax_year }} &bull; Nomor Dokumen: SOP/PBB/{{ $village->active_tax_year }}/01</p>
    </div>

    <!-- BAGIAN 1: GAMBARAN UMUM -->
    <h4 class="section-title">Bagian I: Gambaran Umum & Prinsip Tata Kelola</h4>
    <p>
        Sistem Administrasi Data dan Pelayanan Pajak PBB Desa (<strong>SADAYA PBB</strong>) dirancang sebagai instrumen digital terpadu dalam pengelolaan Pajak Bumi dan Bangunan Perdesaan dan Perkotaan (PBB-P2) di tingkat desa. Sistem ini menjamin 3 (tiga) pilar utama tata kelola keuangan desa:
    </p>
    <ol>
        <li><strong>Transparansi & Akuntabilitas Penuh:</strong> Setiap uang tunai yang disetorkan warga tercatat secara <em>real-time</em> dan divalidasi dengan Kuitansi Resmi ber-QR Code yang dapat diverifikasi publik melalui pemindaian smartphone.</li>
        <li><strong>Pemisahan Fungsi (Segregation of Duties):</strong> Petugas loket kasir tidak dapat memanipulasi buku kas; kolektor tidak dapat mengubah tagihan SPPT; dan admin desa bertindak sebagai validator (approval) tanpa memegang fisik uang kas harian.</li>
        <li><strong>Perlindungan Integritas Saldo (Zero Cash Leakage):</strong> Saldo berjalan kasir, saldo fisik di tangan kolektor, dan saldo setoran di Bank BJB terikat dalam rekonsiliasi otomatis. Pembatalan kuitansi wajib melalui persetujuan berjenjang.</li>
    </ol>

    <!-- BAGIAN 2: ALUR KERJA TERPADU -->
    <h4 class="section-title">Bagian II: Alur Kerja Terpadu (End-to-End Workflow)</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Tahapan</th>
                <th style="width: 25%;">Pelaku (Role)</th>
                <th style="width: 35%;">Uraian Alur & Tindakan</th>
                <th style="width: 25%;">Output / Bukti Sah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Tahap 1</strong></td>
                <td>Admin Desa</td>
                <td>Menerima DHKP resmi dari Bapenda Kabupaten, lalu mengunggah berkas template Excel/CSV ke dalam sistem.</td>
                <td>Master Objek Pajak & SPPT Terbit</td>
            </tr>
            <tr>
                <td><strong>Tahap 2</strong></td>
                <td>Kadus / Kasir Loket</td>
                <td>Mencari NOP Wajib Pajak di loket POS, menerima uang kas (Lunas/Cicilan), lalu memproses pembayaran.</td>
                <td>Kuitansi Sah ber-QR Code (A4 / Thermal)</td>
            </tr>
            <tr>
                <td><strong>Tahap 3</strong></td>
                <td>Kasir & Kolektor</td>
                <td>Kasir menyerahkan uang kas fisik harian kepada Kolektor. Kolektor menghitung fisik uang dan menekan 'Konfirmasi Terima'.</td>
                <td>Berita Acara Serah Terima Kas</td>
            </tr>
            <tr>
                <td><strong>Tahap 4</strong></td>
                <td>Kolektor Desa</td>
                <td>Memantau Buku Kas Umum (Cashbook Ledger real-time), memastikan saldo kasir yang disetor sama dengan uang di tangan.</td>
                <td>Buku Kas Harian Terkonsiliasi</td>
            </tr>
            <tr>
                <td><strong>Tahap 5</strong></td>
                <td>Kolektor & Admin</td>
                <td>Kolektor menyetorkan uang ke Rekening Kas Desa di Bank BJB. Kolektor upload bukti setor bank, Admin memverifikasi & Approve.</td>
                <td>Slip Bukti Setor Bank Sah</td>
            </tr>
            <tr>
                <td><strong>Tahap 6</strong></td>
                <td>Bendahara & Kades</td>
                <td>Bendahara merekonsiliasi kas masuk; Kepala Desa memantau capaian target desa (Rp {{ number_format($village->target_amount, 0, ',', '.') }}) di Dashboard.</td>
                <td>Laporan Realisasi & LPJ Desa</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- BAGIAN 3: MATRIKS TUGAS PER ROLE -->
    <h4 class="section-title">Bagian III: Matriks Peran, Wewenang, dan Fungsi Role</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Peran (Role)</th>
                <th style="width: 45%;">Tugas Utama & Wewenang</th>
                <th style="width: 35%;">Batasan / Larangan Sistem</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>1. Admin Desa</strong></td>
                <td>
                    &bull; Pengelolaan Master Wajib Pajak & Objek Pajak.<br>
                    &bull; Import DHKP Kabupaten & Perekaman Terpadu.<br>
                    &bull; Pengaturan Dusun, Profil Desa, & Akun Petugas.<br>
                    &bull; Approval Pembatalan Kuitansi Kasir.<br>
                    &bull; Approval Slip Setoran Bank Kolektor.<br>
                    &bull; Pengawasan Rekam Jejak (Audit Log).
                </td>
                <td>
                    Tidak mengelola fisik uang kasir sehari-hari untuk menjaga objektivitas fungsi verifikator.
                </td>
            </tr>
            <tr>
                <td><strong>2. Kadus / Kasir Loket</strong></td>
                <td>
                    &bull; Pelayanan Kasir POS (Pencarian NOP/Nama).<br>
                    &bull; Penerimaan Pembayaran Tunai (Lunas / Cicilan).<br>
                    &bull; Cetak Kuitansi Resmi QR (A4 & Thermal).<br>
                    &bull; Pengajuan Pembatalan Kuitansi salah input.<br>
                    &bull; Pelaporan Kasus TO (Tanpa Objek).<br>
                    &bull; Usulan Berita Acara Mutasi & Balik Nama.<br>
                    &bull; Penyerahan Kas Fisik ke Kolektor.
                </td>
                <td>
                    Tidak dapat membatalkan kuitansi secara sepihak (wajib approval Admin). Tidak dapat mengubah nominal tagihan SPPT.
                </td>
            </tr>
            <tr>
                <td><strong>3. Kolektor Desa</strong></td>
                <td>
                    &bull; Penerimaan & Konfirmasi Kas dari seluruh Kasir.<br>
                    &bull; Pengelolaan Buku Kas Umum (Cashbook Ledger).<br>
                    &bull; Rekonsiliasi Kas Harian.<br>
                    &bull; Investigasi Lapangan Kasus TO.<br>
                    &bull; Penyetoran Uang Pajak ke Bank BJB / Kasda.<br>
                    &bull; Pengunggahan Berkas Bukti Setor Bank.
                </td>
                <td>
                    Tidak dapat melakukan approval atas setoran bank yang dibuatnya sendiri (wajib approval Admin Desa).
                </td>
            </tr>
            <tr>
                <td><strong>4. Bendahara Desa</strong></td>
                <td>
                    &bull; Pengawasan Kas Masuk & Kas Terdistribusi.<br>
                    &bull; Monitoring Saldo Bank vs Saldo Buku Kas.<br>
                    &bull; Analisis Laporan Realisasi per Dusun.<br>
                    &bull; Analisis Laporan Kuitansi & Arus Kas (Cash Flow).<br>
                    &bull; Pengawasan Kepatuhan Finansial Desa.
                </td>
                <td>
                    Fokus pada fungsi pengawasan, rekonsiliasi, dan pelaporan keuangan berkala kepada Kepala Desa.
                </td>
            </tr>
            <tr>
                <td><strong>5. Kepala Desa</strong></td>
                <td>
                    &bull; Pemantauan Executive Dashboard Real-Time.<br>
                    &bull; Evaluasi Capaian Target APBDes (Rp {{ number_format($village->target_amount, 0, ',', '.') }}).<br>
                    &bull; Penilaian Performa Penagihan per Wilayah Dusun.<br>
                    &bull; Pengambilan Kebijakan Operasional Penagihan.<br>
                    &bull; Pengawasan Transparansi Pelayanan Publik.
                </td>
                <td>
                    Memiliki hak akses eksekutif menyeluruh untuk memantau data tanpa dibebani tugas teknis loket kasir.
                </td>
            </tr>
        </tbody>
    </table>

    <!-- BAGIAN 4: STANDAR OPERASIONAL PROSEDUR KASIR & SANKSI -->
    <h4 class="section-title">Bagian IV: Prosedur Pengamanan Kasir & Kuitansi</h4>
    <ol>
        <li><strong>Pemberian Kuitansi Wajib:</strong> Petugas kasir WAJIB mencetak dan memberikan kuitansi ber-QR Code kepada warga sesaat setelah pembayaran diterima. Dilarang keras menerima uang tanpa menerbitkan kuitansi sistem.</li>
        <li><strong>Penanganan Salah Input:</strong> Jika kasir salah mengetik nominal, kasir segera membuka menu <em>Riwayat Kuitansi</em>, klik <em>Ajukan Pembatalan</em> dengan menyertakan alasan. Admin desa akan memverifikasi dan menyetujui pembatalan tersebut sehingga tagihan warga pulih kembali.</li>
        <li><strong>Penyerahan Kas Harian:</strong> Kasir tidak diperkenankan menyimpan uang kas hasil pungutan lebih dari 1x24 jam tanpa membuat Berita Acara Serah Terima Kas kepada Kolektor Desa.</li>
    </ol>

    <!-- BAGIAN 5: KETENTUAN MUTASI & KASUS TO -->
    <h4 class="section-title">Bagian V: Ketentuan Mutasi & Kasus Tanpa Objek (TO)</h4>
    <p>
        <strong>1. Batasan Kewenangan Mutasi di Tingkat Desa:</strong><br>
        Sesuai regulasi perpajakan daerah, wewenang mutasi formal dan penerbitan SPPT baru berada sepenuhnya pada Bapenda Kabupaten. Modul Mutasi & Balik Nama di SADAYA PBB berfungsi sebagai pencatatan berita acara peralihan hak (jual-beli, waris, hibah) dan bahan laporan resmi desa ke Bapenda untuk penyesuaian SPPT tahun pajak berikutnya.
    </p>
    <p>
        <strong>2. Penanganan Kasus TO (Tanpa Objek):</strong><br>
        Objek pajak yang tanahnya tidak ditemukan, fiktif, atau dalam sengketa kepemilikan wajib dilaporkan sebagai Kasus TO. Sistem secara otomatis mengunci NOP tersebut agar tidak bisa dibayar di kasir sampai proses investigasi lapangan oleh Kolektor selesai.
    </p>

    <!-- PENGESAHAN RESMI -->
    <table class="signatures">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Kepala Desa {{ $village->name }}</strong>
                <div class="sign-space"></div>
                <strong>{{ $village->kades_name ?? 'ABDUL HAYAT' }}</strong><br>
                <span>NIP. {{ $village->kades_nip ?? '-' }}</span>
            </td>
            <td>
                Disusun & Disahkan Oleh,<br>
                <strong>Bendahara / Admin PBB Desa</strong>
                <div class="sign-space"></div>
                <strong>TIM PENGELOLA PBB DESA</strong><br>
                <span>Desa {{ $village->name }}</span>
            </td>
        </tr>
    </table>

</body>
</html>
