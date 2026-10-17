# SADAYA PBB (Sistem Administrasi Data dan Pelayanan Pajak PBB Desa)

Aplikasi web administrasi dan pengelolaan Pajak Bumi dan Bangunan Perdesaan dan Perkotaan (PBB-P2) tingkat desa. Membantu pemerintah desa dalam pendataan wajib pajak, objek pajak, SPPT, pencatatan pembayaran tunai/cicilan, serah terima kas kasir-kolektor, pencatatan setoran ke bank/Bapenda, mutasi/pemecahan objek, serta pelaporan keuangan real-time.

---

## Fitur Utama

1. **Dashboard & Monitoring Eksekutif**
   - Realisasi penerimaan PBB (target vs capaian, persentase).
   - Kas di tangan kolektor, belum disetor, dan tunggakan per dusun/RW.
   - Grafik capaian bulanan dan peringkat realisasi wilayah.

2. **Master Data & SPPT Terpadu**
   - Pendataan Wajib Pajak (NIK, nama, alamat, kontak).
   - Pendataan Objek Pajak (NOP 18 digit berformat resmi) dengan rincian komponen multi-objek:
     - Bumi
     - Bangunan
     - Bumi Bersama
     - Bangunan Bersama
   - Fitur Input Terpadu (Single-Page Form WP + Objek + Komponen + SPPT).
   - Pengaturan tarif pajak dinamis per desa (persentase) & NJOPTKP.

3. **Pemecahan & Mutasi Objek Pajak**
   - Pemecahan komponen objek (misal bumi/bangunan dijual sebagian ke WP lain).
   - Alur pengajuan dan persetujuan mutasi oleh Admin/Kepala Desa.
   - Penanganan SPPT Tanpa Objek (TO) dengan berita acara investigasi & kunci transaksi sementara.

4. **Loket Kasir POS & Penerimaan PBB**
   - Split-screen POS dengan tabel pencarian SPPT interaktif (cari NOP, nama WP, dusun, status).
   - Pembayaran lunas atau cicilan bertahap.
   - Kuitansi pembayaran resmi (format A4 & Thermal 58mm/80mm) dilengkapi QR Code verifikasi publik anti-pemalsuan.

5. **Arus Kas & Rekonsiliasi 3 Tingkat**
   - **Tingkat 1 (Kasir/Kadus):** Pengajuan serah terima kas (`handover`) dengan dukungan konfirmasi massal (bulk confirmation).
   - **Tingkat 2 (Kolektor Desa):** Penerimaan fisik uang, verifikasi selisih kas, dan pencatatan setoran ke Bank/Bapenda.
   - **Tingkat 3 (Buku Kas Umum / Cashbook):** Ledger kronologis mutasi uang masuk (dari WP) vs uang keluar (setor ke Bank) serta saldo berjalan (*running balance*).

6. **Pusat Laporan & Ekspor**
   - Laporan Realisasi per Dusun.
   - Laporan Penerimaan Kasir/Kadus.
   - Laporan Arus Kas & Rekonsiliasi.
   - Laporan Mutasi & Kasus TO.
   - Dukungan cetak printer dan ekspor CSV/Excel.

---

## Teknologi yang Digunakan

- **Backend:** PHP 8.2+, Laravel 11 / 12
- **Database:** MySQL
- **Frontend:** Blade, Tailwind CSS v4, Alpine.js, Chart.js
- **Testing:** PHPUnit (Feature & Unit Tests)

---

## Panduan Instalasi Lokal

1. **Clone repositori:**
   ```bash
   git clone https://github.com/indamuliana/SPPT-PBB-Desa.git
   cd SPPT-PBB-Desa
   ```

2. **Install dependensi PHP & Node:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Sesuaikan kredensial database di berkas `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sadaya_pbb
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Migrasi Database & Seeder:**
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```

6. **Akses Pengguna Bawaan:**
   - **Admin:** `admin@desa.id` / `password`
   - **Kolektor:** `kolektor@desa.id` / `password`
   - **Kasir/Kadus 1:** `kasir1@desa.id` / `password`
   - **Kepala Desa:** `kades@desa.id` / `password`

---

## Menjalankan Pengujian (Testing)

```bash
php artisan test
```

---

## Lisensi

Aplikasi ini dilisensikan di bawah lisensi terbuka [MIT](LICENSE).
