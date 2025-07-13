-- Database untuk Sistem Pembayaran Sekolah
CREATE DATABASE IF NOT EXISTS school_payment CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE school_payment;

-- Tabel users untuk login admin dan siswa
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel students untuk data siswa
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    nis VARCHAR(20) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    kelas VARCHAR(10) NOT NULL,
    alamat TEXT,
    nomor_hp VARCHAR(15),
    nama_wali VARCHAR(100),
    nomor_hp_wali VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel jenis pembayaran
CREATE TABLE payment_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_pembayaran VARCHAR(100) NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    deskripsi TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel payments untuk record pembayaran
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    payment_type_id INT NOT NULL,
    jumlah_bayar DECIMAL(10,2) NOT NULL,
    tanggal_bayar DATE NOT NULL,
    metode_pembayaran VARCHAR(50) DEFAULT 'Transfer Bank',
    status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    keterangan TEXT,
    verified_by INT NULL,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_type_id) REFERENCES payment_types(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
);

-- Tabel payment_proofs untuk bukti transfer
CREATE TABLE payment_proofs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    payment_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT,
    mime_type VARCHAR(100),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE
);

-- Insert data default admin
INSERT INTO users (username, password, role, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@sekolah.com');
-- Password default: admin123

-- Insert jenis pembayaran default
INSERT INTO payment_types (nama_pembayaran, jumlah, deskripsi) VALUES
('SPP Bulanan', 300000.00, 'Pembayaran SPP per bulan'),
('Uang Gedung', 5000000.00, 'Pembayaran uang gedung untuk siswa baru'),
('Uang Seragam', 750000.00, 'Pembayaran seragam sekolah'),
('Uang Buku', 500000.00, 'Pembayaran buku pelajaran'),
('Uang Kegiatan', 250000.00, 'Pembayaran kegiatan ekstrakurikuler');

-- Indexes untuk performa
CREATE INDEX idx_students_nis ON students(nis);
CREATE INDEX idx_payments_student ON payments(student_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_date ON payments(tanggal_bayar);
