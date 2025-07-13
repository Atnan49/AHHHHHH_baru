<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

# Sistem Pembayaran Sekolah

Ini adalah proyek website pembayaran sekolah dengan fitur:
- Manajemen data siswa
- Upload bukti transfer pembayaran
- Verifikasi pembayaran oleh admin
- Dashboard monitoring

## Teknologi yang digunakan:
- PHP 7.4+ 
- MySQL 8.0+
- Bootstrap 5 untuk UI
- JavaScript untuk interaktivitas

## Struktur Database:
- Tabel users (admin, siswa)
- Tabel students (data siswa)
- Tabel payments (pembayaran)
- Tabel payment_proofs (bukti transfer)

## Coding Guidelines:
- Gunakan prepared statements untuk keamanan database
- Implementasi session management yang aman
- Validasi input yang ketat
- UI responsif dengan Bootstrap
- Error handling yang baik
