<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require student access
require_student();

$page_title = 'Dashboard Siswa';
$show_navbar = true;
$show_footer = true;

// Get student data
try {
    $db = new Database();
    
    // Get current student info
    $db->query("SELECT s.*, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = :user_id");
    $db->bind(':user_id', $_SESSION['user_id']);
    $student = $db->single();
    
    if (!$student) {
        // Student record not found
        $_SESSION['flash_message'] = [
            'message' => 'Data siswa tidak ditemukan. Silakan hubungi admin.',
            'type' => 'danger'
        ];
        redirect('../index.php');
    }
    
    // Get payment statistics
    $db->query("SELECT COUNT(*) as total FROM payments WHERE student_id = :student_id");
    $db->bind(':student_id', $student->id);
    $total_payments = $db->single()->total;
    
    $db->query("SELECT COUNT(*) as total FROM payments WHERE student_id = :student_id AND status = 'pending'");
    $db->bind(':student_id', $student->id);
    $pending_payments = $db->single()->total;
    
    $db->query("SELECT COUNT(*) as total FROM payments WHERE student_id = :student_id AND status = 'verified'");
    $db->bind(':student_id', $student->id);
    $verified_payments = $db->single()->total;
    
    $db->query("SELECT SUM(jumlah_bayar) as total FROM payments WHERE student_id = :student_id AND status = 'verified'");
    $db->bind(':student_id', $student->id);
    $result = $db->single();
    $total_paid = $result->total ?? 0;
    
    // Get recent payments
    $db->query("SELECT p.*, pt.nama_pembayaran, pp.file_name, pp.file_path 
                FROM payments p 
                JOIN payment_types pt ON p.payment_type_id = pt.id 
                LEFT JOIN payment_proofs pp ON p.id = pp.payment_id 
                WHERE p.student_id = :student_id 
                ORDER BY p.created_at DESC 
                LIMIT 5");
    $db->bind(':student_id', $student->id);
    $recent_payments = $db->resultset();
    
    // Get available payment types
    $db->query("SELECT * FROM payment_types WHERE is_active = 1 ORDER BY nama_pembayaran");
    $payment_types = $db->resultset();
    
} catch (Exception $e) {
    $error_message = 'Error loading dashboard: ' . $e->getMessage();
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payments.php">
                            <i class="bi bi-credit-card"></i> Pembayaran Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payment-new.php">
                            <i class="bi bi-plus-circle"></i> Bayar Tagihan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person"></i> Profil Saya
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
                <h1 class="h2">Dashboard Siswa</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Welcome Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h4 class="mb-2">Selamat Datang, <?php echo htmlspecialchars($student->nama_lengkap); ?>!</h4>
                                    <p class="mb-0">
                                        <i class="bi bi-person-badge"></i> NIS: <?php echo htmlspecialchars($student->nis); ?> | 
                                        <i class="bi bi-building"></i> Kelas: <?php echo htmlspecialchars($student->kelas); ?>
                                    </p>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-person-circle" style="font-size: 4rem; opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Total Pembayaran</div>
                                    <div class="h5 mb-0"><?php echo number_format($total_payments); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-receipt" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Menunggu Verifikasi</div>
                                    <div class="h5 mb-0"><?php echo number_format($pending_payments); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-clock" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Terverifikasi</div>
                                    <div class="h5 mb-0"><?php echo number_format($verified_payments); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <div class="card bg-dark text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Total Dibayar</div>
                                    <div class="h6 mb-0"><?php echo format_currency($total_paid); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                                </div>
                            </div>
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
                                    <a href="payment-new.php" class="btn btn-success w-100">
                                        <i class="bi bi-plus-circle"></i> Bayar Tagihan Baru
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="payments.php" class="btn btn-primary w-100">
                                        <i class="bi bi-list"></i> Lihat Semua Pembayaran
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="profile.php" class="btn btn-info w-100">
                                        <i class="bi bi-person"></i> Edit Profil
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="payments.php?status=pending" class="btn btn-warning w-100">
                                        <i class="bi bi-clock"></i> Cek Status
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Payments -->
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
                                                <th>Jenis Pembayaran</th>
                                                <th>Jumlah</th>
                                                <th>Tanggal</th>
                                                <th>Status</th>
                                                <th>Bukti</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_payments as $payment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($payment->nama_pembayaran); ?></td>
                                                    <td><?php echo format_currency($payment->jumlah_bayar); ?></td>
                                                    <td><?php echo format_date_id($payment->tanggal_bayar); ?></td>
                                                    <td><?php echo get_status_badge($payment->status); ?></td>
                                                    <td>
                                                        <?php if ($payment->file_name): ?>
                                                            <a href="../<?php echo htmlspecialchars($payment->file_path); ?>" 
                                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted mt-2">Belum ada pembayaran</p>
                                    <a href="payment-new.php" class="btn btn-success">
                                        <i class="bi bi-plus-circle"></i> Buat Pembayaran Pertama
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Available Payment Types -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-tags"></i> Jenis Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($payment_types)): ?>
                                <?php foreach ($payment_types as $type): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 border rounded">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($type->nama_pembayaran); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($type->deskripsi); ?></small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-primary"><?php echo format_currency($type->jumlah); ?></div>
                                            <a href="payment-new.php?type=<?php echo $type->id; ?>" class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-plus"></i> Bayar
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted text-center">Belum ada jenis pembayaran yang tersedia</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Student Info -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person"></i> Informasi Siswa</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Nama:</strong></td>
                                    <td><?php echo htmlspecialchars($student->nama_lengkap); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>NIS:</strong></td>
                                    <td><?php echo htmlspecialchars($student->nis); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Kelas:</strong></td>
                                    <td><?php echo htmlspecialchars($student->kelas); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td><?php echo htmlspecialchars($student->email); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>HP:</strong></td>
                                    <td><?php echo htmlspecialchars($student->nomor_hp); ?></td>
                                </tr>
                            </table>
                            <div class="d-grid">
                                <a href="profile.php" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Edit Profil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
