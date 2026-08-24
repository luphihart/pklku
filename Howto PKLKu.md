# Panduan Penggunaan Sistem PKLku (Howto PKLku)

Sistem **PKLku** adalah platform terintegrasi berbasis web untuk mempermudah monitoring, administrasi, presensi, jurnal, dan evaluasi nilai Praktek Kerja Lapangan (PKL) secara digital.

---

## 🔑 Kredensial Login Bawaan (Default Credentials)

Seluruh pengguna masuk ke sistem menggunakan **Alamat Email** dan **Password** masing-masing yang telah didaftarkan. Di bawah ini adalah akun uji coba bawaan (default seeder) yang siap digunakan:

### 1. Administrator Sekolah
* **Role**: Admin
* **Email**: `admin@pklsmk.sch.id`
* **Password**: `admin123`

### 2. Guru Pembimbing
* **Role**: Guru
* **Password Global**: `guru123`
* **Daftar Akun Uji Coba**:
  * `budi@pklsmk.sch.id` (Budi Hermawan, S.Kom)
  * `siti@pklsmk.sch.id` (Siti Aminah, M.T)
  * `hendro@pklsmk.sch.id` (Hendro Wibowo, S.Pd)

### 3. Siswa (Murid)
* **Role**: Murid
* **Password Global**: `murid123`
* **Daftar Akun Uji Coba**:
  * `ahmad@pklsmk.sch.id` (Ahmad Fauzi)
  * `citra@pklsmk.sch.id` (Citra Lestari)
  * `danu@pklsmk.sch.id` (Danu Wijaya)
  * `eka@pklsmk.sch.id` (Eka Saputri)

---

## 🛠️ Alur Kerja & Fitur Berdasarkan Peran

### 1. Peran: Admin Sekolah (Administrator)
Admin bertugas mempersiapkan seluruh infrastruktur data sebelum PKL dimulai.
* **Langkah 1: Setup Data Master**
  * Masukkan atau impor data master (Tahun Ajaran, Jurusan, Kelas, Siswa, Guru, Mitra DUDI).
* **Langkah 2: Plotting Penempatan**
  * Hubungkan siswa ke DUDI beserta Guru Pembimbingnya melalui menu **Plotting Penempatan**. Ini dapat dilakukan secara massal.
* **Langkah 3: Pengaturan Kriteria Nilai**
  * Tentukan Tujuan Pembelajaran (TP), indikator penilaian aspek sekolah & industri, serta persentase bobot nilai rapor akhir (contoh: 40% Guru, 60% DUDI) di menu **Pengaturan**.
* **Langkah 4: Pengaturan Jam Kehadiran**
  * Konfigurasikan jam masuk, batas terlambat, jam pulang, serta radius toleransi geofence presensi per masing-masing DUDI.

### 2. Peran: Guru Pembimbing
Guru bertugas memantau perkembangan harian siswa bimbingannya secara berkala.
* **Langkah 1: Monitoring Real-Time**
  * Di Dashboard, Guru dapat melihat **Peta Lokasi Aktif** yang menunjukkan sebaran titik presensi masuk/pulang harian siswa bimbingan beserta fotonya secara real-time.
* **Langkah 2: Verifikasi Jurnal**
  * Guru meninjau isi jurnal harian siswa, memberikan koreksi/catatan, lalu memberikan persetujuan status jurnal harian.
* **Langkah 3: Pencatatan Kunjungan ke DUDI**
  * Guru mencatat kunjungan langsung ke lokasi DUDI (pilih jenis kunjungan, isi catatan kunjungan, dan wajib unggah foto bukti kunjungan). Data kunjungan ini juga dapat diekspor ke format PDF resmi.
* **Langkah 4: Pengisian Nilai & Cetak Rapor**
  * Guru menginput nilai aspek sekolah, mengisi keterangan ketercapaian Tujuan Pembelajaran (TP) siswa, lalu mengunduh **Rapor PDF** resmi.

### 3. Peran: Siswa (Murid)
Siswa adalah subjek yang dipantau selama masa pelaksanaan PKL di industri.
* **Langkah 1: Presensi Harian (Geofence & Selfie)**
  * Setiap pagi dan sore, siswa melakukan check-in dan check-out melalui handphone/browser. Presensi hanya berhasil jika posisi GPS siswa berada dalam radius geofence aman kantor DUDI dan wajib mengunggah foto selfie.
* **Langkah 2: Pengisian Jurnal Harian**
  * Siswa menulis deskripsi kegiatan harian dan mengunggah foto dokumentasi pekerjaan sebagai bukti aktivitas magang.
* **Langkah 3: Pengajuan Izin/Sakit**
  * Jika siswa berhalangan hadir, siswa mengirimkan permohonan izin/sakit beserta unggahan file surat keterangan untuk disetujui oleh Guru Pembimbing.
* **Langkah 4: Unduh Rapor Mandiri**
  * Setelah nilai diinput lengkap oleh Guru Pembimbing, siswa dapat melihat rangkuman nilai dan mengunduh berkas PDF Rapor PKL-nya secara mandiri.
