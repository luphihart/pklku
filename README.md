# PKLku - Platform Manajemen & Monitoring Praktek Kerja Lapangan (PKL)

**PKLku** adalah aplikasi web berbasis framework Laravel yang dirancang untuk mempermudah manajemen, pemantauan, presensi, dan penilaian pelaksanaan Praktek Kerja Lapangan (PKL) bagi sekolah menengah kejuruan (SMK). Aplikasi ini mengintegrasikan peran **Admin Sekolah**, **Guru Pembimbing**, **Pembimbing Industri (DUDI)**, dan **Siswa (Murid)** ke dalam satu ekosistem digital yang modern.

---

## 📋 Daftar Isi
1. [Pendahuluan](#1-pendahuluan)
2. [Tujuan Aplikasi](#2-tujuan-aplikasi)
3. [Fitur](#3-fitur)
4. [Role Pengguna](#4-role-pengguna)
5. [Arsitektur Sistem](#5-arsitektur-sistem)
6. [Teknologi yang Digunakan](#6-teknologi-yang-digunakan)
7. [Struktur Folder](#7-struktur-folder)
8. [Persyaratan Sistem](#8-persyaratan-sistem)
9. [Instalasi Lokal](#9-instalasi-lokal)
10. [Konfigurasi Environment](#10-konfigurasi-environment)
11. [Setup Database](#11-setup-database)
12. [Menjalankan Aplikasi](#12-menjalankan-aplikasi)
13. [Deployment](#13-deployment)
14. [Struktur Database](#14-struktur-database)
15. [Dokumentasi API](#15-dokumentasi-api)
16. [Screenshot Antarmuka](#16-screenshot-antarmuka)
17. [Troubleshooting](#17-troubleshooting)
18. [FAQ](#18-faq)
19. [Changelog](#19-changelog)
20. [Roadmap](#20-roadmap)
21. [Kontributor](#21-kontributor)
22. [Lisensi](#22-lisensi)

---

## 1. Pendahuluan
Aktivitas Praktek Kerja Lapangan (PKL) siswa SMK seringkali terkendala oleh sulitnya pemantauan kehadiran harian, pengumpulan jurnal harian secara fisik yang rentan hilang, serta proses penilaian manual yang lambat. **PKLku** hadir sebagai solusi berbasis web terpadu untuk mendigitalisasi seluruh rantai proses PKL mulai dari administrasi data, absensi berbasis lokasi GPS, pelaporan jurnal harian, hingga penilaian akhir rapor secara otomatis dan aman.

## 2. Tujuan Aplikasi
* **Meningkatkan Akurasi Absensi**: Mencegah kecurangan absensi siswa menggunakan Geofencing GPS dan verifikasi foto selfie.
* **Mempermudah Monitoring**: Menyediakan wadah bagi guru pembimbing untuk memantau jurnal harian dan presensi siswa tanpa harus berkunjung ke lokasi industri setiap hari.
* **Meningkatkan Efisiensi Penilaian**: Memfasilitasi industri dan sekolah untuk menginput nilai secara kolaboratif sesuai dengan Tujuan Pembelajaran (TP) sekolah dan mengunduhnya secara langsung dalam bentuk PDF A4 resmi.
* **Penyimpanan Data Terpusat**: Menyediakan database tunggal untuk riwayat hubungan kerja sama industri sekolah dari tahun ke tahun.

## 3. Fitur
* **Presensi Geofence & Selfie**: Batasan radius lokasi aman presensi masuk/pulang serta pengambilan foto kamera perangkat.
* **Jurnal Kegiatan Harian**: Pencatatan jurnal deskriptif dengan unggah foto bukti kegiatan dan verifikasi persetujuan industri.
* **Pengajuan Izin/Sakit**: Pengajuan izin harian siswa dengan unggah bukti surat pendukung (PDF/Gambar) dan persetujuan dari guru.
* **Penilaian Dinamis & Catatan TP**: Input nilai berbasis indikator Guru/Industri dan keterangan evaluasi spesifik per Tujuan Pembelajaran (TP) dengan merge baris (*rowspan*).
* **Unduh Rapor PDF Resmi**: Generate PDF Rapor 1-halaman A4 yang rapi dan seragam menggunakan Dompdf.
* **Ekspor & Impor Massal Excel**: Pengolahan data master serta impor nilai menggunakan PhpSpreadsheet.
* **Konfigurasi Branding & Sistem**: Kustomisasi logo sekolah, kepala sekolah, koordinat DUDI default, jam kerja absensi, kota surat, hingga copyright footer halaman login.

## 4. Role Pengguna
* **Administrator**:
  * Mengelola data master (Tahun Ajaran, Jurusan, Kelas, Siswa, Guru, DUDI, Penempatan).
  * Mengatur parameter sistem, jam absensi, geofence, dan bobot persentase nilai rapor.
  * Mengakses laporan rekapitulasi data pendaftaran dan grafik keterisian industri.
* **Guru Pembimbing**:
  * Memantau daftar siswa bimbingan beserta penempatannya.
  * Meninjau jurnal kegiatan harian dan rekap kehadiran siswa.
  * Memberikan persetujuan pengajuan izin/sakit.
  * Mengisi nilai aspek sekolah dan catatan evaluasi Tujuan Pembelajaran (TP).
* **Pembimbing Industri (DUDI)**:
  * Melakukan verifikasi dan koreksi jurnal harian siswa di tempat PKL.
  * Memantau kedatangan siswa bimbingan.
  * Mengisi nilai kompetensi teknis aspek industri.
* **Siswa (Murid)**:
  * Mengisi presensi masuk dan pulang harian di tempat PKL.
  * Menulis jurnal harian beserta foto dokumentasi.
  * Memantau status persetujuan jurnal, izin, dan rekap nilai akhir.
  * Mengunduh file PDF rapor nilai pribadi secara mandiri.

## 5. Arsitektur Sistem
Aplikasi ini dibangun menggunakan arsitektur **Monolith Modern** berbasis MVC (Model-View-Controller) dengan modulasi modular (Modular Laravel). Setiap modul (seperti Auth, PKL, Presensi, Penilaian, Laporan) diisolasi ke dalam direktori independen di bawah `app/Modules/` guna mempermudah pemeliharaan jangka panjang dan isolasi fitur.

## 6. Teknologi yang Digunakan
* **Framework**: Laravel 11.x
* **Bahasa Pemrograman**: PHP >= 8.2 & Javascript (ES6)
* **Basis Data**: MySQL / MariaDB
* **Gaya & Desain (CSS)**: Bootstrap 5 & Vanilla CSS (Premium & Modern Aesthetics dengan font Inter dan Outfit)
* **Kompiler Aset**: Vite
* **Peta & Lokasi**: Leaflet.js
* **Framework Interaktif**: Alpine.js (State management sidebar & modal dinamis)
* **Library Ekspor/Impor**: Barryvdh Dompdf & Maatwebsite Excel

## 7. Struktur Folder
```
d:/PKLV2/
├── app/
│   ├── Modules/                   # Direktori Modul Sistem (Modular Laravel)
│   │   ├── Auth/                  # Modul login, logout, dan otorisasi
│   │   ├── MasterData/            # Modul master Kelas, Jurusan, Siswa, Guru, DUDI
│   │   ├── PKL/                   # Modul penempatan PKL aktif
│   │   ├── Presensi/              # Modul check-in, check-out, izin/sakit
│   │   ├── Jurnal/                # Modul jurnal harian siswa
│   │   ├── Penilaian/             # Modul indikator, Tujuan Pembelajaran, input nilai, Excel
│   │   └── Laporan/               # Modul generator laporan PDF rapor & presensi
│   └── Providers/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/                # Migrasi skema database relasional
│   └── seeders/                   # Data awal (seeder) admin, parameter, DUDI
├── public/                        # Aset publik terkompilasi, gambar, logo
├── resources/
│   ├── css/                       # app.css (style tema premium)
│   ├── js/                        # app.js & Alpine.js scripts
│   └── views/                     # Layout utama dashboard admin/auth
├── routes/
└── tests/                         # Pengujian otomatis Feature & Unit
```

## 8. Persyaratan Sistem
* Sistem Operasi: Windows / Linux / macOS
* Web Server: Apache / Nginx (diperlukan saat deployment)
* Versi PHP: >= 8.2
* Database: MySQL >= 5.7 atau MariaDB >= 10.3
* Package Manager: Composer >= 2.0 & Node.js/NPM >= 18.0

## 9. Instalasi Lokal
1. Pindahkan atau clone direktori proyek `PKLV2` ke server lokal Anda (misal folder `htdocs` XAMPP).
2. Jalankan instalasi seluruh dependensi backend via Composer:
   ```bash
   composer install
   ```
3. Jalankan instalasi modul frontend:
   ```bash
   npm install
   ```

## 10. Konfigurasi Environment
1. Buat file `.env` dengan menyalin berkas `.env.example`:
   ```bash
   copy .env.example .env
   ```
2. Sesuaikan konfigurasi koneksi database Anda di dalam `.env` (isi `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
3. Generate kunci aplikasi Laravel:
   ```bash
   php artisan key:generate
   ```

## 11. Setup Database
1. Buat database baru bernama `pkl_smk_antigravity` pada server MySQL Anda.
2. Jalankan perintah migrasi beserta seeding data awal bawaan:
   ```bash
   php artisan migrate --seed
   ```
3. Hubungkan folder penyimpanan lokal agar file yang diunggah dapat diakses publik:
   ```bash
   php artisan storage:link
   ```

## 12. Menjalankan Aplikasi
1. Jalankan server lokal Laravel:
   ```bash
   php artisan serve
   ```
2. Jalankan build aset CSS & JS menggunakan Vite:
   ```bash
   npm run build
   ```
3. Akses aplikasi melalui alamat `http://127.0.0.1:8000` di web browser Anda.

## 13. Deployment
Untuk panduan lengkap dan ringkas mengenai proses upload ke cPanel, setup folder source, pemetaan folder public, serta pembuatan symlink storage di shared hosting produksi, silakan merujuk ke berkas dokumentasi terpisah di [DEPLOYMENT.md](file:///d:/PKLV2/DEPLOYMENT.md).

## 14. Struktur Database
Database sistem terdiri dari tabel utama berikut:
* `users`: Menyimpan informasi kredensial login dan hak akses (`admin`, `guru`, `industri`, `murid`).
* `penempatan_pkl`: Tabel relasional utama yang menghubungkan siswa dengan DUDI, guru pembimbing, dan pembimbing industri.
* `presensi`: Menyimpan log jam masuk, jam pulang, titik koordinat GPS, status ketepatan waktu, dan path foto selfie.
* `izin_sakit`: Menyimpan data pengajuan ketidakhadiran siswa karena sakit/izin beserta berkas bukti surat pendukung.
* `penilaian_pkl`: Menyimpan nilai rata-rata, detail skor indikator dalam format JSON, catatan akhir, serta komentar Tujuan Pembelajaran (`keterangan_tp_json`).
* `settings`: Menyimpan konfigurasi dinamis (nama sekolah, alamat, logo, koordinat default, radius geofence, kota, dan footer login).

## 15. Dokumentasi API
Daftar endpoint API presensi (Check-in, Check-out), ekspor excel, dan respons data JSON terdokumentasi lengkap pada berkas [API.md](file:///d:/PKLV2/API.md).

## 16. Screenshot Antarmuka
Berikut adalah representasi tata letak visual antarmuka sistem PKLku:
* **Halaman Login Premium**: Desain bersih modern berpola radial gradient dengan copyright sekolah dinamis.
* **Dashboard Kolaboratif**: Visualisasi grafik data penempatan, statistik kehadiran, dan menu log jurnal harian.
* **Cetak Rapor PDF A4**: Tata letak rapor presisi 1-halaman A4 dengan kolom komentar tergabung (*rowspan*) per Tujuan Pembelajaran dan tanda tangan sejajar.

## 17. Troubleshooting
* **Error: Storage Folder Permission Denied**:
  * Di server produksi, pastikan folder `storage/` dan `bootstrap/cache/` memiliki izin akses `775` (atau `755`).
* **Gambar Presensi/Logo Tidak Tampil**:
  * Jalankan kembali perintah `php artisan storage:link`. Di shared hosting, pastikan folder `public_html/storage` merupakan symbolic link aktif yang mengarah ke `storage/app/public`.
* **Font PDF Mengalami Glitch / Berubah Menjadi Serif**:
  * Hal ini dikarenakan Dompdf tidak menemukan font Arial Bold saat merender berat font kustom seperti `600`. Pastikan menggunakan `font-weight: bold` pada elemen yang ingin ditebalkan untuk memaksa renderer memuat font Arial tebal.

## 18. FAQ
* **Apakah siswa bisa memanipulasi lokasi absensi?**
  * Tidak. Sistem membandingkan koordinat GPS dari perangkat siswa dengan koordinat kantor DUDI secara realtime di sisi server. Jika koordinat berada di luar radius geofence (contoh 50m), absensi akan langsung ditolak.
* **Bagaimana cara mengubah bobot nilai Rapor?**
  * Masuk sebagai Admin -> Menu **Pengaturan Sistem** -> Tab **Bobot Nilai Rapor**. Ubah angka presentase lalu simpan (total persentase wajib bernilai 100%).

## 19. Changelog
### Versi 1.2.0 (Juli 2026)
* Mengintegrasikan tabel statistik Kehadiran (Sakit, Izin, Alfa) pada PDF Rapor Nilai siswa.
* Menambahkan pengaturan admin untuk Kota Tanda Tangan (`kota_sekolah`) dan Copyright Halaman Login (`footer_login`).
* Mengharmoniskan seluruh font PDF menggunakan Arial-Bold seragam (mengganti font-weight 600 ke bold).
* Merapikan margin pembatas info paginasi agar tidak menempel rapat dengan tombol navigasi.

## 20. Roadmap
* Pengembangan Aplikasi Mobile Native (Android & iOS) khusus untuk modul presensi siswa.
* Integrasi Notifikasi Real-time WhatsApp/Telegram API ke orang tua siswa saat siswa tidak hadir di industri.
* Fitur logbook berbasis berkas lampiran PDF portofolio proyek siswa.

## 21. Kontributor
* Tim Pengembang IT SMK Negeri 1 Antigravity
* Budi Hermawan, S.Kom (Koordinator Hubungan Industri)
* Feri Rokhyani Thohid, S.Pd (Kurikulum & Evaluasi)

## 22. Lisensi
Proyek **PKLku** didistribusikan di bawah lisensi **MIT License**. Hak cipta dilindungi undang-undang.
