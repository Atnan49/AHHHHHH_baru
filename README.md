# Sistem Pembayaran Sekolah

Website pembayaran sekolah dengan fitur upload bukti transfer dan verifikasi admin.

## Fitur Utama
- 📝 Pendaftaran siswa baru
- 💰 Upload bukti pembayaran
- ✅ Verifikasi pembayaran oleh admin
- 📊 Dashboard monitoring
- 👥 Manajemen data siswa

## Persyaratan Sistem
- PHP 7.4 atau lebih tinggi
- MySQL 8.0 atau lebih tinggi
- Web server (Apache/Nginx)

## Instalasi
1. Clone atau download proyek ini
2. Import database dari file `database/school_payment.sql`
3. Konfigurasi database di `config/database.php`
4. Akses melalui web browser

## Default Login
- **Admin:** 
  - Username: admin
  - Password: admin123
- **Siswa:** Daftar terlebih dahulu

## Struktur Folder
```
├── config/          # Konfigurasi database
├── admin/           # Panel admin
├── student/         # Panel siswa
├── assets/          # CSS, JS, Images
├── uploads/         # File upload bukti transfer
├── database/        # File SQL database
└── includes/        # File include umum
```

## Teknologi
- **Backend:** PHP, MySQL
- **Frontend:** Bootstrap 5, JavaScript
- **Security:** Session management, prepared statements
