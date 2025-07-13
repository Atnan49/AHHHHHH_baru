# Sistem Pembayaran Sekolah - Panduan Lengkap

## Overview
Sistem Pembayaran Sekolah adalah aplikasi web berbasis PHP yang memungkinkan siswa untuk mengunggah bukti pembayaran dan admin untuk memverifikasi pembayaran tersebut.

## Fitur Utama

### Untuk Admin:
- **Dashboard Admin**: Melihat statistik pembayaran, siswa, dan pendapatan
- **Manajemen Siswa**: Melihat, menambah, mengedit, dan menghapus data siswa
- **Verifikasi Pembayaran**: Menerima atau menolak bukti pembayaran dari siswa
- **Manajemen Jenis Pembayaran**: Mengelola berbagai jenis pembayaran sekolah
- **Laporan**: Melihat laporan pembayaran dan statistik

### Untuk Siswa:
- **Dashboard Siswa**: Melihat status pembayaran dan informasi pribadi
- **Upload Bukti Bayar**: Mengunggah bukti transfer pembayaran
- **Riwayat Pembayaran**: Melihat histori pembayaran yang telah dilakukan
- **Profil**: Mengelola informasi pribadi

## Teknologi yang Digunakan

### Backend:
- **PHP 7.4+**: Server-side scripting
- **MySQL 8.0+**: Database management
- **PDO**: Database connection dengan prepared statements

### Frontend:
- **Bootstrap 5**: CSS framework untuk UI responsif
- **Font Awesome 6**: Icon library
- **JavaScript (Vanilla)**: Client-side interactivity
- **Custom CSS**: Styling kustom untuk tampilan modern

### Keamanan:
- **Password Hashing**: Menggunakan PHP password_hash()
- **Prepared Statements**: Mencegah SQL injection
- **Session Management**: Autentikasi dan otorisasi user
- **CSRF Protection**: Token untuk mencegah CSRF attacks
- **Input Validation**: Sanitasi input dari user

## Struktur Proyek

```
e:\Web\AHHHHHH_baru\
├── .github/
│   └── copilot-instructions.md
├── .vscode/
│   └── tasks.json
├── admin/
│   ├── dashboard.php
│   ├── students.php
│   ├── payments.php
│   ├── payment-types.php
│   └── reports.php
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   └── icons.css
│   └── js/
│       └── main.js
├── config/
│   └── database.php
├── database/
│   └── school_payment.sql
├── includes/
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── student/
│   ├── dashboard.php
│   ├── payment-new.php
│   ├── payments.php
│   └── profile.php
├── uploads/
│   └── .htaccess
├── .htaccess
├── index.php
├── login.php
├── register.php
├── logout.php
└── README.md
```

## Database Schema

### Tabel Users
- `id` (INT, PK, AUTO_INCREMENT)
- `username` (VARCHAR)
- `password` (VARCHAR, hashed)
- `role` (ENUM: 'admin', 'student')
- `created_at` (TIMESTAMP)

### Tabel Students
- `id` (INT, PK, AUTO_INCREMENT)
- `user_id` (INT, FK ke users.id)
- `nama_lengkap` (VARCHAR)
- `nis` (VARCHAR, UNIQUE)
- `kelas` (VARCHAR)
- `email` (VARCHAR)
- `no_hp` (VARCHAR)
- `alamat` (TEXT)
- `created_at` (TIMESTAMP)

### Tabel Payment_Types
- `id` (INT, PK, AUTO_INCREMENT)
- `nama_pembayaran` (VARCHAR)
- `jumlah` (DECIMAL)
- `deskripsi` (TEXT)
- `is_active` (BOOLEAN)
- `created_at` (TIMESTAMP)

### Tabel Payments
- `id` (INT, PK, AUTO_INCREMENT)
- `student_id` (INT, FK ke students.id)
- `payment_type_id` (INT, FK ke payment_types.id)
- `jumlah_bayar` (DECIMAL)
- `tanggal_bayar` (DATE)
- `status` (ENUM: 'pending', 'verified', 'rejected')
- `catatan` (TEXT)
- `created_at` (TIMESTAMP)

### Tabel Payment_Proofs
- `id` (INT, PK, AUTO_INCREMENT)
- `payment_id` (INT, FK ke payments.id)
- `file_path` (VARCHAR)
- `file_name` (VARCHAR)
- `file_size` (INT)
- `uploaded_at` (TIMESTAMP)

## Cara Instalasi

### Prerequisites:
- PHP 7.4 atau lebih tinggi
- MySQL 8.0 atau lebih tinggi
- Web server (Apache/Nginx) atau PHP built-in server

### Langkah Instalasi:

1. **Clone atau Download Project**
   ```bash
   # Jika menggunakan Git
   git clone [repository-url]
   
   # Atau extract file ZIP ke folder web server
   ```

2. **Setup Database**
   ```sql
   # Buat database baru
   CREATE DATABASE school_payment;
   
   # Import database schema
   mysql -u root -p school_payment < database/school_payment.sql
   ```

3. **Konfigurasi Database**
   ```php
   // Edit file config/database.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'school_payment');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   ```

4. **Set Permissions**
   ```bash
   # Pastikan folder uploads dapat ditulis
   chmod 755 uploads/
   ```

5. **Start Server**
   ```bash
   # Menggunakan PHP built-in server
   cd e:\Web\AHHHHHH_baru
   php -S localhost:8000
   
   # Atau deploy ke web server Apache/Nginx
   ```

## Akun Default

### Admin:
- **Username**: admin
- **Password**: admin123

### Student:
Daftar siswa baru melalui halaman register atau gunakan data yang sudah ada di database.

## Cara Penggunaan

### Untuk Admin:

1. **Login**
   - Akses `http://localhost:8000/login.php`
   - Masukkan username: `admin` dan password: `admin123`

2. **Dashboard**
   - Melihat ringkasan statistik sistem
   - Monitoring pembayaran terbaru
   - Akses quick actions

3. **Mengelola Siswa**
   - Buka menu "Data Siswa"
   - Tambah, edit, atau hapus data siswa
   - Import data siswa dari file Excel (jika tersedia)

4. **Verifikasi Pembayaran**
   - Buka menu "Pembayaran"
   - Review bukti pembayaran yang diunggah siswa
   - Approve atau reject pembayaran
   - Tambah catatan jika diperlukan

5. **Mengelola Jenis Pembayaran**
   - Buka menu "Jenis Pembayaran"
   - Tambah jenis pembayaran baru (SPP, UTS, UAS, dll.)
   - Set jumlah dan deskripsi

### Untuk Siswa:

1. **Registrasi**
   - Akses `http://localhost:8000/register.php`
   - Isi data pribadi lengkap
   - Username dan password akan dibuat otomatis

2. **Login**
   - Gunakan username dan password yang diberikan saat registrasi

3. **Upload Bukti Pembayaran**
   - Buka menu "Pembayaran Baru"
   - Pilih jenis pembayaran
   - Upload bukti transfer (JPG, PNG, PDF max 5MB)
   - Isi informasi tambahan

4. **Monitoring Status**
   - Check status pembayaran di dashboard
   - Lihat riwayat pembayaran di menu "Riwayat"

## Fitur Keamanan

### Autentikasi:
- Password di-hash menggunakan algoritma bcrypt
- Session-based authentication
- Auto logout setelah periode inaktif

### Otorisasi:
- Role-based access control (Admin vs Student)
- Protected routes dengan function guards
- Permission checks pada setiap aksi

### Validasi:
- Server-side input validation
- Client-side form validation
- File upload restrictions
- SQL injection prevention

### File Security:
- Restricted file types untuk upload
- File size limitations
- Secure file naming
- Protected upload directory

## API Endpoints (untuk future development)

```php
// Authentication
POST /api/login
POST /api/logout
POST /api/register

// Students (Admin only)
GET    /api/students
POST   /api/students
PUT    /api/students/{id}
DELETE /api/students/{id}

// Payments
GET    /api/payments
POST   /api/payments
PUT    /api/payments/{id}/status

// Payment Types (Admin only)
GET    /api/payment-types
POST   /api/payment-types
PUT    /api/payment-types/{id}

// File Upload
POST   /api/upload-proof
```

## Troubleshooting

### Error Database Connection:
1. Check database credentials di `config/database.php`
2. Pastikan MySQL service running
3. Verify database dan tabel sudah dibuat

### File Upload Issues:
1. Check permissions folder `uploads/`
2. Verify PHP upload settings (`upload_max_filesize`, `post_max_size`)
3. Check file type dan size restrictions

### Session Problems:
1. Pastikan PHP session enabled
2. Check session path permissions
3. Clear browser cookies/storage

### Performance Issues:
1. Enable PHP OPcache
2. Optimize database queries
3. Implement caching untuk static assets

## Development Guidelines

### Coding Standards:
- Follow PSR-12 coding standard
- Use meaningful variable names
- Comment complex logic
- Implement error handling

### Security Best Practices:
- Always use prepared statements
- Validate all inputs
- Escape output data
- Implement CSRF protection
- Use HTTPS in production

### Database Best Practices:
- Use indexes for frequently queried columns
- Implement soft deletes for important data
- Regular database backups
- Monitor query performance

## Future Enhancements

### Phase 2 Features:
- [ ] Email notifications
- [ ] SMS notifications
- [ ] Payment gateway integration
- [ ] Mobile app (Flutter/React Native)
- [ ] Report generation (PDF)
- [ ] Bulk operations
- [ ] Advanced filtering dan search

### Phase 3 Features:
- [ ] Multi-school support
- [ ] API for third-party integrations
- [ ] Real-time notifications
- [ ] Advanced analytics
- [ ] Mobile-responsive PWA
- [ ] Offline capability

## Lisensi
Proyek ini menggunakan lisensi MIT. Anda bebas untuk menggunakan, memodifikasi, dan mendistribusikan sesuai kebutuhan.

## Support
Untuk bantuan teknis atau pertanyaan, silakan hubungi tim development atau buat issue di repository GitHub.

---

**Last Updated**: Desember 2024
**Version**: 1.0.0
**Author**: School Payment System Development Team
