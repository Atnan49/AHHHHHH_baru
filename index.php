<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'Beranda';
$show_navbar = true;
$show_footer = true;

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Hero Section -->
            <div class="text-center mb-5">
                <h1 class="display-4 fw-bold text-primary">
                    <i class="bi bi-bank2"></i> Sistem Pembayaran Sekolah
                </h1>
                <p class="lead text-muted">Lakukan pembayaran sekolah dengan mudah, aman, dan terpercaya</p>
            </div>

            <?php if (is_logged_in()): ?>
                <!-- User Dashboard Quick Access -->
                <div class="row g-4 mb-5">
                    <?php if (is_admin()): ?>
                        <div class="col-md-4">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="bi bi-speedometer2 text-primary" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Dashboard Admin</h5>
                                    <p class="card-text">Kelola data siswa dan verifikasi pembayaran</p>
                                    <a href="admin/dashboard.php" class="btn btn-primary">Akses Dashboard</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="bi bi-people text-success" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Data Siswa</h5>
                                    <p class="card-text">Kelola dan tambah data siswa baru</p>
                                    <a href="admin/students.php" class="btn btn-success">Kelola Siswa</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="bi bi-credit-card text-warning" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Verifikasi Pembayaran</h5>
                                    <p class="card-text">Verifikasi bukti pembayaran siswa</p>
                                    <a href="admin/payments.php" class="btn btn-warning">Lihat Pembayaran</a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="col-md-6">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="bi bi-speedometer2 text-primary" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Dashboard Siswa</h5>
                                    <p class="card-text">Lihat status pembayaran dan tagihan Anda</p>
                                    <a href="student/dashboard.php" class="btn btn-primary">Akses Dashboard</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card text-center h-100">
                                <div class="card-body">
                                    <i class="bi bi-credit-card text-success" style="font-size: 3rem;"></i>
                                    <h5 class="card-title mt-3">Bayar Tagihan</h5>
                                    <p class="card-text">Upload bukti pembayaran tagihan sekolah</p>
                                    <a href="student/payments.php" class="btn btn-success">Bayar Sekarang</a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Guest Actions -->
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-box-arrow-in-right text-primary" style="font-size: 3rem;"></i>
                                <h5 class="card-title mt-3">Login</h5>
                                <p class="card-text">Masuk ke akun Anda untuk melakukan pembayaran</p>
                                <a href="login.php" class="btn btn-primary">Login Sekarang</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-person-plus text-success" style="font-size: 3rem;"></i>
                                <h5 class="card-title mt-3">Daftar Siswa Baru</h5>
                                <p class="card-text">Daftarkan diri sebagai siswa baru</p>
                                <a href="register.php" class="btn btn-success">Daftar Sekarang</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Features Section -->
            <div class="row g-4">
                <div class="col-12">
                    <h3 class="text-center mb-4">Fitur Utama</h3>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-shield-check text-success" style="font-size: 2.5rem;"></i>
                            <h5 class="card-title mt-3">Keamanan Terjamin</h5>
                            <p class="card-text">Data pembayaran Anda aman dengan enkripsi tingkat tinggi</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-clock text-primary" style="font-size: 2.5rem;"></i>
                            <h5 class="card-title mt-3">Verifikasi Cepat</h5>
                            <p class="card-text">Pembayaran diverifikasi dalam waktu 1x24 jam</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-phone text-warning" style="font-size: 2.5rem;"></i>
                            <h5 class="card-title mt-3">Akses Mobile</h5>
                            <p class="card-text">Dapat diakses dari smartphone dan tablet</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Types Info -->
            <div class="card mt-5">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-info-circle"></i> Jenis Pembayaran</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php
                        try {
                            $db = new Database();
                            $db->query("SELECT * FROM payment_types WHERE is_active = 1 ORDER BY nama_pembayaran");
                            $payment_types = $db->resultset();

                            if ($payment_types) {
                                foreach ($payment_types as $type) {
                                    echo '<div class="col-md-6 mb-3">';
                                    echo '<div class="d-flex justify-content-between align-items-center p-3 border rounded">';
                                    echo '<div>';
                                    echo '<h6 class="mb-1">' . htmlspecialchars($type->nama_pembayaran) . '</h6>';
                                    echo '<small class="text-muted">' . htmlspecialchars($type->deskripsi) . '</small>';
                                    echo '</div>';
                                    echo '<div class="text-end">';
                                    echo '<span class="fw-bold text-primary">' . format_currency($type->jumlah) . '</span>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div class="col-12">';
                                echo '<p class="text-muted text-center">Belum ada jenis pembayaran yang tersedia.</p>';
                                echo '</div>';
                            }
                        } catch (Exception $e) {
                            echo '<div class="col-12">';
                            echo '<p class="text-danger text-center">Error loading payment types.</p>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="text-center mt-5">
                <h4>Butuh Bantuan?</h4>
                <p class="text-muted">Hubungi admin sekolah untuk informasi lebih lanjut</p>
                <div class="row justify-content-center">
                    <div class="col-auto">
                        <i class="bi bi-telephone"></i> (021) 1234-5678
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-envelope"></i> admin@sekolah.com
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-whatsapp"></i> +62 812-3456-7890
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
