# Dokumentasi API PKLku

Dokumentasi ini menjelaskan rute API dan integrasi web service yang tersedia dalam aplikasi **PKLku**.

---

## 🔐 Autentikasi Pengguna

Seluruh rute selain login menggunakan session guard Laravel. Untuk integrasi eksternal atau aplikasi mobile, aplikasi dapat dikonfigurasi untuk mendukung Laravel Sanctum.

---

## 📍 Presensi API (Siswa/Murid)

### 1. Check-In (Absensi Masuk)
Mencatat kehadiran masuk siswa disertai foto selfie dan deteksi lokasi geofence.

* **URL**: `/presensi/check-in`
* **Method**: `POST`
* **Headers**:
  * `Content-Type: application/json`
  * `X-CSRF-TOKEN: [csrf_token]`
* **Payload**:
  ```json
  {
    "penempatan_pkl_id": 1,
    "latitude": -6.892345,
    "longitude": 110.982342,
    "photo": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
  }
  ```
* **Responses**:
  * **200 OK**:
    ```json
    {
      "success": true,
      "message": "Check In berhasil dicatat!"
    }
    ```
  * **422 Unprocessable Entity**:
    ```json
    {
      "success": false,
      "message": "Presensi gagal! Anda berada di luar radius DUDI (120 meter dari target)."
    }
    ```

### 2. Check-Out (Absensi Pulang)
Mencatat jam pulang siswa disertai foto selfie dan verifikasi koordinat lokasi.

* **URL**: `/presensi/check-out`
* **Method**: `POST`
* **Payload**:
  ```json
  {
    "penempatan_pkl_id": 1,
    "latitude": -6.892340,
    "longitude": 110.982348,
    "photo": "data:image/jpeg;base64,/9j/4AAQSkZJRg..."
  }
  ```
* **Responses**:
  * **200 OK**:
    ```json
    {
      "success": true,
      "message": "Check Out berhasil dicatat!"
    }
    ```

---

## 📊 Penilaian & Ekspor API (Admin / Guru)

### 1. Ekspor Template Nilai (Excel)
Mengunduh lembar spreadsheet penilaian dalam format `.xlsx`.

* **URL**: `/penilaian/export`
* **Method**: `GET`
* **Query Parameters**:
  * `role`: `guru` atau `admin` (opsional)

### 2. Impor Nilai Siswa (Excel)
Mengunggah lembar spreadsheet penilaian untuk disimpan secara massal.

* **URL**: `/penilaian/import`
* **Method**: `POST`
* **Payload**: Form-Data berisi file berkas `.xlsx`.
