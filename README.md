# Petal - Time Capsule & Future Message Web Application

Petal adalah aplikasi web kapsul waktu interaktif berbasis PHP dan MySQL yang dirancang untuk menyimpan dan menjadwalkan pengiriman pesan masa depan. Pengguna dapat menulis pesan privat yang akan dikirim secara otomatis ke email penerima pada tanggal tertentu, atau menulis pesan publik yang akan terpampang selamanya di dinding pesan dunia (*Public Wall*).

Aplikasi ini dibangun khusus untuk memenuhi persyaratan tugas proyek akhir **Praktikum Pemrograman Basis Data (PBD) - Universitas Gadjah Mada (UGM)**.

---

## Fitur Utama

- **Hero & Landing Page Interaktif**: Menampilkan 5 kartu pilihan memo warna interaktif dengan desain minimalis modern dan statistik pesan real-time.
- **Formulis Tambah Data (Write)**:
  - Form HTML interaktif untuk membuat surat baru (Privat / Publik).
  - Validasi JavaScript sisi klien sebelum pengiriman data.
- **Dinding Pesan Publik (Public Wall)**:
  - Menampilkan semua pesan publik dalam bentuk *Grid Card* Bootstrap berwarna solid yang estetik.
  - Dilengkapi fitur pencarian kata kunci dan paginasi data secara dinamis.
- **Autentikasi Admin & Proteksi Halaman**:
  - Halaman login admin terlindungi dengan session (`session_start()`).
  - Penyimpanan kata sandi terenkripsi menggunakan `password_hash()` dan verifikasi menggunakan `password_verify()`.
- **Dashboard Manajemen Admin (CRUD)**:
  - Manajemen surat privat & publik dalam bentuk tabel responsif (bisa digeser/*swipe* di perangkat mobile).
  - Integrasi fitur **Send Now** untuk mengirim email langsung melalui SMTP PHP Mailer/Mailtrap.
  - **System Activity Logs**: Log otomatis yang mencatat aksi penghapusan pesan oleh admin menggunakan database trigger (`admin_logs`).
- **Desain Responsif**: Dioptimalkan secara penuh untuk kenyamanan akses pada perangkat mobile (minimal resolusi 375px) hingga layar desktop (1440px).

---

## Spesifikasi Teknologi

- **Sisi Klien**: HTML5, Vanilla CSS, Bootstrap 5.3, Bootstrap Icons CDN
- **Sisi Server**: PHP (Native)
- **Basis Data**: MySQL (MariaDB)
- **SMTP**: PHP Mailer & Mailtrap (untuk pengujian simulasi email)

---

## Struktur Folder Proyek

```text
petal/
├── assets/
│   ├── css/
│   │   └── style.css     # Custom stylesheet & media queries
│   └── js/
│       └── main.js       # JS client validations & DOM scripts
├── includes/
│   ├── config.php        # Konfigurasi database MySQL
│   ├── header.php        # Header & Floating Navbar responsif
│   └── footer.php        # Footer layout
├── pages/
│   ├── admin/            # Panel administrasi CRUD
│   │   ├── dashboard.php # Dashboard admin & log aktivitas
│   │   ├── settings.php  # Pengaturan profil & kata sandi admin
│   │   ├── login.php     # Halaman masuk admin
│   │   ├── logout.php    # Sesi keluar
│   │   ├── delete.php    # Aksi hapus data
│   │   └── send-now.php  # Pemicu SMTP email manual
│   ├── public-wall.php   # Dinding pesan publik
│   └── write.php         # Form input tambah pesan
├── database.sql          # Skema basis data lengkap
├── README.md             # Dokumentasi proyek
└── .gitignore            # Pengabaian git file sensitif
```

---

## Cara Menjalankan Aplikasi

### 1. Persiapan Server Lokal
1. Pastikan Anda sudah menginstal web server lokal seperti **Laragon** atau **XAMPP**.
2. Salin folder proyek `petal` ke dalam direktori server lokal Anda:
   - Jika Laragon: `C:\laragon\www\petal`
   - Jika XAMPP: `C:\xampp\htdocs\petal`
3. Jalankan Apache dan MySQL pada panel kontrol Laragon/XAMPP.

### 2. Import Basis Data
1. Buka browser dan akses **phpMyAdmin** (`http://localhost/phpmyadmin` atau `http://localhost:8080/phpmyadmin`).
2. Buat database baru dengan nama `petal_db`.
3. Pilih database `petal_db`, klik tab **Import**, lalu pilih file `database.sql` yang berada di dalam folder proyek Petal.
4. Klik **Import** (atau **Go**) untuk menjalankan skema database.

### 3. Konfigurasi Koneksi Database
1. Buka file [config.php](file:///C:/laragon/www/petal/includes/config.php) yang berada di dalam folder `includes/`.
2. Sesuaikan konfigurasi database dengan server lokal Anda:
   ```php
   $host = "localhost";
   $user = "root";       // Default user MySQL
   $pass = "";           // Default password MySQL (kosongkan jika Laragon/XAMPP)
   $db   = "petal_db";
   ```

### 4. Menjalankan Aplikasi
1. Buka browser dan ketik alamat berikut:
   `http://localhost/petal/` atau `http://localhost:8080/petal/` (tergantung port Apache Anda).
2. Untuk masuk ke dashboard admin, akses:
   `http://localhost/petal/pages/admin/login.php`
   - **Username Default**: `admin`
   - **Password Default**: `admin123`

---

## Tampilan UI Aplikasi

Berikut beberapa tangkapan layar antarmuka aplikasi Petal:

### 1. Halaman Beranda (Desktop)
![Homepage](assets/img/screenshot_home.png)

### 2. Halaman Dashboard Admin & Tabel Responsif (Mobile)
![Mobile Dashboard](assets/img/screenshot_mobile_dashboard.png)

---

*Proyek Akhir Praktikum Pemrograman Basis Data - Departemen Teknik Elektro dan Informatika Sekolah Vokasi UGM 2026.*
