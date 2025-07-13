<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require student access
require_student();

$page_title = 'Pembayaran Saya';
$show_navbar = true;
$show_footer = true;

// Get student data
try {
    $db = new Database();
    $db->query("SELECT * FROM students WHERE user_id = :user_id");
    $db->bind(':user_id', $_SESSION['user_id']);
    $student = $db->single();
    
    if (!$student) {
        redirect('dashboard.php');
    }
} catch (Exception $e) {
    redirect('dashboard.php');
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Build WHERE clause
    $where_clause = "WHERE p.student_id = :student_id";
    $params = [':student_id' => $student->id];
    
    if (!empty($status_filter)) {
        $where_clause .= " AND p.status = :status";
        $params[':status'] = $status_filter;
    }
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total 
                    FROM payments p 
                    $where_clause";
    
    $db->query($count_query);
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $total_payments = $db->single()->total;
    $total_pages = ceil($total_payments / $limit);
    
    // Get payments with details
    $db->query("SELECT p.*, pt.nama_pembayaran, pp.file_name, pp.file_path, u.username as verified_by_name
                FROM payments p 
                JOIN payment_types pt ON p.payment_type_id = pt.id 
                LEFT JOIN payment_proofs pp ON p.id = pp.payment_id
                LEFT JOIN users u ON p.verified_by = u.id
                $where_clause 
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset");
    
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $db->bind(':limit', $limit);
    $db->bind(':offset', $offset);
    
    $payments = $db->resultset();
    
    // Get payment statistics
    $db->query("SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                    SUM(CASE WHEN status = 'verified' THEN jumlah_bayar ELSE 0 END) as total_paid
                FROM payments WHERE student_id = :student_id");
    $db->bind(':student_id', $student->id);
    $stats = $db->single();
    
} catch (Exception $e) {
    $error_message = 'Error loading payments: ' . $e->getMessage();
    $payments = [];
    $stats = (object)[
        'total' => 0,
        'pending' => 0,
        'verified' => 0,
        'rejected' => 0,
        'total_paid' => 0
    ];
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
                        <a class="nav-link active" href="payments.php">
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
                <h1 class="h2">Pembayaran Saya</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="payment-new.php" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Bayar Tagihan Baru
                        </a>
                    </div>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="text-xs fw-bold text-uppercase mb-1">Total Pembayaran</div>
                                    <div class="h5 mb-0"><?php echo number_format($stats->total); ?></div>
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
                                    <div class="h5 mb-0"><?php echo number_format($stats->pending); ?></div>
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
                                    <div class="h5 mb-0"><?php echo number_format($stats->verified); ?></div>
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
                                    <div class="h6 mb-0"><?php echo format_currency($stats->total_paid); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter and Actions -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <select class="form-select" onchange="filterByStatus(this.value)">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                        <option value="verified" <?php echo ($status_filter === 'verified') ? 'selected' : ''; ?>>Terverifikasi</option>
                        <option value="rejected" <?php echo ($status_filter === 'rejected') ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Menampilkan <?php echo count($payments); ?> dari <?php echo number_format($total_payments); ?> pembayaran
                    </small>
                    <?php if (!empty($status_filter)): ?>
                        <br><a href="payments.php" class="btn btn-sm btn-secondary">Reset Filter</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payments List -->
            <div class="row">
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $payment): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><?php echo htmlspecialchars($payment->nama_pembayaran); ?></h6>
                                    <?php echo get_status_badge($payment->status); ?>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Jumlah Bayar:</small>
                                            <div class="fw-bold text-primary"><?php echo format_currency($payment->jumlah_bayar); ?></div>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Tanggal Bayar:</small>
                                            <div><?php echo format_date_id($payment->tanggal_bayar); ?></div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted">Disubmit:</small>
                                            <div><small><?php echo format_date_id($payment->created_at); ?></small></div>
                                        </div>
                                        <div class="col-6">
                                            <?php if ($payment->status !== 'pending'): ?>
                                                <small class="text-muted">
                                                    <?php echo ($payment->status === 'verified') ? 'Diverifikasi:' : 'Ditolak:'; ?>
                                                </small>
                                                <div><small><?php echo format_date_id($payment->verified_at); ?></small></div>
                                                <?php if ($payment->verified_by_name): ?>
                                                    <div><small class="text-muted">oleh <?php echo htmlspecialchars($payment->verified_by_name); ?></small></div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($payment->keterangan)): ?>
                                        <hr>
                                        <small class="text-muted">Keterangan:</small>
                                        <div><small><?php echo htmlspecialchars($payment->keterangan); ?></small></div>
                                    <?php endif; ?>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if ($payment->file_name): ?>
                                                <a href="../<?php echo htmlspecialchars($payment->file_path); ?>" 
                                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-file-earmark-image"></i> Lihat Bukti
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <small class="text-muted">#<?php echo $payment->id; ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="col-12">
                            <nav>
                                <ul class="pagination justify-content-center">
                                    <?php
                                    $base_url = 'payments.php';
                                    $query_string = !empty($status_filter) ? '&status=' . urlencode($status_filter) : '';
                                    ?>
                                    
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo $base_url; ?>?page=<?php echo ($page - 1) . $query_string; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="<?php echo $base_url; ?>?page=<?php echo $i . $query_string; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?php echo $base_url; ?>?page=<?php echo ($page + 1) . $query_string; ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-credit-card" style="font-size: 4rem; color: #ccc;"></i>
                                <h4 class="text-muted mt-3">
                                    <?php if (!empty($status_filter)): ?>
                                        Tidak ada pembayaran dengan status "<?php echo ucfirst($status_filter); ?>"
                                    <?php else: ?>
                                        Belum Ada Pembayaran
                                    <?php endif; ?>
                                </h4>
                                <p class="text-muted">
                                    <?php if (!empty($status_filter)): ?>
                                        Coba ubah filter atau buat pembayaran baru
                                    <?php else: ?>
                                        Mulai dengan membuat pembayaran pertama Anda
                                    <?php endif; ?>
                                </p>
                                <div class="mt-3">
                                    <?php if (!empty($status_filter)): ?>
                                        <a href="payments.php" class="btn btn-secondary me-2">
                                            <i class="bi bi-arrow-left"></i> Lihat Semua
                                        </a>
                                    <?php endif; ?>
                                    <a href="payment-new.php" class="btn btn-success">
                                        <i class="bi bi-plus-circle"></i> Bayar Tagihan Baru
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<script>
function filterByStatus(status) {
    const url = new URL(window.location);
    if (status) {
        url.searchParams.set('status', status);
    } else {
        url.searchParams.delete('status');
    }
    url.searchParams.delete('page'); // Reset to first page
    window.location = url;
}

// Auto refresh for pending payments
<?php if ($status_filter === 'pending' || $stats->pending > 0): ?>
setTimeout(function() {
    location.reload();
}, 60000); // Refresh every minute if there are pending payments
<?php endif; ?>
</script>

<?php include '../includes/footer.php'; ?>
