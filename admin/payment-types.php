<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
require_admin();

$page_title = 'Jenis Pembayaran';
$show_navbar = true;
$show_footer = true;

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $action = $_POST['action'];
        
        try {
            $db = new Database();
            
            if ($action === 'add') {
                $nama_pembayaran = sanitize_input($_POST['nama_pembayaran']);
                $jumlah = str_replace(['.', ','], '', $_POST['jumlah']);
                $jumlah = (float)$jumlah;
                $deskripsi = sanitize_input($_POST['deskripsi']);
                
                if (empty($nama_pembayaran) || $jumlah <= 0) {
                    $error_message = 'Nama pembayaran dan jumlah harus diisi dengan benar!';
                } else {
                    $db->query("INSERT INTO payment_types (nama_pembayaran, jumlah, deskripsi) VALUES (:nama, :jumlah, :deskripsi)");
                    $db->bind(':nama', $nama_pembayaran);
                    $db->bind(':jumlah', $jumlah);
                    $db->bind(':deskripsi', $deskripsi);
                    $db->execute();
                    
                    $success_message = 'Jenis pembayaran berhasil ditambahkan!';
                }
            } elseif ($action === 'edit') {
                $id = (int)$_POST['id'];
                $nama_pembayaran = sanitize_input($_POST['nama_pembayaran']);
                $jumlah = str_replace(['.', ','], '', $_POST['jumlah']);
                $jumlah = (float)$jumlah;
                $deskripsi = sanitize_input($_POST['deskripsi']);
                
                if (empty($nama_pembayaran) || $jumlah <= 0) {
                    $error_message = 'Nama pembayaran dan jumlah harus diisi dengan benar!';
                } else {
                    $db->query("UPDATE payment_types SET nama_pembayaran = :nama, jumlah = :jumlah, deskripsi = :deskripsi WHERE id = :id");
                    $db->bind(':nama', $nama_pembayaran);
                    $db->bind(':jumlah', $jumlah);
                    $db->bind(':deskripsi', $deskripsi);
                    $db->bind(':id', $id);
                    $db->execute();
                    
                    $success_message = 'Jenis pembayaran berhasil diperbarui!';
                }
            } elseif ($action === 'toggle') {
                $id = (int)$_POST['id'];
                $is_active = (int)$_POST['is_active'];
                
                $db->query("UPDATE payment_types SET is_active = :is_active WHERE id = :id");
                $db->bind(':is_active', $is_active);
                $db->bind(':id', $id);
                $db->execute();
                
                $success_message = 'Status jenis pembayaran berhasil diubah!';
            } elseif ($action === 'delete') {
                $id = (int)$_POST['id'];
                
                // Check if this payment type is being used
                $db->query("SELECT COUNT(*) as count FROM payments WHERE payment_type_id = :id");
                $db->bind(':id', $id);
                $result = $db->single();
                
                if ($result->count > 0) {
                    $error_message = 'Jenis pembayaran tidak dapat dihapus karena masih digunakan dalam transaksi!';
                } else {
                    $db->query("DELETE FROM payment_types WHERE id = :id");
                    $db->bind(':id', $id);
                    $db->execute();
                    
                    $success_message = 'Jenis pembayaran berhasil dihapus!';
                }
            }
        } catch (Exception $e) {
            $error_message = 'Error: ' . $e->getMessage();
        }
    }
}

// Get payment types
try {
    $db = new Database();
    $db->query("SELECT pt.*, COUNT(p.id) as usage_count 
                FROM payment_types pt 
                LEFT JOIN payments p ON pt.id = p.payment_type_id 
                GROUP BY pt.id 
                ORDER BY pt.nama_pembayaran");
    $payment_types = $db->resultset();
} catch (Exception $e) {
    $error_message = 'Error loading payment types: ' . $e->getMessage();
    $payment_types = [];
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
                        <a class="nav-link active" href="payment-types.php">
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
                <h1 class="h2">Jenis Pembayaran</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle"></i> Tambah Jenis Pembayaran
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

            <!-- Payment Types Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($payment_types)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nama Pembayaran</th>
                                        <th>Jumlah</th>
                                        <th>Deskripsi</th>
                                        <th>Status</th>
                                        <th>Digunakan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payment_types as $type): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($type->nama_pembayaran); ?></div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary"><?php echo format_currency($type->jumlah); ?></span>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?php echo htmlspecialchars($type->deskripsi); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($type->is_active): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo number_format($type->usage_count); ?> kali</span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-warning" 
                                                            onclick="editPaymentType(<?php echo htmlspecialchars(json_encode($type)); ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-<?php echo $type->is_active ? 'secondary' : 'success'; ?>" 
                                                            onclick="togglePaymentType(<?php echo $type->id; ?>, <?php echo $type->is_active ? 0 : 1; ?>)">
                                                        <i class="bi bi-<?php echo $type->is_active ? 'eye-slash' : 'eye'; ?>"></i>
                                                    </button>
                                                    <?php if ($type->usage_count == 0): ?>
                                                        <button type="button" class="btn btn-danger" 
                                                                onclick="deletePaymentType(<?php echo $type->id; ?>, '<?php echo htmlspecialchars($type->nama_pembayaran); ?>')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-tags" style="font-size: 4rem; color: #ccc;"></i>
                            <h4 class="text-muted mt-3">Belum Ada Jenis Pembayaran</h4>
                            <p class="text-muted">Tambahkan jenis pembayaran untuk mulai menerima pembayaran dari siswa</p>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-circle"></i> Tambah Jenis Pembayaran Pertama
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jenis Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="add_nama_pembayaran" class="form-label">Nama Pembayaran *</label>
                        <input type="text" class="form-control" id="add_nama_pembayaran" name="nama_pembayaran" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_jumlah" class="form-label">Jumlah *</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="add_jumlah" name="jumlah" 
                                   required onkeyup="formatCurrency(this)">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="add_deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Jenis Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_nama_pembayaran" class="form-label">Nama Pembayaran *</label>
                        <input type="text" class="form-control" id="edit_nama_pembayaran" name="nama_pembayaran" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_jumlah" class="form-label">Jumlah *</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" id="edit_jumlah" name="jumlah" 
                                   required onkeyup="formatCurrency(this)">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Forms -->
<form id="toggleForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <input type="hidden" name="action" value="toggle">
    <input type="hidden" name="id" id="toggle_id">
    <input type="hidden" name="is_active" id="toggle_is_active">
</form>

<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="delete_id">
</form>

<script>
function editPaymentType(type) {
    document.getElementById('edit_id').value = type.id;
    document.getElementById('edit_nama_pembayaran').value = type.nama_pembayaran;
    document.getElementById('edit_jumlah').value = parseInt(type.jumlah).toLocaleString('id-ID');
    document.getElementById('edit_deskripsi').value = type.deskripsi;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function togglePaymentType(id, isActive) {
    const action = isActive ? 'mengaktifkan' : 'menonaktifkan';
    if (confirm(`Apakah Anda yakin ingin ${action} jenis pembayaran ini?`)) {
        document.getElementById('toggle_id').value = id;
        document.getElementById('toggle_is_active').value = isActive;
        document.getElementById('toggleForm').submit();
    }
}

function deletePaymentType(id, name) {
    if (confirm(`Apakah Anda yakin ingin menghapus jenis pembayaran "${name}"?\n\nPerhatian: Tindakan ini tidak dapat dibatalkan!`)) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
