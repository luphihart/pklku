# PKLku

Platform modern manajemen, presensi geofence, jurnal harian, dan penilaian Praktik Kerja Lapangan (PKL) SMK berbasis **Laravel 12**.

🌐 **Demo Online**: [https://pklku.sinaumedia.my.id](https://pklku.sinaumedia.my.id)  
📦 **Repository**: [https://github.com/luphihart/pklku.git](https://github.com/luphihart/pklku.git)

---

## 🚀 Fitur Utama

- 📍 **Presensi GPS & Selfie**: Geofence radius akurat (Haversine), foto selfie kamera langsung, deteksi WFO/WFA/Hybrid, serta dukungan multi-shift (Pagi, Siang, Sore, Rolling).
- 📝 **Jurnal Harian 5W+1H**: Siswa mengunggah laporan kerja harian dan foto bukti, diverifikasi langsung oleh guru pembimbing dengan catatan revisi.
- 📋 **Pengajuan Izin & Sakit**: Alur pengajuan digital dengan bukti surat dokter/orang tua dan persetujuan guru/admin.
- 🗺️ **Monitoring & Kunjungan DUDI**: Peta sebaran lokasi industri (Leaflet.js), pencatatan kunjungan guru, dan cetak lembar SPPD PDF.
- 🎓 **Penilaian & Rapor PKL**: Input nilai aspek sekolah dan industri, evaluasi Tujuan Pembelajaran (TP), dan cetak Rapor Nilai PDF resmi standar kurikulum vokasi.
- 📅 **Kalender Libur & Auto-Absent**: Sinkronisasi 1-klik hari libur nasional resmi. Cron otomatis mencatat Alpha tanpa salah menandai tanggal merah atau libur shift.
- 📊 **Ekspor Laporan**: Rekapitulasi presensi, jurnal, penempatan, dan nilai dalam format Excel (.xlsx) dan PDF.

---

## 👥 Role & Hak Akses

| Peran | Akses Utama |
|---|---|
| **Administrator** | Manajemen master data (siswa, guru, DUDI, tahun ajaran, libur), plotting penempatan massal, pengaturan aplikasi, rekapitulasi, dan audit log. |
| **Guru Pembimbing** | Monitoring siswa bimbingan, verifikasi jurnal & izin, input laporan kunjungan/SPPD, input nilai sekolah, dan pengesahan rapor. |
| **Siswa (Murid)** | Presensi GPS & selfie datang/pulang, pengajuan izin/sakit, penulisan jurnal harian, input nilai lembar DUDI, dan unduh rapor PDF. |

---

## 🛠️ Tech Stack

- **Backend**: Laravel 12.x, PHP >= 8.2
- **Database**: MySQL / MariaDB
- **Frontend**: Blade, Bootstrap 5, Vanilla JS, Leaflet.js
- **Laporan & Dokumen**: Dompdf (PDF), Maatwebsite (Excel)
- **Asset Bundler**: Vite

---

## 📂 Struktur Modul

Proyek menggunakan pola **Modular Monolith** di dalam `app/Modules/`:

```
app/Modules/
├── Auth/         # Autentikasi multi-role & profil
├── Dashboard/    # Dashboard analitik & ringkasan aktivitas
├── MasterData/   # Tahun Ajaran, Kelas, Jurusan, Guru, Murid, DUDI, Libur
├── PKL/          # Plotting Penempatan & Kunjungan SPPD
├── Presensi/     # Presensi GPS/Selfie, Shift, Izin & Sakit
├── Jurnal/       # Jurnal harian 5W+1H & verifikasi guru
├── Penilaian/    # Indikator, Capaian TP, Rapor Nilai PKL PDF
├── Laporan/      # Rekapitulasi Excel & PDF (Presensi, Jurnal, Nilai)
├── Monitoring/   # Pemantauan penempatan & DUDI
├── Pengumuman/   # Pengumuman terarah sekolah
├── Setting/      # Konfigurasi sekolah, jam kerja, bobot nilai
└── System/       # Audit log & utilitas sistem
```

---

## 💻 Instalasi Lokal

```bash
# 1. Clone repositori
git clone https://github.com/luphihart/pklku.git
cd pklku

# 2. Pasang dependensi
composer install
npm install

# 3. Setup environment & app key
cp .env.example .env
php artisan key:generate

# 4. Atur database di file .env lalu migrasi & seeding
php artisan migrate --seed
php artisan storage:link

# 5. Kompilasi asset & jalankan server
npm run build
php artisan serve
```

Akses lokal di: `http://localhost:8000`  
**Login Default Admin**: Email: `admin@gmail.com` | Password: `password`

---

## 🌐 Panduan Deployment cPanel

1. **Struktur Direktori**:
   - Letakkan source code core di luar webroot: `/home/username/pklku/`
   - Letakkan file public webroot di folder subdomain: `/home/username/public_html/pklku.domain.com/`
2. **Penyesuaian `index.php` di Webroot**:
   ```php
   require __DIR__.'/../../pklku/vendor/autoload.php';
   $app = require_once __DIR__.'/../../pklku/bootstrap/app.php';
   ```
3. **Storage Symlink**:
   ```bash
   ln -s /home/username/pklku/storage/app/public /home/username/public_html/pklku.domain.com/storage
   ```
4. **Cron Job Otomatisasi Harian (18:00 WIB)**:
   ```bash
   0 18 * * * cd /home/username/pklku && php artisan presensi:auto-absent >> /dev/null 2>&1
   ```

---

## 📄 Lisensi

Proyek ini dirilis di bawah lisensi [MIT](LICENSE).
