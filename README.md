# PKLku - Platform Manajemen & Monitoring Praktek Kerja Lapangan (PKL)

**PKLku** adalah aplikasi web modern berbasis framework Laravel 11 yang dirancang khusus untuk mempermudah tata kelola, pemantauan, presensi geofence, jurnal harian, dan penilaian pelaksanaan Praktek Kerja Lapangan (PKL) bagi Sekolah Menengah Kejuruan (SMK). Aplikasi ini mengintegrasikan seluruh pemangku kepentingan: **Administrator Sekolah**, **Guru Pembimbing**, **Pembimbing Industri (Mitra DUDI)**, dan **Siswa (Murid)** ke dalam satu platform digital terpadu yang efisien, aman, dan transparan.

---

## 📋 Daftar Isi
1. [Pendahuluan](#1-pendahuluan)
2. [Fitur Utama](#2-fitur-utama)
3. [Role & Hak Akses Pengguna](#3-role--hak-akses-pengguna)
4. [Arsitektur Sistem & Struktur Modul](#4-arsitektur-sistem--struktur-modul)
5. [Teknologi yang Digunakan](#5-teknologi-yang-digunakan)
6. [Persyaratan Sistem](#6-persyaratan-sistem)
7. [Panduan Instalasi Lokal](#7-panduan-instalasi-lokal)
8. [Setup Database & Seeding](#8-setup-database--seeding)
9. [Panduan Deployment (cPanel / VPS)](#9-panduan-deployment-cpanel--vps)
10. [Konfigurasi Cron Job & Otomatisasi](#10-konfigurasi-cron-job--otomatisasi)
11. [Logika Presensi Geofence & Hari Libur](#11-logika-presensi-geofence--hari-libur)
12. [Changelog](#12-changelog)
13. [Kontributor & Lisensi](#13-kontributor--lisensi)

---

## 1. Pendahuluan
Pelaksanaan PKL di SMK sering menghadapi kendala klasik:
- Absensi manual yang rawan manipulasi atau titip absen.
- Pemantauan guru pembimbing yang terbatas jarak dan waktu.
- Jurnal kegiatan fisik yang rentan hilang atau rusak.
- Perhitungan kehadiran raport yang rumit karena harus memotong hari libur nasional dan cuti bersama secara manual.

**PKLku** hadir mendigitalkan seluruh siklus PKL dengan validasi GPS & kamera selfie, pemetaan sebaran DUDI secara visual, kalkulasi kehadiran otomatis, hingga cetak lembar penilaian rapor PDF resmi berstandar kurikulum SMK.

---

## 2. Fitur Utama

### 📍 A. Presensi Geofence & Foto Selfie Real-Time
* **Validasi Lokasi 2 Lapis (Haversine Formula)**: Presensi divalidasi berdasarkan titik GPS DUDI dengan radius yang dapat disesuaikan per industri atau menggunakan radius default global sekolah.
* **Model Kerja Fleksibel (Hybrid WFO-WFA)** *(Terbaru)*: Mendukung penempatan siswa dengan model kerja `100% WFO`, `100% WFA (Full Remote)`, maupun `Hybrid (Kombinasi Hari Tertentu)`. Pada hari WFA siswa, validasi radius geofence dibebaskan (*bypassed*) secara otomatis sementara foto selfie & koordinat GPS tetap tercatat.
* **Foto Selfie Anti-Kecurangan**: Pengambilan foto langsung dari kamera perangkat dengan watermarking waktu, nama, koordinat GPS, status model kerja (`WFO`/`WFA`), dan kompresi otomatis.
* **Deteksi Keterlambatan**: Status otomatis *Tepat Waktu*, *Terlambat*, atau *Pulang Cepat* berdasarkan konfigurasi jam kerja sekolah/DUDI.

### 📅 B. Manajemen Hari Libur & Tanggal Merah Nasional *(Terbaru)*
* **Sinkronisasi 1-Klik**: Tombol auto-sync untuk mengisi seluruh hari libur nasional resmi Indonesia (Tahun Baru, Idul Fitri, HUT RI, Nyepi, Natal, dll.).
* **Integrasi Presensi**: Banner libur otomatis di halaman siswa dan dashboard. Tombol presensi otomatis terkunci saat hari libur.
* **Cron Auto-Absent Aman**: Cron job pencatat ketidakhadiran (Alpha) otomatis melewati hari libur terdaftar sehingga **tidak ada siswa yang terkena Alpha palsu**.
* **Koreksi Hari Efektif Rapor**: Hari libur nasional yang jatuh pada hari kerja otomatis dipotong dari kalkulasi total hari kerja di rapor nilai PDF dan ekspor Excel.

### 📖 C. Jurnal Kegiatan & Pengajuan Izin/Sakit
* **Jurnal Harian**: Input ringkasan kegiatan harian dan unggah foto dokumentasi kerja dengan sistem verifikasi/catatan dari pembimbing industri dan guru.
* **Izin / Sakit Online**: Pengajuan izin multi-hari disertai unggahan surat keterangan dokter/orang tua (PDF/JPG) dengan alur persetujuan (*approval*) guru pembimbing.

### 🗺️ D. Pemetaan & Monitoring DUDI
* **Peta Interaktif (Leaflet.js)**: Visualisasi sebaran siswa di seluruh mitra industri di Indonesia secara interaktif.
* **Daftar Mitra DUDI Urut Abjad (A–Z)**: Panel ringkasan DUDI aktif dan daftar siswa bimbingan yang otomatis berurutan secara alfabetis.
* **Lembar Kunjungan Monitoring PDF**: Pencatatan riwayat kunjungan guru pembimbing ke DUDI beserta unduh berkas berita acara kunjungan dalam format PDF resmi.

### 📊 E. Penilaian PKL & Cetak Rapor PDF Resmi
* **Penilaian Kolaboratif**: Penilaian aspek sekolah (oleh Guru) dan aspek teknis industri (oleh Pembimbing DUDI).
* **Catatan Tujuan Pembelajaran (TP)**: Evaluasi capaian kompetensi per Tujuan Pembelajaran dengan penggabungan baris (*rowspan*) yang rapi pada lembar rapor.
* **Rapor 1-Halaman A4 Presisi**: Ekspor PDF rapor nilai siswa yang rapi menggunakan Dompdf dengan rekapitulasi kehadiran (Hadir, Sakit, Izin, Alfa).
* **Impor & Ekspor Excel**: Manajemen data master dan nilai menggunakan template Excel yang terstandarisasi.

---

## 3. Role & Hak Akses Pengguna

| Fitur / Menu | Admin Sekolah | Guru Pembimbing | Pembimbing Industri | Siswa (Murid) |
| :--- | :---: | :---: | :---: | :---: |
| **Dashboard Statistik & Peta DUDI** | ✅ | ✅ (Khusus Bimbingan) | ✅ | ✅ (Ringkasan Mandiri) |
| **Master Data (TA, Jurusan, Kelas, Siswa, Guru, DUDI)** | ✅ | ❌ | ❌ | ❌ |
| **Plotting Penempatan Siswa** | ✅ | ❌ | ❌ | ❌ |
| **Presensi Mandiri (Check-in / Check-out)** | ❌ | ❌ | ❌ | ✅ |
| **Monitoring & Rekap Presensi** | ✅ | ✅ | ✅ | ✅ (Riwayat Sendiri) |
| **Verifikasi Izin / Sakit** | ✅ | ✅ | ❌ | ❌ (Hanya Pengajuan) |
| **Review / Approval Jurnal** | ✅ | ✅ | ✅ | ❌ (Hanya Menulis) |
| **Input Nilai Aspek Sekolah / Industri** | ✅ | ✅ | ✅ | ❌ |
| **Unduh Rapor PDF & Sertifikat** | ✅ | ✅ | ❌ | ✅ |
| **Hari Libur Nasional & Setting Sekolah** | ✅ | ❌ | ❌ | ❌ |

---

## 4. Arsitektur Sistem & Struktur Modul

PKLku mengadopsi pola **Modular Monolith Architecture** berbasis Laravel, di mana setiap domain bisnis diisolasi ke dalam direktori independen:

```
app/
├── Modules/
│   ├── Auth/           # Autentikasi, login multi-guard, reset password
│   ├── Dashboard/      # Statistik agregat, peta lokasi DUDI aktif
│   ├── MasterData/     # Tahun Ajaran, Hari Libur, Jurusan, Kelas, Siswa, Guru, DUDI
│   ├── PKL/            # Plotting Penempatan & Lembar Kunjungan Monitoring
│   ├── Presensi/       # Presensi Geofence, selfie watermarking, Izin/Sakit
│   ├── Jurnal/         # Jurnal kegiatan harian dan persetujuan
│   ├── Penilaian/      # TP, Indikator, input nilai, impor nilai Excel
│   ├── Laporan/        # Rekap kehadiran, ekspor Excel, cetak rapor PDF
│   └── Setting/        # Parameter sekolah, kop surat, jam kerja, radius default
├── Console/Commands/   # Cron auto-absent harian (MarkAbsentStudents)
├── Providers/          # ModuleServiceProvider & registrasi sistem
```

---

## 5. Teknologi yang Digunakan

* **Backend**: PHP >= 8.2, Laravel 11.x
* **Database**: MySQL 5.7+ / MariaDB 10.3+
* **Frontend**: Bootstrap 5, Vanilla Modern CSS, Alpine.js (State management dinamis)
* **Peta & GIS**: Leaflet.js & OpenStreetMap
* **Laporan & Dokumen**: Dompdf (Barryvdh Dompdf) & PhpSpreadsheet (Maatwebsite Excel)
* **Asset Bundler**: Vite

---

## 6. Persyaratan Sistem

* PHP `>= 8.2` dengan ekstensi: `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `zip`, `xml`, `curl`
* Composer `>= 2.0`
* Node.js `>= 18.0` & NPM
* Web Server: Apache / Nginx / LiteSpeed (cPanel)

---

## 7. Panduan Instalasi Lokal

1. **Clone repositori**:
   ```bash
   git clone https://github.com/luphihart/pklku.git
   cd pklku
   ```

2. **Instal dependensi backend & frontend**:
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi Environment**:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Database**:
   Buka file `.env` dan sesuaikan koneksi database Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pklku_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Migrasi & Seed Data Awal**:
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

6. **Jalankan Aplikasi**:
   ```bash
   npm run build
   php artisan serve
   ```
   Akses aplikasi di browser: `http://localhost:8000`.

---

## 8. Setup Database & Seeding

Untuk inisialisasi cepat akun dan data pengujian:
* **Admin Default**: `admin` / Password: `password`
* **Seeder mencakup**:
  - Konfigurasi sekolah & jam kerja default.
  - Master data tahun ajaran & indikator kurikulum.
  - Sinkronisasi hari libur nasional Indonesia.

---

## 9. Panduan Deployment (cPanel / Shared Hosting)

Untuk shared hosting (cPanel), struktur folder direkomendasikan dipisahkan:
1. **Core Laravel App**: Letakkan seluruh folder aplikasi di `/home/username/pklku/` (di luar public_html).
2. **Public Web Root**: Letakkan isi folder `public/` ke dalam domain folder `/home/username/public_html/`.
3. **Konfigurasi `index.php`**:
   Sesuaikan jalur autoload di `public_html/index.php`:
   ```php
   require __DIR__.'/../pklku/vendor/autoload.php';
   $app = require_once __DIR__.'/../pklku/bootstrap/app.php';
   ```
4. **Symlink Storage**:
   Buat symlink dari `storage/app/public` ke `public_html/storage` via Terminal cPanel:
   ```bash
   ln -s /home/username/pklku/storage/app/public /home/username/public_html/storage
   ```

---

## 10. Konfigurasi Cron Job & Otomatisasi

Untuk menjalankan otomatisasi penandaan siswa Alpha secara harian pada pukul **18:00 WIB**, tambahkan baris cron berikut di cPanel (**Cron Jobs**):

```bash
* * * * * cd /home/username/pklku && php artisan schedule:run >> /dev/null 2>&1
```

Atau jalankan perintah langsung:
```bash
0 18 * * * cd /home/username/pklku && php artisan presensi:auto-absent >> /dev/null 2>&1
```
*(Catatan: Sistem secara otomatis akan melewati/skip hari libur nasional dan hari non-kerja).*

---

## 11. Logika Presensi Geofence, WFA & Hari Libur

### Hierarki Penentuan Radius Presensi:
1. **Pengecekan Mode WFA**: Jika siswa diset `100% WFA` atau jadwal hari ini adalah hari WFA pada skema `Hybrid`, validasi radius kantor **dibebaskan (*bypass*)**. Siswa dapat presensi dari koordinat mana pun dengan tetap mengirim foto selfie & koordinat GPS.
2. **Radius Khusus DUDI**: Untuk siswa pada jadwal WFO, sistem memeriksa radius spesifik per DUDI di menu *Master Data > Mitra DUDI*.
3. **Radius Global Sekolah**: Digunakan jika radius khusus DUDI tidak ditentukan (diatur di *Tambahan > Setting Sekolah*).
4. **Formula Jarak**: Menggunakan *Haversine Formula* untuk menghitung jarak akurat dalam satuan meter antara GPS siswa dan titik DUDI.

### Logika Hari Libur Nasional:
- Jika tanggal hari ini terdaftar di tabel `hari_libur`, sistem otomatis:
  1. Menutup akses presensi dengan peringatan hari libur resmi.
  2. Melewati eksekusi auto-absent cron job.
  3. Memotong hari libur dari total hari kerja di kalkulasi rapor.

---

## 12. Changelog

### Versi 2.2.0 (Agustus 2026)
* ✨ **Fitur Baru: Model Kerja Fleksibel (Hybrid WFO-WFA per Siswa & per Hari)**:
  * Pengaturan skema kerja fleksibel pada Plotting Penempatan (`100% WFO`, `100% WFA`, atau `Hybrid` dengan pilihan hari WFA tertentu).
  * Validasi geofence otomatis membebaskan radius pada hari WFA siswa.
  * Antarmuka presensi murid cerdas yang menampilkan status mode kerja dan GPS realtime.
  * Watermark foto selfie otomatis menyertakan label status `(WFA)` atau `(WFO)`.
  * Tabel monitoring presensi admin & guru dilengkapi badge indikator `Presensi WFA` / `Presensi WFO`.

### Versi 2.1.0 (Agustus 2026)
* ✨ **Fitur Baru: Modul Manajemen Hari Libur & Tanggal Merah Nasional**:
  * Manajemen kalender libur tahunan di Admin.
  * Fitur 1-klik sinkronisasi hari libur nasional Indonesia.
  * Integrasi penuh ke presensi siswa, cron auto-absent, dan kalkulasi rapor kehadiran.
* 🗂️ **Penyempurnaan Navigasi Sidebar**: Reorganisasi menu kategori *Master Data*, *Aktivitas PKL*, dan *Tambahan*.
* 🔤 **Pengurutan Otomatis Alfabetis (A–Z)**: Daftar Mitra DUDI dan siswa bimbingan di dashboard dan peta monitoring otomatis berurutan secara abjad.
* 📄 **Cetak Lembar Kunjungan DUDI**: Penambahan fitur export PDF laporan monitoring kunjungan guru ke DUDI.
* 🚀 **Optimasi Database & Performa**: Penambahan indeks database komposit pada tabel presensi dan relasi penempatan untuk eliminasi masalah N+1 query.
* 🎨 **Pembersihan Antarmuka**: Penghapusan breadcrumb ganda untuk tampilan antarmuka yang lebih lapang, bersih, dan modern.

---

## 13. Kontributor & Lisensi

Dikembangkan dengan dedikasi untuk mendukung kemajuan pendidikan vokasi dan digitalisasi SMK di Indonesia.

* **Lisensi**: [MIT License](LICENSE)
* **Repositori GitHub**: [https://github.com/luphihart/pklku.git](https://github.com/luphihart/pklku.git)
