<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
require_admin();

$page_title = 'Kelola Pembayaran';
$show_navbar = true;
$show_footer = true;

$success_message = '';
$error_message = '';

// Handle verification actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $action = $_POST['action'];
        $payment_id = (int)$_POST['payment_id'];
        $keterangan = sanitize_input($_POST['keterangan'] ?? '');
        
        try {
            $db = new Database();
            
            if ($action === 'verify') {
                $db->query("UPDATE payments SET status = 'verified', verified_by = :admin_id, verified_at = NOW(), keterangan = :keterangan WHERE id = :id");
                $db->bind(':admin_id', $_SESSION['user_id']);
                $db->bind(':keterangan', $keterangan);
                $db->bind(':id', $payment_id);
                $db->execute();
                
                $success_message = 'Pembayaran berhasil diverifikasi!';
            } elseif ($action === 'reject') {
                $db->query("UPDATE payments SET status = 'rejected', verified_by = :admin_id, verified_at = NOW(), keterangan = :keterangan WHERE id = :id");
                $db->bind(':admin_id', $_SESSION['user_id']);
                $db->bind(':keterangan', $keterangan);
                $db->bind(':id', $payment_id);
                $db->execute();
                
                $success_message = 'Pembayaran ditolak!';
            }
        } catch (Exception $e) {
            $error_message = 'Error: ' . $e->getMessage();
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    $db = new Database();
    
    // Build WHERE clause
    $where_conditions = [];
    $params = [];
    
    if (!empty($status_filter)) {
        $where_conditions[] = "p.status = :status";
        $params[':status'] = $status_filter;
    }
    
    if (!empty($search)) {
        $where_conditions[] = "(s.nama_lengkap LIKE :search OR s.nis LIKE :search OR pt.nama_pembayaran LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    $where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total 
                    FROM payments p 
                    JOIN students s ON p.student_id = s.id 
                    JOIN payment_types pt ON p.payment_type_id = pt.id 
                    $where_clause";
    
    $db->query($count_query);
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $total_payments = $db->single()->total;
    $total_pages = ceil($total_payments / $limit);
    
    // Get payments
    $db->query("SELECT p.*, s.nama_lengkap, s.nis, s.kelas, pt.nama_pembayaran, 
                       u.username as verified_by_username,
                       pp.file_name, pp.file_path
                FROM payments p 
                JOIN students s ON p.student_id = s.id 
                JOIN payment_types pt ON p.payment_type_id = pt.id 
                LEFT JOIN users u ON p.verified_by = u.id
                LEFT JOIN payment_proofs pp ON p.id = pp.payment_id
                $where_clause 
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset");
    
    foreach ($params as $key => $value) {
        $db->bind($key, $value);
    }
    $db->bind(':limit', $limit);
    $db->bind(':offset', $offset);
    
    $payments = $db->resultset();
    
} catch (Exception $e) {
    $error_message = 'Error loading payments: ' . $e->getMessage();
    $payments = [];
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
                        <a class="nav-link active" href="payments.php">
                            <i class="bi bi-credit-card"></i> Pembayaran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payment-types.php">
                            <i class="bi bi-tags"></i> Jenis Pembayaran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php">
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
                <h1 class="h2">Kelola Pembayaran</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <form method="GET" action="">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Cari siswa atau jenis pembayaran..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <?php if (!empty($status_filter)): ?>
                            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                        <?php endif; ?>
                    </form>
                </div>
                <div class="col-md-4">
                    <select class="form-select" onchange="filterByStatus(this.value)">
                        <option value="">Semua Status</option>
                        <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                        <option value="verified" <?php echo ($status_filter === 'verified') ? 'selected' : ''; ?>>Terverifikasi</option>
                        <option value="rejected" <?php echo ($status_filter === 'rejected') ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted">
                        <?php echo number_format($total_payments); ?> pembayaran ditemukan
                    </small>
                    <?php if (!empty($search) || !empty($status_filter)): ?>
                        <br><a href="payments.php" class="btn btn-sm btn-secondary">Reset Filter</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-clock" style="font-size: 2rem;"></i>
                            <h4>
                                <?php
                                try {
                                    $db->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'");
                                    echo $db->single()->count;
                                } catch (Exception $e) {
                                    echo '0';
                                }
                                ?>
                            </h4>
                            <small>Menunggu Verifikasi</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                            <h4>
                                <?php
                                try {
                                    $db->query("SELECT COUNT(*) as count FROM payments WHERE status = 'verified'");
                                    echo $db->single()->count;
                                } catch (Exception $e) {
                                    echo '0';
                                }
                                ?>
                            </h4>
                            <small>Terverifikasi</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-x-circle" style="font-size: 2rem;"></i>
                            <h4>
                                <?php
                                try {
                                    $db->query("SELECT COUNT(*) as count FROM payments WHERE status = 'rejected'");
                                    echo $db->single()->count;
                                } catch (Exception $e) {
                                    echo '0';
                                }
                                ?>
                            </h4>
                            <small>Ditolak</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                            <h6>
                                <?php
                                try {
                                    $db->query("SELECT SUM(jumlah_bayar) as total FROM payments WHERE status = 'verified' AND MONTH(created_at) = MONTH(CURDATE())");
                                    $result = $db->single();
                                    echo format_currency($result->total ?? 0);
                                } catch (Exception $e) {
                                    echo 'Rp 0';
                                }
                                ?>
                            </h6>
                            <small>Total Bulan Ini</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($payments)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Siswa</th>
                                        <th>Jenis Pembayaran</th>
                                        <th>Jumlah</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Status</th>
                                        <th>Bukti</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                        <tr>
                                            <td>#<?php echo $payment->id; ?></td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($payment->nama_lengkap); ?></div>
                                                <small class="text-muted">
                                                    NIS: <?php echo htmlspecialchars($payment->nis); ?> | 
                                                    <?php echo htmlspecialchars($payment->kelas); ?>
                                                </small>
                                            </td>
                                            <td><?php echo htmlspecialchars($payment->nama_pembayaran); ?></td>
                                            <td><?php echo format_currency($payment->jumlah_bayar); ?></td>
                                            <td><?php echo format_date_id($payment->tanggal_bayar); ?></td>
                                            <td><?php echo get_status_badge($payment->status); ?></td>
                                            <td>
                                                <?php if ($payment->file_name): ?>
                                                    <a href="../<?php echo htmlspecialchars($payment->file_path); ?>" 
                                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-file-earmark-image"></i> Lihat
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($payment->status === 'pending'): ?>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-success" 
                                                                onclick="verifyPayment(<?php echo $payment->id; ?>, 'verify')">
                                                            <i class="bi bi-check"></i> Verifikasi
                                                        </button>
                                                        <button type="button" class="btn btn-danger" 
                                                                onclick="verifyPayment(<?php echo $payment->id; ?>, 'reject')">
                                                            <i class="bi bi-x"></i> Tolak
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <small class="text-muted">
                                                        <?php echo $payment->status === 'verified' ? 'Diverifikasi' : 'Ditolak'; ?> oleh 
                                                        <?php echo htmlspecialchars($payment->verified_by_username); ?>
                                                        <br><?php echo format_date_id($payment->verified_at); ?>
                                                    </small>
                                                    <?php if (!empty($payment->keterangan)): ?>
                                                        <br><small class="text-info">
                                                            <i class="bi bi-info-circle"></i> <?php echo htmlspecialchars($payment->keterangan); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav class="mt-3">
                                <ul class="pagination justify-content-center">
                                    <?php
                                    $base_url = 'payments.php';
                                    $query_params = [];
                                    if (!empty($search)) $query_params[] = 'search=' . urlencode($search);
                                    if (!empty($status_filter)) $query_params[] = 'status=' . urlencode($status_filter);
                                    $query_string = !empty($query_params) ? '&' . implode('&', $query_params) : '';
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
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-credit-card" style="font-size: 4rem; color: #ccc;"></i>
                            <h4 class="text-muted mt-3">Belum Ada Data Pembayaran</h4>
                            <p class="text-muted">Pembayaran yang disubmit siswa akan muncul di sini</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Verification Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="verificationForm" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="verificationTitle">Verifikasi Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" id="verificationAction">
                    <input type="hidden" name="payment_id" id="verificationPaymentId">
                    
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" name="keterangan" id="keterangan" rows="3" 
                                  placeholder="Tambahkan keterangan jika diperlukan..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <span id="verificationMessage">Pastikan bukti pembayaran sudah sesuai sebelum memverifikasi.</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn" id="verificationSubmitBtn">Verifikasi</button>
                </div>
            </form>
        </div>
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

function verifyPayment(paymentId, action) {
    document.getElementById('verificationPaymentId').value = paymentId;
    document.getElementById('verificationAction').value = action;
    
    const modal = document.getElementById('verificationModal');
    const title = document.getElementById('verificationTitle');
    const message = document.getElementById('verificationMessage');
    const submitBtn = document.getElementById('verificationSubmitBtn');
    
    if (action === 'verify') {
        title.textContent = 'Verifikasi Pembayaran';
        message.textContent = 'Pastikan bukti pembayaran sudah sesuai sebelum memverifikasi.';
        submitBtn.textContent = 'Verifikasi';
        submitBtn.className = 'btn btn-success';
    } else {
        title.textContent = 'Tolak Pembayaran';
        message.textContent = 'Pembayaran akan ditolak. Berikan keterangan alasan penolakan.';
        submitBtn.textContent = 'Tolak';
        submitBtn.className = 'btn btn-danger';
    }
    
    new bootstrap.Modal(modal).show();
}

// Auto refresh for pending payments
if (window.location.search.includes('status=pending')) {
    setTimeout(function() {
        location.reload();
    }, 60000); // Refresh every minute for pending payments
}
</script>

<?php include '../includes/footer.php'; ?>
