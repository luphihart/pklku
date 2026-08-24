# Product Requirement Document (PRD) - PKLku

## 1. Executive Summary & Vision
**PKLku** adalah platform berbasis web (Sistem Informasi Manajemen) yang dirancang khusus untuk mempermudah, mendigitalisasi, dan mengintegrasikan seluruh siklus operasional program Praktek Kerja Lapangan (PKL) / Magang bagi Sekolah Menengah Kejuruan (SMK). 

Platform ini menjembatani empat pemangku kepentingan utama: **Admin Sekolah**, **Guru Pembimbing**, **Pembimbing Industri (DUDI)**, dan **Siswa (Murid)** ke dalam satu ekosistem terpadu secara real-time, transparan, dan terukur.

---

## 2. Target User Personas & Roles

### 2.1. Administrator Sekolah (Admin)
* **Wewenang**: Pengelolaan tingkat tinggi (Full System Control).
* **Fungsi Utama**:
  * Mengelola seluruh Data Master (Tahun Ajaran, Jurusan, Kelas, Guru, Murid, DUDI, Pembimbing Industri).
  * Mengatur Plotting Penempatan PKL siswa ke DUDI dan Guru Pembimbing.
  * Mengonfigurasi parameter sistem (Branding sekolah, Kop Surat, Jam Operasional Presensi, Radius Geofence, Bobot Nilai Rapor).
  * Mengelola Master Tujuan Pembelajaran (TP) & Indikator Penilaian.
  * Memiliki akses eksklusif untuk koreksi presensi manual, reset database, dan penghapusan data nilai/kunjungan.

### 2.2. Guru Pembimbing
* **Wewenang**: Monitoring dan evaluasi akademik siswa bimbingan.
* **Fungsi Utama**:
  * Memantau presensi real-time siswa bimbingan di Peta Interaktif (Leaflet.js).
  * Meninjau, menyetujui, menolak, atau memberikan catatan revisi pada Jurnal Harian Siswa.
  * Memberikan persetujuan (*approval*) terhadap pengajuan Izin/Sakit siswa bimbingan.
  * Pencatatan dan pelaporan Kunjungan Pembimbing ke DUDI (dengan bukti foto wajib).
  * Pengisian nilai aspek sekolah, deskripsi Tujuan Pembelajaran (TP), dan cetak Rapor PDF.

### 2.3. Pembimbing Industri (DUDI)
* **Wewenang**: Pemantauan dan evaluasi teknis lapangan.
* **Fungsi Utama**:
  * Memantau kehadiran dan logbook harian siswa magang di tempat industri.
  * Melakukan verifikasi/koreksi terhadap Jurnal Harian Siswa.
  * Pengisian nilai kompetensi aspek industri/lapangan.

### 2.4. Siswa / Murid Peserta PKL
* **Wewenang**: Pengguna operasional harian.
* **Fungsi Utama**:
  * Melakukan presensi Check-In dan Check-Out harian berbasis GPS Geofencing + Kamera Selfie.
  * Mengisi logbook Jurnal Harian kegiatan magang (beserta foto bukti aktivitas).
  * Mengirimkan pengajuan Izin/Sakit disertai foto lampiran surat dokter/orang tua.
  * Memantau status persetujuan jurnal, rekapitulasi kehadiran, dan mengunduh berkas Rapor PDF mandiri.

---

## 3. Core Features & Functional Requirements

### 3.1. Master Data Management & Plotting
* **Manajemen Akademik**: Pengelolaan Tahun Ajaran aktif, Jurusan (Kode, Nama, Singkatan), Kelas, Guru (NIP), Murid (NIS, Tanggal Lahir), dan DUDI (Latitude, Longitude, Radius, Hari Kerja).
* **Plotting Penempatan PKL**: Menghubungkan Murid + DUDI + Guru Pembimbing + Pembimbing Industri dalam satu ikatan penempatan aktif.
* **Impor/Ekspor Massal**: Kemampuan impor massal data master murid, guru, dan nilai dari berkas Excel (.xlsx).

### 3.2. Presensi Geofence & Selfie Verification
* **Haversine Distance Formula**: Menghitung jarak presisi antara lokasi GPS perangkat siswa dengan titik koordinat DUDI di sisi server.
* **Geofence Enforcement**: Presensi hanya diizinkan jika posisi siswa berada di dalam radius aman DUDI (contoh: 50 - 100 meter).
* **Selfie Camera & Dynamic Aspect Ratio**: Kamera menangkap foto selfie dengan rasio asli perangkat tanpa membuat wajah terdistorsi/gepeng.
* **UI Preview Mirroring vs Clean Save**: Tampilan preview kamera di layar murid di-mirror (`scaleX(-1)`) untuk kenyamanan, tetapi file foto yang disimpan ke server tetap dalam posisi normal (non-mirror) agar teks pada baju/latar belakang terbaca jelas.
* **Image Compression & Watermarking**: Foto presensi otomatis dikompresi (max-width 640px) dan diberi *watermark* info waktu, nama siswa, DUDI, & koordinat GPS secara otomatis.
* **Audit Kehadiran Dinamis**: Perhitungan hari Tanpa Keterangan (Alfa) dihitung secara real-time berdasarkan hari kerja efektif DUDI dikurangi hari hadir dan izin/sakit yang disetujui.

### 3.3. Management Izin & Sakit
* **Lampiran Khusus Foto**: Mengubah opsi unggah lampiran menjadi khusus foto gambar (JPG, JPEG, PNG) dan menolak file PDF demi keseragaman visual & kompresi.
* **Image Auto-Compression**: Lampiran surat izin/sakit otomatis dikompresi proporsional (max-width 800px, JPEG quality 75%) saat diunggah.
* **Workflow Verification**: Pengajuan berstatus `pending` dapat disetujui (`approved`) atau ditolak (`ditolak`) oleh Guru Pembimbing dengan catatan tanggapan.

### 3.4. Jurnal Kegiatan Harian
* **Pencatatan Aktivitas**: Siswa menginput deskripsi pekerjaan harian dan melampirkan foto kegiatan.
* **Status Verifikasi**: Status jurnal (`pending`, `disetujui`, `ditolak`) dengan fitur komentar revisi dari Guru/DUDI.

### 3.5. Kunjungan Pembimbing ke DUDI
* **Pencatatan Log Kunjungan**: Guru menginput kunjungan dengan memilih Jenis Kunjungan (*Penjajakan Kerja Sama, Penyerahan Murid, Monitoring Berkala, Penarikan PKL*).
* **Bukti Foto Wajib**: Wajib mengunggah foto bukti fisik kunjungan di lokasi DUDI.
* **Aksi Administrative**: Fitur Edit dan Hapus log kunjungan untuk Guru dan Admin.
* **Ekspor PDF Rekap**: Generasi dokumen PDF resmi rekapitulasi kunjungan pembimbing ber-kop sekolah.

### 3.6. Penilaian PKL & Rapor PDF
* **Penilaian Kolaboratif**: Mengombinasikan skor aspek sekolah (Guru) dan aspek industri (DUDI).
* **Nilai Berbasis TP (Tujuan Pembelajaran)**: Pemetaan skor berdasarkan indikator dinamis beserta input narasi ketercapaian Tujuan Pembelajaran (TP).
* **Cetak Rapor PDF A4**: Output PDF 1-halaman A4 yang terstruktur, rapi, *null-safe* (tidak error jika pembimbing industri belum terhubung), dan menggunakan gambar format Base64 Data URI untuk menjamin foto/logo ter-render 100% pada Dompdf.
* **Fitur Hapus Nilai (Admin Only)**: Admin dapat menghapus data nilai yang sudah diinput jika memerlukan reset/penginputan ulang.

### 3.7. Pusat Laporan & Ekspor Excel
* **Ekspor Harian**: Mengunduh rekap presensi hari tertentu dengan status lengkap (Jam Masuk, Jam Pulang, Tepat Waktu / Terlambat / Pulang Cepat / Cuti / -).
* **Ekspor Mingguan / Bulanan / Kustom (Pivot Date)**: Data tanggal disusun mendatar di kolom sebelah kanan (Pivot Column). Setiap tanggal terbagi menjadi kolom **Waktu** (`HH:MM - HH:MM`) dan **Keterangan** (`Tepat Waktu`, `Terlambat`, `Sakit`, `Izin`, `-`).

### 3.8. Sistem & Audit Log
* **Pengaturan Branding**: Kustomisasi Nama Sekolah, Alamat, Kop Surat, Kota TTD, Logo, dan Footer Login.
* **Audit Trail**: Pencatatan aktivitas pengguna (login, update profil, pengubahan data) beserta IP Address dan User Agent.

---

## 4. Non-Functional Requirements

### 4.1. Performance & Scalability
* Penggunaan teknik kompresi gambar (GD Native / Intervention Image) untuk menghemat ruang penyimpanan server hingga 70-80%.
* Implementasi query database yang efisien dengan *Eager Loading* (`with()`) untuk mencegah *N+1 Query Problem*.

### 4.2. Security & Data Protection
* Otentikasi berbasis session aman Laravel dengan proteksi CSRF (*Cross-Site Request Forgery*) pada seluruh formulir.
* Otorisasi ketat berbasis Role Middleware (`admin`, `guru`, `murid`).
* Verifikasi koordinat lokasi presensi di sisi server (*Server-side Haversine Validation*).

### 4.3. Usability & Aesthetics
* Mengusung desain antarmuka modern (Bootstrap 5, Vanilla CSS, Font Inter & Outfit) tanpa ketergantungan pada TailwindCSS.
* Responsif di layar desktop, tablet, maupun smartphone (Mobile-First Presensi).
