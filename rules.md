# Development Guidelines & Rules - PKLku

Dokumen ini berisi standar pengkodean, arsitektur sistem, aturan pengembangan, serta prinsip teknis yang harus dipatuhi oleh setiap pengembang (*developer* / agentic AI) yang berkontribusi pada proyek **PKLku**.

---

## 1. Arsitektur Sistem & Struktur Kode

### 1.1. Modular Monolith Architecture
Proyek ini mengadopsi arsitektur **Modular Monolith**. Setiap fitur utama dikelompokkan ke dalam modul independen yang berada di bawah direktori `app/Modules/`.

* **Struktur Modul Standar**:
  ```
  app/Modules/<ModuleName>/
  ├── Controllers/         # Controller penanganan HTTP request
  ├── Database/
  │   ├── Migrations/      # File migrasi spesifik modul
  │   └── Seeders/         # Data seeder spesifik modul
  ├── Models/              # Eloquent Model
  ├── Repositories/        # Layer repositori / akses database (opsional)
  ├── Services/            # Business logic layer
  ├── Routes/
  │   └── web.php          # Route khusus modul
  └── Views/               # Blade templates khusus modul
  ```
* **Penyatuan Modul**: Setiap modul mendaftarkan ServiceProvider dan Route-nya secara terisolasi. Jangan mencampur logic modul satu ke modul lainnya secara berantakan.

---

## 2. Standar Teknologi & Framework

1. **Backend Framework**: Laravel 11.x (PHP >= 8.2).
2. **Frontend UI / Styling**:
   * Gunakan **Vanilla CSS** dan **Bootstrap 5**.
   * **Dilarang menggunakan TailwindCSS** kecuali diminta secara eksplisit oleh pengguna.
   * Gunakan font **Inter** atau **Outfit** untuk mempertahankan estetika premium modern.
3. **Interaktivitas Frontend**:
   * Gunakan **Alpine.js** untuk manajemen state sederhana di sisi klien (seperti toggle sidebar, modal dinamis, atau handler camera canvas).
   * Gunakan **Leaflet.js** untuk integrasi peta interaktif & geofencing.
4. **PDF & Excel Processing**:
   * **Barryvdh DomPDF** (`Barryvdh\DomPDF\Facade\Pdf`) untuk laporan PDF.
   * **Maatwebsite Excel** / **PhpSpreadsheet** untuk impor/ekspor berkas spreadsheet.

---

## 3. Aturan Pengkodean (Coding Standards)

### 3.1. PHP & Laravel Conventions
* Ikuti standar pengkodean **PSR-12**.
* **Type Hinting & Return Types**: Deklarasikan parameter tipe data dan return type pada method secara jelas.
* **Logic Isolation**: Controller hanya bertugas menerima request, memanggil Service/Repository, dan mengembalikan response (View/JSON/Redirect). Dilarang menulis business logic kompleks di dalam Controller atau Blade view.

### 3.2. Penanganan File & Kompresi Gambar
* **Public Storage Path**: Seluruh file publik (foto presensi, profil, jurnal, surat izin) disimpan di `public_path('storage/<folder>/')` untuk menjamin kompatibilitas lingkungan hosting cPanel / Shared Hosting.
* **Auto Compression**:
  * Setiap proses unggah foto (Presensi, Profil, Izin/Sakit) **wajib melalui fungsi kompresi** (menggunakan Intervention Image jika tersedia, atau fallback `compressImageNative` berbasis GD).
  * Batasi lebar maksimal gambar (contoh: Presensi max 640px, Izin/Sakit max 800px, Profil max 400px) dan gunakan kualitas kompresi JPEG 75-80%.
* **Prinsip Foto Presensi Selfie**:
  * Preview kamera di layar murid harus di-mirror menggunakan CSS (`transform: scaleX(-1)`).
  * Foto yang disimpan ke server **tidak boleh di-mirror** (harus orientasi asli) agar teks/identitas pada latar belakang dapat terbaca jelas.

### 3.3. Penanganan PDF (Dompdf Specifics)
* **Base64 Image Encoding**: Saat merender gambar/logo/foto bukti di dalam template PDF Dompdf, selalu gunakan skema **Base64 Data URI** (`data:image/jpg;base64,...`) atau file path absolut server untuk mencegah kegagalan pemuatan gambar/tanda silang merah.
* **Null-Safety pada Template PDF**: Pastikan semua relasi data (`$placement->guru`, `$placement->pembimbingIndustri`, dll) dipanggil dengan pengecekan `optional()` atau ternari (`? :`) agar tidak menyebabkan error crash saat data relasi belum di-plotting.
* **Font Styling PDF**: Gunakan `font-weight: bold` daripada angka seperti `font-weight: 600` agar Dompdf dapat mendeteksi bobot font tebal secara konsisten tanpa berubah menjadi font Serif bawaan.

### 3.4. Keamanan & Akses Kontrol
* **Role Verification**: Proteksi seluruh route menggunakan middleware `auth` dan `role:<role1>,<role2>`.
* **Admin-Only Destructive Actions**: Aksi destruktif seperti hapus nilai (`penilaian.destroy`), hapus penempatan massal, atau reset database **wajib dibatasi hanya untuk role `admin`**.
* **Input Validation**: Validasi seluruh input di sisi server secara ketat. Khusus lampiran izin/sakit, batasi mime type hanya untuk `jpeg,png,jpg` (tolak format `.pdf`).

---

## 4. Aturan Pengujian & Verifikasi (Testing Rules)

1. **Jalankan Automated Tests**: Setiap kali melakukan perubahan fitur utama atau refactoring, pengembang **wajib menjalankan unit/feature test**:
   ```bash
   php artisan test
   ```
2. **Prinsip No Regression**: Pastikan seluruh test case (seperti `NilaiExcelTest`, test presensi, export PDF, dll) bernilai **PASS (21+ passed)** sebelum menyatakan pekerjaan selesai.
3. **Penyebab Failure**: Jika ada test yang gagal akibat perubahan skema (misal penambahan kolom wajib), perbarui test data seeder/factory terkait secara tepat, jangan pernah menghapus unit test yang gagal.

---

## 5. Deployment & Perubahan Database

1. **Migrasi Database**:
   * Gunakan file migrasi resmi Laravel untuk setiap perubahan struktur tabel.
   * Dilarang mengubah skema database secara langsung di DBMS tanpa membuat file migrasi.
2. **Panduan cPanel/Production**:
   * Dokumentasikan setiap file baru atau file yang diubah dalam tabel ringkasan di akhir tanggapan untuk memudahkan pengguna mengunggahnya ke server produksi cPanel.
