# PKLku - Platform Manajemen & Monitoring Praktek Kerja Lapangan (PKL)

**PKLku** adalah sistem informasi manajemen praktek kerja lapangan (PKL) modern yang dirancang untuk merampingkan seluruh rantai operasional program magang siswa SMK. Platform ini menjembatani komunikasi, kolaborasi, pemantauan presensi, pengisian jurnal kegiatan harian, logbook kunjungan guru, hingga pengisian nilai rapor industri & sekolah secara aman, transparan, dan real-time.

Aplikasi ini mengintegrasikan 4 peran utama secara terintegrasi: **Admin Sekolah**, **Guru Pembimbing**, **Pembimbing Industri (DUDI)**, dan **Siswa (Murid)**.

---

## 📋 Daftar Isi
1. [Fitur Utama](#-fitur-utama)
2. [Modul & Hak Akses Pengguna](#-modul--hak-akses-pengguna)
3. [Arsitektur & Struktur Proyek](#-arsitektur--struktur-proyek)
4. [Persyaratan Sistem](#-persyaratan-sistem)
5. [Instalasi Lokal](#-instalasi-lokal)
6. [Konfigurasi Environment](#-konfigurasi-environment)
7. [Inisialisasi Database](#-inisialisasi-database)
8. [Panduan Uji Coba Unit & Feature Test](#-panduan-uji-coba-unit--feature-test)
9. [Deployment Server](#-deployment-server)
10. [Lisensi & Hak Cipta](#-lisensi--hak-cipta)

---

## 🚀 Fitur Utama

### 📍 1. Presensi Geofence & Selfie Verification (Real-time)
* **Geofencing Aman**: Pembatasan radius aman kehadiran siswa (check-in/check-out) disesuaikan secara presisi per lokasi DUDI (contoh: radius 50 meter).
* **Selfie Verification**: Siswa wajib mengunggah foto selfie saat melakukan presensi untuk mencegah manipulasi kehadiran.
* **Geotagging Presensi**: Lokasi harian siswa terplotting langsung pada Peta Leaflet.js dashboard Guru Pembimbing.

### 📓 2. Jurnal Harian Siswa & Validasi Industri
* **Logbook Deskriptif**: Siswa mencatat ringkasan aktivitas, materi pembelajaran harian, beserta lampiran foto bukti pendukung kegiatan.
* **Persetujuan Pembimbing DUDI**: Pembimbing DUDI memantau, menyetujui, atau memberikan koreksi terhadap jurnal harian siswa.

### 🚗 3. Kunjungan Pembimbing (Kunjungan DUDI)
* **Pencatatan Kunjungan**: Guru pembimbing dapat mencatat kunjungan langsung ke DUDI.
* **Kategorisasi Kunjungan**: Jenis kunjungan lengkap (Penjajakan Kerja Sama, Penyerahan Murid, Monitoring Berkala, Penarikan PKL).
* **Foto Bukti & Catatan Wajib**: Mewajibkan unggah dokumen/foto kunjungan langsung ke lapangan.
* **Ekspor PDF Rekap**: Memungkinkan Guru dan Admin mengekspor rekap kunjungan pembimbing berformat PDF resmi sekolah yang rapi.

### 📝 4. Penilaian Kolaboratif Dinamis
* **Bobot Rapor Fleksibel**: Persentase bobot nilai akhir sekolah vs industri dapat disesuaikan dinamis oleh Admin (misalnya: 50% Aspek Guru, 50% Aspek DUDI).
* **Evaluasi per Tujuan Pembelajaran (TP)**: Penilaian detail yang memetakan deskripsi ketercapaian Tujuan Pembelajaran kurikulum sekolah.
* **Import/Export Nilai via Excel**: Penginputan nilai massal dengan templat spreadsheet otomatis terintegrasi.
* **Rapor Rapi Dompdf**: Cetak lembar Rapor PKL formal 1-halaman A4 dengan layout tanda tangan guru pembimbing & pembimbing industri sejajar serta rekap absensi.

---

## 👥 Modul & Hak Akses Pengguna

### 🛡️ 1. Admin Sekolah
* Mengelola data master (Tahun Ajaran, Jurusan, Kelas, Siswa, Guru, Mitra DUDI, Penempatan PKL).
* Memantau peta lokasi aktif DUDI dengan daftar total keterisian murid terplotting.
* Mengelola master Tujuan Pembelajaran (TP) & Indikator Penilaian.
* Melakukan aksi edit/hapus pada log kunjungan pembimbing (kunjungan dibatasi hanya bisa diisi oleh Guru).
* Mengonfigurasi parameter branding sekolah, jam operasional presensi default, kota tanda tangan, logo, dan footer sistem.

### 👨‍🏫 2. Guru Pembimbing
* Memantau peta lokasi DUDI bimbingan & plotting titik presensi real-time siswa bimbingannya hari ini.
* Meninjau, menyetujui, dan merekapitulasi presensi serta pengajuan izin/sakit siswa bimbingan.
* Menginput nilai aspek sekolah & deskripsi catatan Tujuan Pembelajaran (TP) rapor PKL.
* Mengisi, mengedit, menghapus, serta mencetak rekap Kunjungan Pembimbing ke DUDI.

### 🏢 3. Pembimbing Industri (DUDI)
* Memverifikasi jurnal harian siswa magang di perusahaannya.
* Menginput nilai aspek kompetensi industri rapor PKL.
* Memantau grafik kehadiran harian siswa magang yang berada di lokasinya.

### 🎓 4. Siswa (Murid)
* Mengisi absensi masuk/pulang harian terproteksi geofence GPS + kamera selfie.
* Mengunggah jurnal harian dan pengajuan izin absensi (izin/sakit).
* Melihat rangkuman absensi bulanan, status jurnal, dan mengunduh berkas PDF Rapor PKL secara mandiri.

---

## 🏗️ Arsitektur & Struktur Proyek

Sistem ini dikembangkan menggunakan konsep **Modular Monolith** pada Laravel. Setiap modul aplikasi dikelompokkan dalam satu direktori terisolasi di bawah folder `app/Modules/`, yang mencakup routing, views, controllers, models, dan services masing-masing.

### 📂 Struktur Direktori Utama
```
d:/PKLV2/
├── app/
│   └── Modules/                      # Lokasi Modul Aplikasi (Modular Monolith)
│       ├── Auth/                     # Otentikasi login, logout, & hak akses
│       ├── Dashboard/                # Halaman muka (Peta Leaflet, statistik, pengumuman)
│       ├── Jurnal/                   # Pengisian jurnal kegiatan harian siswa & validasi DUDI
│       ├── Laporan/                  # Ekspor PDF Rapor, presensi harian, & jurnal
│       ├── MasterData/               # Manajemen Siswa, Guru, DUDI, Kelas, Jurusan
│       ├── Penilaian/                # Tujuan Pembelajaran (TP), Nilai Akhir, Impor/Ekspor Excel
│       ├── PKL/                      # Manajemen Penempatan PKL, Kunjungan Pembimbing ke DUDI
│       ├── Presensi/                 # Manajemen Absensi, Izin, Sakit, Lokasi Geofence
│       └── Setting/                  # Pengaturan branding logo, kop surat, & parameter sistem
├── bootstrap/                        # Bootstrapping konfigurasi Laravel
├── config/                           # Berkas konfigurasi framework
├── database/
│   ├── migrations/                   # Skema migrasi database relasional
│   └── seeders/                      # Data awal (seeder) bawaan sistem
├── public/                           # Aset terkompilasi (CSS, JS, upload berkas foto)
├── resources/                        # Layout utama (Blade templates) & stylesheet master (CSS)
├── routes/                           # Routing aplikasi Laravel
└── tests/                            # Pengujian otomatis Unit & Feature (TDD)
```

---

## 💻 Persyaratan Sistem
* **Sistem Operasi**: Windows / Linux / macOS
* **Web Server**: Apache / Nginx
* **Versi PHP**: `>= 8.2` (dengan ekstensi GD, PDO, Mbstring, XML, ZIP, SQLite3)
* **Basis Data**: MySQL `>= 5.7` atau MariaDB `>= 10.3` (SQLite didukung untuk testing)
* **Package Manager**: Composer `>= 2.0` & Node.js/NPM `>= 18.0`

---

## 🔧 Instalasi Lokal

1. Pindahkan atau clone direktori proyek `PKLV2` ke dalam lokal web root Anda (seperti `htdocs` atau `var/www/html`).
2. Pasang semua dependensi library PHP menggunakan Composer:
   ```bash
   composer install
   ```
3. Pasang semua dependensi modul frontend NPM:
   ```bash
   npm install
   ```

---

## ⚙️ Konfigurasi Environment

1. Salin berkas konfigurasi env bawaan:
   ```bash
   cp .env.example .env
   ```
2. Sesuaikan konfigurasi koneksi database MySQL lokal Anda di dalam berkas `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=pklku_db
   DB_USERNAME=root
   DB_PASSWORD=password_anda
   ```
3. Generate application key:
   ```bash
   php artisan key:generate
   ```

---

## 💾 Inisialisasi Database

1. Buat database baru bernama `pklku_db` (atau sesuai konfigurasi di `.env`) di dalam DBMS MySQL/MariaDB Anda.
2. Jalankan perintah migrasi skema tabel beserta data awal bawaan (seeder):
   ```bash
   php artisan migrate --seed
   ```
3. Buat symbolic link folder storage publik agar file upload dapat diakses di browser:
   ```bash
   php artisan storage:link
   ```

---

## 🧪 Panduan Uji Coba Unit & Feature Test

Sistem telah dilengkapi dengan automated tests (Unit & Feature) menggunakan PHPUnit. Untuk memverifikasi integritas sistem (master data, manual presensi, import excel, hingga export PDF) berfungsi 100% tanpa regresi, jalankan perintah pengujian:

```bash
php artisan test
```

---

## 🌐 Deployment Server

1. Unggah seluruh berkas proyek `PKLV2` (kecuali folder `node_modules` dan berkas `.env` lokal) ke folder di luar directory `public_html` hosting Anda demi keamanan.
2. Pindahkan isi file di dalam folder `public/` ke dalam direktori utama web root hosting Anda (seperti `public_html`).
3. Sesuaikan *pathing* `require` bootstrap pada berkas `index.php` yang dipindahkan di `public_html` agar mengarah ke folder source `PKLV2` dengan tepat.
4. Buat tautan symlink storage dengan membuat file script PHP kustom berisi `symlink('/home/username/PKLV2/storage/app/public', '/home/username/public_html/storage')` lalu jalankan sekali lewat browser.
5. Harap pastikan folder `storage/` dan `bootstrap/cache/` di dalam server hosting memiliki hak izin tulis (*write permission*) `775` atau `755`.

---

## 📄 Lisensi & Hak Cipta
Aplikasi **PKLku** didistribusikan di bawah lisensi **MIT License**.
Hak cipta dilindungi undang-undang.
