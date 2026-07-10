# Panduan Deployment Produksi (Web Hosting)

Panduan ini menjelaskan langkah-langkah detail untuk memindahkan aplikasi **PKLku** dari server lokal ke shared hosting (cPanel), VPS, atau hosting khusus lainnya.

---

## 📋 Langkah-Langkah Deployment cPanel (Shared Hosting)

### Langkah 1: Ekspor Database Lokal
1. Ekspor database lokal Anda ke format `.sql`. Berkas database default siap pakai sudah disediakan di root proyek Anda: `pkl_smk_antigravity.sql`.

### Langkah 2: Persiapkan File Proyek
1. Kompres seluruh file proyek Anda menjadi format `.zip` **KECUALI** folder `node_modules/` dan folder `storage/app/public/` (karena folder storage publik akan dihubungkan secara dinamis melalui symlink di hosting).

### Langkah 3: Unggah File ke Hosting
1. Masuk ke cPanel hosting Anda, lalu buka **File Manager**.
2. Unggah file `.zip` proyek ke direktori luar `public_html` (Sangat disarankan demi keamanan kode program, agar folder `app`, `config`, `.env`, dll. tidak bisa diakses langsung publik).
   * Contoh struktur di hosting:
     ```
     /home/username/pklku_source/ (Isi zip proyek diekstrak di sini)
     /home/username/public_html/ (Isi folder public proyek dipindahkan ke sini)
     ```
3. Ekstrak file `.zip` tersebut di folder `/home/username/pklku_source/`.
4. Pindahkan semua file yang berada di dalam folder `pklku_source/public/` ke folder `/home/username/public_html/` (atau direktori root domain/subdomain Anda).
5. Edit file `index.php` yang berada di dalam folder `/home/username/public_html/` untuk menyesuaikan autoload dan bootstrap path:
   * Ubah baris 34:
     ```php
     require __DIR__.'/../vendor/autoload.php';
     ```
     Menjadi:
     ```php
     require __DIR__.'/../pklku_source/vendor/autoload.php';
     ```
   * Ubah baris 47:
     ```php
     $app = require_once __DIR__.'/../bootstrap/app.php';
     ```
     Menjadi:
     ```php
     $app = require_once __DIR__.'/../pklku_source/bootstrap/app.php';
     ```

### Langkah 4: Konfigurasi Database & File .env di Hosting
1. Masuk ke cPanel -> **MySQL Database Wizard**, buat database baru, buat user baru, dan hubungkan user tersebut ke database dengan hak akses penuh (*All Privileges*).
2. Masuk ke **phpMyAdmin** di cPanel, pilih database baru yang telah dibuat, lalu pilih menu **Import** dan unggah berkas `pkl_smk_antigravity.sql`.
3. Buka file `.env` di folder `/home/username/pklku_source/` menggunakan text editor cPanel, ubah konfigurasinya menjadi mode produksi:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://nama-domain-anda.com
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=nama_database_hosting_anda
   DB_USERNAME=username_database_hosting_anda
   DB_PASSWORD=password_database_hosting_anda
   ```

### Langkah 5: Hubungkan Storage Symlink di Hosting
Aplikasi membutuhkan symbolic link agar foto selfie siswa yang diunggah ke storage internal bisa ditampilkan ke publik.
* **Metode 1 (Terminal cPanel)**: Jika hosting Anda menyediakan akses Terminal/SSH, masuk ke folder `pklku_source` lalu jalankan:
  ```bash
  php artisan storage:link
  ```
* **Metode 2 (Cron Job)**: Jika tidak ada SSH, masuk ke menu **Cron Jobs** di cPanel, buat tugas baru yang berjalan sekali saja dengan perintah:
  ```bash
  ln -s /home/username/pklku_source/storage/app/public /home/username/public_html/storage
  ```
* **Metode 3 (Temporary Route)**: Buka file `pklku_source/routes/web.php` dan tambahkan route sementara berikut:
  ```php
  Route::get('/run-symlink', function () {
      \Illuminate\Support\Facades\Artisan::call('storage:link');
      return 'Storage Symlink Created Successfully!';
  });
  ```
  Akses URL `https://nama-domain-anda.com/run-symlink` di browser sekali, lalu hapus kembali rute tersebut demi keamanan.

---

## 🛠️ Optimasi Produksi (Production Optimization)

Jalankan perintah pengoptimalan berikut (lewat SSH jika tersedia) demi meningkatkan kecepatan loading aplikasi di server produksi:

```bash
# Optimasi konfigurasi
php artisan config:cache

# Optimasi rute/routing
php artisan route:cache

# Optimasi template Blade
php artisan view:cache
```

Jika ada pembaruan kode di kemudian hari, jalankan `php artisan optimize:clear` terlebih dahulu sebelum mengunggah kode baru, lalu ulangi perintah optimasi di atas.
