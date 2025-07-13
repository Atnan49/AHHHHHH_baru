<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
require_admin();

$page_title = 'Dashboard Admin';
$show_navbar = true;
$show_footer = true;

// Get statistics
try {
    $db = new Database();
    
    // Total students
    $db->query("SELECT COUNT(*) as total FROM students");
    $total_students = $db->single()->total;
    
    // Total payments today
    $db->query("SELECT COUNT(*) as total FROM payments WHERE DATE(created_at) = CURDATE()");
    $payments_today = $db->single()->total;
    
    // Pending payments
    $db->query("SELECT COUNT(*) as total FROM payments WHERE status = 'pending'");
    $pending_payments = $db->single()->total;
    
    // Total revenue this month
    $db->query("SELECT SUM(jumlah_bayar) as total FROM payments WHERE status = 'verified' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $result = $db->single();
    $monthly_revenue = $result->total ?? 0;
    
    // Recent payments
    $db->query("SELECT p.*, s.nama_lengkap, s.kelas, pt.nama_pembayaran 
                FROM payments p 
                JOIN students s ON p.student_id = s.id 
                JOIN payment_types pt ON p.payment_type_id = pt.id 
                ORDER BY p.created_at DESC 
                LIMIT 10");
    $recent_payments = $db->resultset();
    
    // Payment status distribution
    $db->query("SELECT status, COUNT(*) as count FROM payments GROUP BY status");
    $payment_status = $db->resultset();
    
} catch (Exception $e) {
    $error_message = 'Error loading dashboard data: ' . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <!-- Admin Navigation -->
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-none d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.php">
                            <i class="fas fa-users me-2"></i> Data Siswa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payments.php">
                            <i class="fas fa-credit-card me-2"></i> Pembayaran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payment-types.php">
                            <i class="fas fa-tags me-2"></i> Jenis Pembayaran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
                            <i class="fas fa-chart-bar me-2"></i> Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin</h1>
                <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>! Kelola sistem pembayaran sekolah dengan mudah.</p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php show_flash_message(); ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3><?php echo number_format($total_students ?? 0); ?></h3>
                                <p>Total Siswa</p>
                            </div>
                            <div class="payment-icon" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card success">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3><?php echo number_format($payments_today ?? 0); ?></h3>
                                <p>Pembayaran Hari Ini</p>
                            </div>
                            <div class="payment-icon" style="width: 50px; height: 50px; font-size: 1.2rem; background: rgba(255,255,255,0.2);">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card warning">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3><?php echo number_format($pending_payments ?? 0); ?></h3>
                                <p>Menunggu Verifikasi</p>
                            </div>
                            <div class="payment-icon" style="width: 50px; height: 50px; font-size: 1.2rem; background: rgba(255,255,255,0.2);">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stats-card info">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h3>Rp <?php echo number_format($monthly_revenue ?? 0, 0, ',', '.'); ?></h3>
                                <p>Pendapatan Bulan Ini</p>
                            </div>
                            <div class="payment-icon" style="width: 50px; height: 50px; font-size: 1.2rem; background: rgba(255,255,255,0.2);">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="students.php">Lihat Detail</a>
                            <div class="small text-white"><i class="bi bi-angle-right"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Pembayaran Hari Ini</div>
                                    <div class="h5 mb-0"><?php echo number_format($payments_today ?? 0); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-calendar-check" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="payments.php">Lihat Detail</a>
                            <div class="small text-white"><i class="bi bi-angle-right"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Pendapatan Bulan Ini</div>
                                    <div class="h5 mb-0"><?php echo format_currency($monthly_revenue ?? 0); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="reports.php">Lihat Laporan</a>
                            <div class="small text-white"><i class="bi bi-angle-right"></i></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card bg-danger text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Menunggu Verifikasi</div>
                                    <div class="h5 mb-0"><?php echo number_format($pending_payments ?? 0); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-clock" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="payments.php?status=pending">Verifikasi Sekarang</a>
                            <div class="small text-white"><i class="bi bi-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-lightning"></i> Aksi Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="students.php?action=add" class="btn btn-primary w-100">
                                        <i class="bi bi-person-plus"></i> Tambah Siswa
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="payments.php?status=pending" class="btn btn-warning w-100">
                                        <i class="bi bi-check-circle"></i> Verifikasi Pembayaran
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="payment-types.php" class="btn btn-info w-100">
                                        <i class="bi bi-tags"></i> Kelola Jenis Pembayaran
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="reports.php" class="btn btn-success w-100">
                                        <i class="bi bi-file-earmark-text"></i> Buat Laporan
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Payments -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Pembayaran Terbaru</h5>
                            <a href="payments.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_payments)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Siswa</th>
                                                <th>Jenis</th>
                                                <th>Jumlah</th>
                                                <th>Status</th>
                                                <th>Tanggal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_payments as $payment): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-bold"><?php echo htmlspecialchars($payment->nama_lengkap); ?></div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($payment->kelas); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($payment->nama_pembayaran); ?></td>
                                                    <td><?php echo format_currency($payment->jumlah_bayar); ?></td>
                                                    <td><?php echo get_status_badge($payment->status); ?></td>
                                                    <td><?php echo format_date_id($payment->created_at); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">Belum ada pembayaran</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Status Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($payment_status)): ?>
                                <?php foreach ($payment_status as $status): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <?php echo get_status_badge($status->status); ?>
                                        </div>
                                        <span class="fw-bold"><?php echo number_format($status->count); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center">Belum ada data</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- System Info -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Sistem</h5>
                        </div>
                        <div class="card-body">
                            <small>
                                <strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
                                <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
                                <strong>Database:</strong> MySQL<br>
                                <strong>Last Update:</strong> <?php echo date('Y-m-d'); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
