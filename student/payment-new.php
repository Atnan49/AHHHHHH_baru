<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require student access
require_student();

$page_title = 'Bayar Tagihan Baru';
$show_navbar = true;
$show_footer = true;

$success_message = '';
$error_messages = [];

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $payment_type_id = (int)$_POST['payment_type_id'];
        $jumlah_bayar = str_replace(['.', ','], '', $_POST['jumlah_bayar']);
        $jumlah_bayar = (float)$jumlah_bayar;
        $tanggal_bayar = sanitize_input($_POST['tanggal_bayar']);
        $keterangan = sanitize_input($_POST['keterangan']);
        
        // Validasi input
        if (empty($payment_type_id)) $error_messages[] = 'Jenis pembayaran harus dipilih!';
        if (empty($jumlah_bayar) || $jumlah_bayar <= 0) $error_messages[] = 'Jumlah pembayaran tidak valid!';
        if (empty($tanggal_bayar)) $error_messages[] = 'Tanggal pembayaran harus diisi!';
        
        // Validasi file upload
        if (!isset($_FILES['bukti_transfer']) || $_FILES['bukti_transfer']['error'] !== UPLOAD_ERR_OK) {
            $error_messages[] = 'Bukti transfer harus diupload!';
        }
        
        if (empty($error_messages)) {
            try {
                // Validate payment type
                $db->query("SELECT * FROM payment_types WHERE id = :id AND is_active = 1");
                $db->bind(':id', $payment_type_id);
                $payment_type = $db->single();
                
                if (!$payment_type) {
                    $error_messages[] = 'Jenis pembayaran tidak valid!';
                } else {
                    // Upload file
                    $upload_result = upload_file($_FILES['bukti_transfer'], '../uploads/');
                    
                    if ($upload_result['success']) {
                        // Insert payment record
                        $db->query("INSERT INTO payments (student_id, payment_type_id, jumlah_bayar, tanggal_bayar, keterangan, status, created_at) 
                                   VALUES (:student_id, :payment_type_id, :jumlah_bayar, :tanggal_bayar, :keterangan, 'pending', NOW())");
                        $db->bind(':student_id', $student->id);
                        $db->bind(':payment_type_id', $payment_type_id);
                        $db->bind(':jumlah_bayar', $jumlah_bayar);
                        $db->bind(':tanggal_bayar', $tanggal_bayar);
                        $db->bind(':keterangan', $keterangan);
                        $db->execute();
                        
                        $payment_id = $db->lastInsertId();
                        
                        // Insert payment proof
                        $db->query("INSERT INTO payment_proofs (payment_id, file_name, file_path, file_size, mime_type) 
                                   VALUES (:payment_id, :file_name, :file_path, :file_size, :mime_type)");
                        $db->bind(':payment_id', $payment_id);
                        $db->bind(':file_name', $upload_result['filename']);
                        $db->bind(':file_path', $upload_result['filepath']);
                        $db->bind(':file_size', $upload_result['filesize']);
                        $db->bind(':mime_type', $upload_result['mimetype']);
                        $db->execute();
                        
                        $success_message = 'Pembayaran berhasil disubmit! Silakan tunggu verifikasi dari admin dalam 1x24 jam.';
                    } else {
                        $error_messages[] = $upload_result['message'];
                    }
                }
            } catch (Exception $e) {
                $error_messages[] = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    } else {
        $error_messages[] = 'Token keamanan tidak valid!';
    }
}

// Get payment types
try {
    $db->query("SELECT * FROM payment_types WHERE is_active = 1 ORDER BY nama_pembayaran");
    $payment_types = $db->resultset();
} catch (Exception $e) {
    $payment_types = [];
}

// Pre-select payment type if provided
$selected_type = isset($_GET['type']) ? (int)$_GET['type'] : 0;

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
                        <a class="nav-link" href="payments.php">
                            <i class="bi bi-credit-card"></i> Pembayaran Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="payment-new.php">
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
                <h1 class="h2">Bayar Tagihan Baru</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="payments.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    <hr>
                    <div class="d-flex gap-2">
                        <a href="payments.php" class="btn btn-success">
                            <i class="bi bi-list"></i> Lihat Pembayaran Saya
                        </a>
                        <a href="payment-new.php" class="btn btn-outline-success">
                            <i class="bi bi-plus"></i> Bayar Lagi
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_messages)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <h6><i class="bi bi-exclamation-triangle"></i> Terjadi kesalahan:</h6>
                    <ul class="mb-0">
                        <?php foreach ($error_messages as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <?php if (empty($success_message)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Form Pembayaran</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data" id="paymentForm">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    
                                    <!-- Student Info -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nama Siswa</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($student->nama_lengkap); ?>" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">NIS</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($student->nis); ?>" readonly>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Kelas</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($student->kelas); ?>" readonly>
                                        </div>
                                    </div>
                                    
                                    <!-- Payment Details -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="payment_type_id" class="form-label">Jenis Pembayaran *</label>
                                            <select class="form-select" id="payment_type_id" name="payment_type_id" required onchange="updatePaymentAmount()">
                                                <option value="">Pilih Jenis Pembayaran</option>
                                                <?php foreach ($payment_types as $type): ?>
                                                    <option value="<?php echo $type->id; ?>" 
                                                            data-amount="<?php echo $type->jumlah; ?>"
                                                            <?php echo ($selected_type == $type->id) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($type->nama_pembayaran); ?> - <?php echo format_currency($type->jumlah); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="jumlah_bayar" class="form-label">Jumlah Bayar *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control" id="jumlah_bayar" name="jumlah_bayar" 
                                                       required onkeyup="formatCurrency(this)" placeholder="0">
                                            </div>
                                            <div class="form-text">Masukkan jumlah yang dibayarkan</div>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="tanggal_bayar" class="form-label">Tanggal Pembayaran *</label>
                                            <input type="date" class="form-control" id="tanggal_bayar" name="tanggal_bayar" 
                                                   value="<?php echo date('Y-m-d'); ?>" required>
                                            <div class="form-text">Tanggal saat melakukan transfer</div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="bukti_transfer" class="form-label">Bukti Transfer *</label>
                                            <input type="file" class="form-control" id="bukti_transfer" name="bukti_transfer" 
                                                   accept=".jpg,.jpeg,.png,.pdf" required onchange="previewFile()">
                                            <div class="form-text">Format: JPG, PNG, PDF (Maks. 5MB)</div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" 
                                                  placeholder="Tambahkan keterangan jika diperlukan..."></textarea>
                                    </div>
                                    
                                    <!-- File Preview -->
                                    <div id="filePreview" class="mb-3" style="display: none;">
                                        <label class="form-label">Preview Bukti Transfer:</label>
                                        <div class="border rounded p-3">
                                            <img id="imagePreview" src="" alt="Preview" class="img-fluid" style="max-height: 200px; display: none;">
                                            <div id="fileInfo" class="text-muted"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="confirmData" required>
                                        <label class="form-check-label" for="confirmData">
                                            Saya menyatakan bahwa data yang dimasukkan adalah benar dan bukti transfer yang diupload adalah asli
                                        </label>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <button type="reset" class="btn btn-secondary me-md-2">
                                            <i class="bi bi-arrow-clockwise"></i> Reset
                                        </button>
                                        <button type="submit" class="btn btn-success" id="submitBtn">
                                            <i class="bi bi-send"></i> Submit Pembayaran
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-lg-4">
                    <!-- Payment Instructions -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Petunjuk Pembayaran</h6>
                        </div>
                        <div class="card-body">
                            <ol class="small">
                                <li>Pilih jenis pembayaran yang sesuai</li>
                                <li>Transfer ke rekening sekolah:
                                    <br><strong>Bank BCA: 1234567890</strong>
                                    <br><strong>A/n: SMA Negeri 1</strong>
                                </li>
                                <li>Screenshot/foto bukti transfer</li>
                                <li>Upload bukti transfer di form ini</li>
                                <li>Tunggu verifikasi maksimal 1x24 jam</li>
                            </ol>
                            
                            <div class="alert alert-warning alert-sm">
                                <small>
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Penting:</strong> Pastikan jumlah yang ditransfer sesuai dengan tagihan dan bukti transfer jelas terbaca.
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Available Payment Types -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-tags"></i> Jenis Pembayaran Tersedia</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($payment_types as $type): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                    <div>
                                        <small class="fw-bold"><?php echo htmlspecialchars($type->nama_pembayaran); ?></small>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($type->deskripsi); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <small class="fw-bold text-primary"><?php echo format_currency($type->jumlah); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function updatePaymentAmount() {
    const select = document.getElementById('payment_type_id');
    const amountInput = document.getElementById('jumlah_bayar');
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        const amount = option.getAttribute('data-amount');
        amountInput.value = parseInt(amount).toLocaleString('id-ID');
    } else {
        amountInput.value = '';
    }
}

function previewFile() {
    const file = document.getElementById('bukti_transfer').files[0];
    const preview = document.getElementById('filePreview');
    const imagePreview = document.getElementById('imagePreview');
    const fileInfo = document.getElementById('fileInfo');
    
    if (file) {
        preview.style.display = 'block';
        
        // Show file info
        fileInfo.innerHTML = `
            <strong>File:</strong> ${file.name}<br>
            <strong>Ukuran:</strong> ${(file.size / 1024 / 1024).toFixed(2)} MB<br>
            <strong>Tipe:</strong> ${file.type}
        `;
        
        // Show image preview if it's an image
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    } else {
        preview.style.display = 'none';
    }
}

// Auto-update amount when page loads if type is pre-selected
document.addEventListener('DOMContentLoaded', function() {
    updatePaymentAmount();
    
    // Form validation
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const jumlahBayar = document.getElementById('jumlah_bayar').value.replace(/[^\d]/g, '');
        
        if (!jumlahBayar || parseInt(jumlahBayar) <= 0) {
            e.preventDefault();
            alert('Jumlah pembayaran harus diisi dengan benar!');
            return false;
        }
        
        if (!document.getElementById('confirmData').checked) {
            e.preventDefault();
            alert('Anda harus mencentang konfirmasi data!');
            return false;
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
    });
});
</script>

<?php include '../includes/footer.php'; ?>
