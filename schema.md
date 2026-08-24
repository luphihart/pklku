# Database Schema Documentation - PKLku

Dokumen ini mendokumentasikan skema database relasional proyek **PKLku**, mencakup struktur tabel, tipe data, kunci utama/asing (*primary/foreign keys*), indeks, dan relasi antar tabel.

---

## 1. Entity Relationship Overview

Sistem PKLku berpusat pada relasi tabel `penempatan_pkl` yang menghubungkan `murid`, `dudi`, `guru`, dan `pembimbing_industri`. Dari tabel penempatan ini, sistem mencatat aktivitas operasional seperti `presensi`, `jurnal`, `izin_sakit`, `kunjungan_monitoring`, serta `penilaian_pkl`.

---

## 2. Rincian Skema Tabel Database

### 2.1. Tabel `users`
Menyimpan data kredensial login dan profil utama pengguna sistem.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID unik pengguna |
| `name` | Varchar(255) | Not Null | Nama lengkap pengguna |
| `email` | Varchar(255) | Unique, Not Null | Alamat email (Username login) |
| `email_verified_at` | Timestamp | Nullable | Waktu verifikasi email |
| `password` | Varchar(255) | Not Null | Hash password (Bcrypt) |
| `role` | Enum | 'admin', 'guru', 'murid' | Peran pengguna dalam sistem |
| `phone` | Varchar(20) | Nullable | Nomor telepon / Whatsapp |
| `photo` | Varchar(255) | Nullable | Nama file foto profil |
| `tanggal_lahir` | Date | Nullable | Tanggal lahir (Banner ulang tahun) |
| `remember_token` | Varchar(100) | Nullable | Token remember me session |
| `created_at` | Timestamp | Nullable | Waktu pembuatan |
| `updated_at` | Timestamp | Nullable | Waktu pembaruan terakhir |
| `deleted_at` | Timestamp | Nullable | Soft delete timestamp |

---

### 2.2. Tabel `tahun_ajaran`
Menyimpan master data periode tahun ajaran dan semester aktif.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID tahun ajaran |
| `tahun` | Varchar(20) | Not Null | Format contoh: `2025/2026` |
| `semester` | Enum | 'ganjil', 'genap' | Semester berjalan |
| `status` | Enum | 'aktif', 'nonaktif', Default: 'nonaktif' | Status keaktifan periode |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |
| `deleted_at` | Timestamp | Nullable | Soft delete timestamp |

---

### 2.3. Tabel `jurusan`
Menyimpan master data kompetensi keahlian / jurusan sekolah.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID jurusan |
| `kode` | Varchar(20) | Unique, Not Null | Kode singkatan jurusan (misal: RPL, TKJ) |
| `nama` | Varchar(255) | Not Null | Nama lengkap jurusan |
| `singkatan` | Varchar(50) | Nullable | Singkatan jurusan |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |
| `deleted_at` | Timestamp | Nullable | Soft delete timestamp |

---

### 2.4. Tabel `kelas`
Menyimpan master data rombongan belajar / kelas siswa.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID kelas |
| `jurusan_id` | BigInt (Unsigned) | Foreign Key -> `jurusan(id)` ON DELETE CASCADE | Relasi ke tabel jurusan |
| `nama` | Varchar(100) | Not Null | Nama kelas (contoh: XII RPL 1) |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |
| `deleted_at` | Timestamp | Nullable | Soft delete timestamp |

---

### 2.5. Tabel `guru`
Menyimpan profil khusus pengguna ber-role Guru Pembimbing.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID guru |
| `user_id` | BigInt (Unsigned) | Foreign Key -> `users(id)` ON DELETE CASCADE | Relasi ke akun user |
| `nip` | Varchar(50) | Unique, Nullable | Nomor Induk Pegawai |
| `nama` | Varchar(255) | Not Null | Nama lengkap beserta gelar |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |
| `deleted_at` | Timestamp | Nullable | Soft delete timestamp |

---

### 2.6. Tabel `murid`
Menyimpan profil khusus pengguna ber-role Siswa / Murid.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID murid |
| `user_id` | BigInt (Unsigned) | Foreign Key -> `users(id)` ON DELETE CASCADE | Relasi ke akun user |
| `kelas_id` | BigInt (Unsigned) | Foreign Key -> `kelas(id)` ON DELETE CASCADE | Relasi ke kelas |
| `nis` | Varchar(50) | Unique, Not Null | Nomor Induk Siswa |
| `nama` | Varchar(255) | Not Null | Nama lengkap murid |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |
| `deleted_at` | Timestamp | Nullable | Soft delete timestamp |

---

### 2.7. Tabel `dudi`
Menyimpan master data Perusahaan / Dunia Usaha & Dunia Industri mitra PKL.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID DUDI |
| `nama` | Varchar(255) | Not Null | Nama perusahaan/instansi |
| `alamat` | Text | Nullable | Alamat lengkap |
| `latitude` | Double | Not Null | Koordinat lintang lokasi DUDI |
| `longitude` | Double | Not Null | Koordinat bujur lokasi DUDI |
| `radius_meter` | Int | Default: 50 | Radius aman presensi (meter) |
| `pic_nama` | Varchar(255) | Nullable | Nama Person in Charge (PIC) |
| `pic_phone` | Varchar(20) | Nullable | Nomor kontak PIC |
| `hari_kerja` | Varchar(255) | Default: 'Senin,Selasa,Rabu,Kamis,Jumat' | Hari operasional magang |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |
| `deleted_at` | Timestamp | Nullable | Soft delete timestamp |

---

### 2.8. Tabel `pembimbing_industri`
Menyimpan data instruktur / pembimbing dari pihak industri (DUDI).

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID pembimbing industri |
| `dudi_id` | BigInt (Unsigned) | Foreign Key -> `dudi(id)` ON DELETE CASCADE | Relasi ke perusahaan DUDI |
| `nama` | Varchar(255) | Not Null | Nama pembimbing industri |
| `phone` | Varchar(20) | Nullable | Nomor kontak pembimbing |
| `email` | Varchar(100) | Nullable | Email pembimbing industri |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |
| `deleted_at` | Timestamp | Nullable | Soft delete timestamp |

---

### 2.9. Tabel `penempatan_pkl`
Tabel relasi utama yang menghubungkan siswa dengan DUDI, Guru, dan Pembimbing Industri.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID penempatan |
| `murid_id` | BigInt (Unsigned) | Foreign Key -> `murid(id)` ON DELETE CASCADE | Relasi ke murid |
| `dudi_id` | BigInt (Unsigned) | Foreign Key -> `dudi(id)` ON DELETE CASCADE | Relasi ke DUDI |
| `guru_id` | BigInt (Unsigned) | Foreign Key -> `guru(id)` ON DELETE CASCADE | Relasi ke guru pembimbing |
| `pembimbing_industri_id` | BigInt (Unsigned) | Foreign Key -> `pembimbing_industri(id)` ON DELETE SET NULL | Relasi ke pembimbing DUDI |
| `tahun_ajaran_id` | BigInt (Unsigned) | Foreign Key -> `tahun_ajaran(id)` ON DELETE CASCADE | Periode tahun ajaran |
| `tanggal_mulai` | Date | Not Null | Tanggal awal pelaksanaan PKL |
| `tanggal_selesai` | Date | Not Null | Tanggal akhir pelaksanaan PKL |
| `status` | Enum | 'aktif', 'selesai', 'batal', Default: 'aktif' | Status penempatan |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |
| `deleted_at` | Timestamp | Nullable | Soft delete timestamp |

---

### 2.10. Tabel `presensi`
Menyimpan log presensi Check-In dan Check-Out harian siswa.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID presensi |
| `penempatan_pkl_id` | BigInt (Unsigned) | Foreign Key -> `penempatan_pkl(id)` ON DELETE CASCADE | Relasi ke penempatan PKL |
| `tanggal` | Date | Not Null | Tanggal presensi |
| `jam_masuk` | Time | Nullable | Waktu check-in masuk |
| `jam_pulang` | Time | Nullable | Waktu check-out pulang |
| `lat_masuk` / `lng_masuk` | Double | Nullable | Koordinat GPS saat check-in |
| `lat_pulang` / `lng_pulang` | Double | Nullable | Koordinat GPS saat check-out |
| `foto_masuk` | Varchar(255) | Nullable | Nama file foto selfie masuk |
| `foto_pulang` | Varchar(255) | Nullable | Nama file foto selfie pulang |
| `status_masuk` | Enum | 'tepat_waktu', 'terlambat', Nullable | Keterangan status masuk |
| `status_pulang` | Enum | 'pulang_cepat', 'tepat_waktu', Nullable | Keterangan status pulang |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |

* **Unique Index**: `['penempatan_pkl_id', 'tanggal']`

---

### 2.11. Tabel `izin_sakit`
Menyimpan data pengajuan ketidakhadiran siswa karena izin atau sakit.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID permohonan |
| `penempatan_pkl_id` | BigInt (Unsigned) | Foreign Key -> `penempatan_pkl(id)` ON DELETE CASCADE | Relasi ke penempatan |
| `tanggal_mulai` | Date | Not Null | Tanggal awal izin |
| `tanggal_selesai` | Date | Not Null | Tanggal akhir izin |
| `tipe` | Enum | 'izin', 'sakit' | Kategori ketidakhadiran |
| `alasan` | Text | Not Null | Narasi penjelasan alasan |
| `surat_pendukung` | Varchar(255) | Nullable | Path file foto surat bukti (JPG/PNG) |
| `status_approval` | Enum | 'pending', 'disetujui', 'ditolak', Default: 'pending' | Status persetujuan guru |
| `catatan_guru` | Text | Nullable | Tanggapan / arahan revisi guru |
| `approved_by` | BigInt (Unsigned) | Foreign Key -> `guru(id)` ON DELETE SET NULL | Guru yang memproses |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |

---

### 2.12. Tabel `jurnal`
Menyimpan catatan kegiatan harian (logbook) siswa PKL.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID jurnal |
| `penempatan_pkl_id` | BigInt (Unsigned) | Foreign Key -> `penempatan_pkl(id)` ON DELETE CASCADE | Relasi ke penempatan |
| `tanggal` | Date | Not Null | Tanggal pelaksanaan jurnal |
| `deskripsi_aktivitas` | Text | Not Null | Rincian kegiatan/pekerjaan |
| `foto_kegiatan` | Varchar(255) | Nullable | Foto dokumentasi kegiatan |
| `status_verifikasi` | Enum | 'pending', 'disetujui', 'ditolak', Default: 'pending' | Status verifikasi |
| `catatan_verifikasi` | Text | Nullable | Catatan koreksi dari peninjau |
| `verified_by` | BigInt (Unsigned) | Foreign Key -> `guru(id)` ON DELETE SET NULL | Pengverifikasi jurnal |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |

---

### 2.13. Tabel `kunjungan_monitoring`
Menyimpan riwayat agenda kunjungan Guru Pembimbing ke lokasi DUDI.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID kunjungan |
| `penempatan_pkl_id` | BigInt (Unsigned) | Foreign Key -> `penempatan_pkl(id)` ON DELETE CASCADE | Relasi penempatan sasaran |
| `tanggal` | Date | Not Null | Tanggal kunjungan |
| `jenis_kunjungan` | Varchar(255) | Default: 'Monitoring Berkala' | Opsi: Penjajakan Kerja Sama, Penyerahan Murid, Monitoring Berkala, Penarikan PKL |
| `deskripsi_kunjungan` | Text | Not Null | Catatan hasil diskusi kunjungan |
| `foto_kunjungan` | Varchar(255) | Nullable | Foto bukti kunjungan di DUDI |
| `latitude` / `longitude` | Double | Nullable | Koordinat opsional |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |

---

### 2.14. Tabel `tujuan_pembelajaran`
Menyimpan master Tujuan Pembelajaran (TP) kurikulum sekolah.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID Tujuan Pembelajaran |
| `nomor` | Int | Not Null | Nomor urut TP (misal: 1, 2, 3) |
| `nama` | Varchar(255) | Not Null | Deskripsi singkat TP |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |

---

### 2.15. Tabel `indikator_penilaian`
Menyimpan indikator turunan spesifik per Tujuan Pembelajaran (TP).

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID Indikator |
| `tujuan_pembelajaran_id` | BigInt (Unsigned) | Foreign Key -> `tujuan_pembelajaran(id)` ON DELETE CASCADE | Relasi ke TP induk |
| `nomor_urut` | Varchar(10) | Not Null | Kode nomor (misal: '1.1', '3.7') |
| `nama` | Varchar(255) | Not Null | Nama kriteria indikator |
| `deskripsi` | Text | Nullable | Penjelasan detail indikator |
| `tipe` | Enum | 'guru', 'industri', Default: 'guru' | Pihak yang menilai |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |

---

### 2.16. Tabel `penilaian_pkl`
Menyimpan skor akhir evaluasi dan detail catatan rapor siswa.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID Penilaian |
| `penempatan_pkl_id` | BigInt (Unsigned) | Foreign Key -> `penempatan_pkl(id)` ON DELETE CASCADE | Relasi penempatan siswa |
| `nilai_guru_json` | JSON | Nullable | Rincian skor per indikator guru |
| `nilai_industri_json` | JSON | Nullable | Rincian skor per indikator industri |
| `rata_nilai_guru` | Double | Default: 0 | Nilai rata-rata aspek sekolah |
| `rata_nilai_industri` | Double | Default: 0 | Nilai rata-rata aspek industri |
| `nilai_akhir` | Double | Default: 0 | Nilai gabungan sesuai bobot (%) |
| `catatan` | Text | Nullable | Catatan umum selama PKL |
| `keterangan_tp_json` | JSON | Nullable | Narasi evaluasi per ID TP |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |

---

### 2.17. Tabel `pengumuman` & `pengumuman_penerima`
Menyimpan berita / informasi pengumuman dari Admin ke peran lain.

* **`pengumuman`**: `id`, `judul`, `isi`, `penulis_id` (FK -> users), `created_at`, `updated_at`.
* **`pengumuman_penerima`**: `id`, `pengumuman_id` (FK -> pengumuman), `user_id` (FK -> users), `is_read` (Boolean), `read_at` (Timestamp).

---

### 2.18. Tabel `settings`
Menyimpan konfigurasi parameter sistem berformat Key-Value.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID Setting |
| `key` | Varchar(255) | Unique, Not Null | Kunci parameter (contoh: `nama_sekolah`, `bobot_nilai_guru`) |
| `value` | LongText | Nullable | Nilai parameter |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |

---

### 2.19. Tabel `audit_logs`
Menyimpan rekam jejak aktivitas penting pengguna di dalam aplikasi.

| Nama Kolom | Tipe Data | Atribut / Constraint | Keterangan |
|---|---|---|---|
| `id` | BigInt (Unsigned) | Primary Key, Auto Increment | ID Log |
| `user_id` | BigInt (Unsigned) | Foreign Key -> `users(id)` ON DELETE SET NULL | Pengguna pemrakarsa |
| `aktivitas` | Varchar(255) | Not Null | Ringkasan aksi (misal: 'Login Sukses') |
| `ip_address` | Varchar(45) | Nullable | IP Address pengakses |
| `user_agent` | Text | Nullable | Browser / Device User-Agent |
| `payload` | JSON | Nullable | Data tambahan dalam format JSON |
| `created_at` / `updated_at` | Timestamp | Nullable | Timestamp |
