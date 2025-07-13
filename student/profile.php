<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require student access
require_student();

$page_title = 'Profil Saya';
$show_navbar = true;
$show_footer = true;

$success_message = '';
$error_messages = [];

// Get student data
try {
    $db = new Database();
    $db->query("SELECT s.*, u.username, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = :user_id");
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
        $action = $_POST['action'];
        
        if ($action === 'update_profile') {
            // Update profile data
            $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
            $alamat = sanitize_input($_POST['alamat']);
            $nomor_hp = sanitize_input($_POST['nomor_hp']);
            $nama_wali = sanitize_input($_POST['nama_wali']);
            $nomor_hp_wali = sanitize_input($_POST['nomor_hp_wali']);
            $email = sanitize_input($_POST['email']);
            
            // Validasi
            if (empty($nama_lengkap)) $error_messages[] = 'Nama lengkap harus diisi!';
            if (empty($alamat)) $error_messages[] = 'Alamat harus diisi!';
            if (empty($nomor_hp)) $error_messages[] = 'Nomor HP harus diisi!';
            if (empty($nama_wali)) $error_messages[] = 'Nama wali harus diisi!';
            if (empty($nomor_hp_wali)) $error_messages[] = 'Nomor HP wali harus diisi!';
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error_messages[] = 'Email tidak valid!';
            
            if (empty($error_messages)) {
                try {
                    // Check if email is already used by another user
                    $db->query("SELECT id FROM users WHERE email = :email AND id != :user_id");
                    $db->bind(':email', $email);
                    $db->bind(':user_id', $_SESSION['user_id']);
                    if ($db->single()) {
                        $error_messages[] = 'Email sudah digunakan oleh user lain!';
                    } else {
                        // Update students table
                        $db->query("UPDATE students SET nama_lengkap = :nama_lengkap, alamat = :alamat, nomor_hp = :nomor_hp, nama_wali = :nama_wali, nomor_hp_wali = :nomor_hp_wali WHERE user_id = :user_id");
                        $db->bind(':nama_lengkap', $nama_lengkap);
                        $db->bind(':alamat', $alamat);
                        $db->bind(':nomor_hp', $nomor_hp);
                        $db->bind(':nama_wali', $nama_wali);
                        $db->bind(':nomor_hp_wali', $nomor_hp_wali);
                        $db->bind(':user_id', $_SESSION['user_id']);
                        $db->execute();
                        
                        // Update users table
                        $db->query("UPDATE users SET email = :email WHERE id = :user_id");
                        $db->bind(':email', $email);
                        $db->bind(':user_id', $_SESSION['user_id']);
                        $db->execute();
                        
                        $success_message = 'Profil berhasil diperbarui!';
                        
                        // Refresh student data
                        $db->query("SELECT s.*, u.username, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = :user_id");
                        $db->bind(':user_id', $_SESSION['user_id']);
                        $student = $db->single();
                    }
                } catch (Exception $e) {
                    $error_messages[] = 'Terjadi kesalahan: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'change_password') {
            // Change password
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validasi
            if (empty($current_password)) $error_messages[] = 'Password lama harus diisi!';
            if (empty($new_password)) $error_messages[] = 'Password baru harus diisi!';
            if (strlen($new_password) < 6) $error_messages[] = 'Password baru minimal 6 karakter!';
            if ($new_password !== $confirm_password) $error_messages[] = 'Konfirmasi password tidak sesuai!';
            
            if (empty($error_messages)) {
                try {
                    // Verify current password
                    $db->query("SELECT password FROM users WHERE id = :user_id");
                    $db->bind(':user_id', $_SESSION['user_id']);
                    $user = $db->single();
                    
                    if (password_verify($current_password, $user->password)) {
                        // Update password
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $db->query("UPDATE users SET password = :password WHERE id = :user_id");
                        $db->bind(':password', $hashed_password);
                        $db->bind(':user_id', $_SESSION['user_id']);
                        $db->execute();
                        
                        $success_message = 'Password berhasil diubah!';
                    } else {
                        $error_messages[] = 'Password lama tidak sesuai!';
                    }
                } catch (Exception $e) {
                    $error_messages[] = 'Terjadi kesalahan: ' . $e->getMessage();
                }
            }
        }
    } else {
        $error_messages[] = 'Token keamanan tidak valid!';
    }
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
                        <a class="nav-link active" href="profile.php">
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
                <h1 class="h2">Profil Saya</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                <!-- Profile Information -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person"></i> Informasi Profil</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="profileForm">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <!-- Account Info (Read-only) -->
                                <h6 class="text-primary mb-3">Informasi Akun</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($student->username); ?>" readonly>
                                        <div class="form-text">Username tidak dapat diubah</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">NIS</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($student->nis); ?>" readonly>
                                        <div class="form-text">NIS tidak dapat diubah</div>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Kelas</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($student->kelas); ?>" readonly>
                                        <div class="form-text">Hubungi admin untuk mengubah kelas</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($student->email); ?>" required>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- Student Info (Editable) -->
                                <h6 class="text-primary mb-3">Data Siswa</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                               value="<?php echo htmlspecialchars($student->nama_lengkap); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nomor_hp" class="form-label">Nomor HP *</label>
                                        <input type="text" class="form-control" id="nomor_hp" name="nomor_hp" 
                                               value="<?php echo htmlspecialchars($student->nomor_hp); ?>" 
                                               required onkeypress="return numberOnly(event)">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat Lengkap *</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($student->alamat); ?></textarea>
                                </div>
                                
                                <hr>
                                
                                <!-- Guardian Info (Editable) -->
                                <h6 class="text-primary mb-3">Data Wali/Orang Tua</h6>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nama_wali" class="form-label">Nama Wali/Orang Tua *</label>
                                        <input type="text" class="form-control" id="nama_wali" name="nama_wali" 
                                               value="<?php echo htmlspecialchars($student->nama_wali); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nomor_hp_wali" class="form-label">Nomor HP Wali *</label>
                                        <input type="text" class="form-control" id="nomor_hp_wali" name="nomor_hp_wali" 
                                               value="<?php echo htmlspecialchars($student->nomor_hp_wali); ?>" 
                                               required onkeypress="return numberOnly(event)">
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="reset" class="btn btn-secondary me-md-2">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Keamanan</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" id="passwordForm">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Password Lama *</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Password Baru *</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           required minlength="6">
                                    <div class="form-text">Minimal 6 karakter</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-key"></i> Ubah Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Account Summary -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Ringkasan Akun</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Terdaftar:</strong></td>
                                    <td><?php echo format_date_id($student->created_at); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Update Terakhir:</strong></td>
                                    <td><?php echo format_date_id($student->updated_at); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <hr>
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle"></i>
                                            Untuk mengubah NIS atau kelas, silakan hubungi admin sekolah.
                                        </small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-lightning"></i> Aksi Cepat</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="payments.php" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-list"></i> Lihat Pembayaran
                                </a>
                                <a href="payment-new.php" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-plus"></i> Bayar Tagihan
                                </a>
                                <a href="dashboard.php" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password confirmation validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword && newPassword !== confirmPassword) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
    
    // Password form validation
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('Konfirmasi password tidak sesuai!');
            return false;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('Password baru minimal 6 karakter!');
            return false;
        }
    });
    
    // Profile form validation
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let hasError = false;
        
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                hasError = true;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi!');
            return false;
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>
