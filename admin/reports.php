<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
require_admin();

$page_title = 'Laporan';
$show_navbar = true;
$show_footer = true;

// Get filter parameters
$period = isset($_GET['period']) ? sanitize_input($_GET['period']) : 'month';
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');

try {
    $db = new Database();
    
    // Build date filters
    switch ($period) {
        case 'year':
            $date_filter = "YEAR(p.created_at) = :year";
            $params = [':year' => $year];
            break;
        case 'month':
            $date_filter = "YEAR(p.created_at) = :year AND MONTH(p.created_at) = :month";
            $params = [':year' => $year, ':month' => $month];
            break;
        default:
            $date_filter = "YEAR(p.created_at) = :year AND MONTH(p.created_at) = :month";
            $params = [':year' => $year, ':month' => $month];
    }
    
    // Get payment summary
    $db->query("SELECT 
                    COUNT(*) as total_payments,
                    SUM(CASE WHEN status = 'verified' THEN jumlah_bayar ELSE 0 END) as total_verified,
                    SUM(CASE WHEN status = 'pending' THEN jumlah_bayar ELSE 0 END) as total_pending,
                    SUM(CASE WHEN status = 'rejected' THEN jumlah_bayar ELSE 0 END) as total_rejected,
                    COUNT(CASE WHEN status = 'verified' THEN 1 END) as count_verified,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as count_pending,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as count_rejected
                FROM payments p WHERE $date_filter");
    
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $summary = $db->single();
    
    // Get payment by type
    $db->query("SELECT pt.nama_pembayaran, 
                       COUNT(p.id) as count,
                       SUM(CASE WHEN p.status = 'verified' THEN p.jumlah_bayar ELSE 0 END) as total_verified
                FROM payment_types pt 
                LEFT JOIN payments p ON pt.id = p.payment_type_id AND $date_filter
                GROUP BY pt.id, pt.nama_pembayaran
                ORDER BY total_verified DESC");
    
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $payment_by_type = $db->resultset();
    
    // Get payment by class
    $db->query("SELECT s.kelas, 
                       COUNT(p.id) as count,
                       SUM(CASE WHEN p.status = 'verified' THEN p.jumlah_bayar ELSE 0 END) as total_verified
                FROM students s 
                LEFT JOIN payments p ON s.id = p.student_id AND $date_filter
                GROUP BY s.kelas
                ORDER BY s.kelas");
    
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $payment_by_class = $db->resultset();
    
    // Get recent verified payments
    $db->query("SELECT p.*, s.nama_lengkap, s.kelas, pt.nama_pembayaran
                FROM payments p 
                JOIN students s ON p.student_id = s.id 
                JOIN payment_types pt ON p.payment_type_id = pt.id
                WHERE p.status = 'verified' AND $date_filter
                ORDER BY p.verified_at DESC 
                LIMIT 20");
    
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $recent_payments = $db->resultset();
    
    // Get monthly trend (last 12 months)
    $db->query("SELECT 
                    YEAR(created_at) as year,
                    MONTH(created_at) as month,
                    COUNT(*) as count,
                    SUM(CASE WHEN status = 'verified' THEN jumlah_bayar ELSE 0 END) as total
                FROM payments 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY YEAR(created_at), MONTH(created_at)
                ORDER BY year, month");
    $monthly_trend = $db->resultset();
    
} catch (Exception $e) {
    $error_message = 'Error loading reports: ' . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="students.php">
                            <i class="bi bi-people"></i> Data Siswa
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payments.php">
                            <i class="bi bi-credit-card"></i> Pembayaran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payment-types.php">
                            <i class="bi bi-tags"></i> Jenis Pembayaran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php">
                            <i class="bi bi-graph-up"></i> Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="bi bi-house"></i> Kembali ke Beranda
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Laporan Pembayaran</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Print
                        </button>
                    </div>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="period" class="form-label">Periode</label>
                            <select class="form-select" id="period" name="period" onchange="toggleMonthField()">
                                <option value="month" <?php echo ($period === 'month') ? 'selected' : ''; ?>>Bulanan</option>
                                <option value="year" <?php echo ($period === 'year') ? 'selected' : ''; ?>>Tahunan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="year" class="form-label">Tahun</label>
                            <select class="form-select" id="year" name="year">
                                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($y == $year) ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3" id="monthField">
                            <label for="month" class="form-label">Bulan</label>
                            <select class="form-select" id="month" name="month">
                                <?php 
                                $months = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                                foreach ($months as $num => $name): ?>
                                    <option value="<?php echo $num; ?>" <?php echo ($num == $month) ? 'selected' : ''; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">
                                <i class="bi bi-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Total Pembayaran</div>
                                    <div class="h5 mb-0"><?php echo number_format($summary->total_payments ?? 0); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-receipt" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Total Pendapatan</div>
                                    <div class="h6 mb-0"><?php echo format_currency($summary->total_verified ?? 0); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Menunggu Verifikasi</div>
                                    <div class="h5 mb-0"><?php echo number_format($summary->count_pending ?? 0); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-clock" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Terverifikasi</div>
                                    <div class="h5 mb-0"><?php echo number_format($summary->count_verified ?? 0); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Payment by Type -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Pembayaran per Jenis</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($payment_by_type)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Jenis Pembayaran</th>
                                                <th>Jumlah</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payment_by_type as $type): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($type->nama_pembayaran); ?></td>
                                                    <td><?php echo number_format($type->count); ?></td>
                                                    <td><?php echo format_currency($type->total_verified); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">Tidak ada data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Payment by Class -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Pembayaran per Kelas</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($payment_by_class)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Kelas</th>
                                                <th>Jumlah</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payment_by_class as $class): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($class->kelas); ?></td>
                                                    <td><?php echo number_format($class->count); ?></td>
                                                    <td><?php echo format_currency($class->total_verified); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">Tidak ada data</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trend -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-graph-up"></i> Tren Pembayaran (12 Bulan Terakhir)</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($monthly_trend)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Bulan</th>
                                                <th>Jumlah Transaksi</th>
                                                <th>Total Pendapatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($monthly_trend as $trend): ?>
                                                <tr>
                                                    <td><?php echo $months[$trend->month] . ' ' . $trend->year; ?></td>
                                                    <td><?php echo number_format($trend->count); ?></td>
                                                    <td><?php echo format_currency($trend->total); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">Tidak ada data tren</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Verified Payments -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Pembayaran Terverifikasi Terbaru</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_payments)): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Siswa</th>
                                                <th>Kelas</th>
                                                <th>Jenis Pembayaran</th>
                                                <th>Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_payments as $payment): ?>
                                                <tr>
                                                    <td><?php echo format_date_id($payment->verified_at); ?></td>
                                                    <td><?php echo htmlspecialchars($payment->nama_lengkap); ?></td>
                                                    <td><?php echo htmlspecialchars($payment->kelas); ?></td>
                                                    <td><?php echo htmlspecialchars($payment->nama_pembayaran); ?></td>
                                                    <td><?php echo format_currency($payment->jumlah_bayar); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted text-center">Tidak ada pembayaran terverifikasi</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function toggleMonthField() {
    const period = document.getElementById('period').value;
    const monthField = document.getElementById('monthField');
    
    if (period === 'year') {
        monthField.style.display = 'none';
    } else {
        monthField.style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleMonthField();
});
</script>

<?php include '../includes/footer.php'; ?>
