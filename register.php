<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect jika sudah login
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('student/dashboard.php');
    }
}

$error_messages = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi CSRF token
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $error_messages[] = 'Token keamanan tidak valid!';
    } else {
        // Ambil data dari form
        $username = sanitize_input($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $email = sanitize_input($_POST['email']);
        $nis = sanitize_input($_POST['nis']);
        $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
        $kelas = sanitize_input($_POST['kelas']);
        $alamat = sanitize_input($_POST['alamat']);
        $nomor_hp = sanitize_input($_POST['nomor_hp']);
        $nama_wali = sanitize_input($_POST['nama_wali']);
        $nomor_hp_wali = sanitize_input($_POST['nomor_hp_wali']);
        
        // Validasi input
        if (empty($username)) $error_messages[] = 'Username harus diisi!';
        if (strlen($username) < 3) $error_messages[] = 'Username minimal 3 karakter!';
        if (empty($password)) $error_messages[] = 'Password harus diisi!';
        if (strlen($password) < 6) $error_messages[] = 'Password minimal 6 karakter!';
        if ($password !== $confirm_password) $error_messages[] = 'Konfirmasi password tidak sesuai!';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error_messages[] = 'Email tidak valid!';
        if (empty($nis)) $error_messages[] = 'NIS harus diisi!';
        if (empty($nama_lengkap)) $error_messages[] = 'Nama lengkap harus diisi!';
        if (empty($kelas)) $error_messages[] = 'Kelas harus diisi!';
        if (empty($alamat)) $error_messages[] = 'Alamat harus diisi!';
        if (empty($nomor_hp)) $error_messages[] = 'Nomor HP harus diisi!';
        if (empty($nama_wali)) $error_messages[] = 'Nama wali harus diisi!';
        if (empty($nomor_hp_wali)) $error_messages[] = 'Nomor HP wali harus diisi!';
        
        // Jika tidak ada error, proses pendaftaran
        if (empty($error_messages)) {
            try {
                $db = new Database();
                
                // Cek apakah username sudah ada
                $db->query("SELECT id FROM users WHERE username = :username");
                $db->bind(':username', $username);
                if ($db->single()) {
                    $error_messages[] = 'Username sudah digunakan!';
                }
                
                // Cek apakah email sudah ada
                $db->query("SELECT id FROM users WHERE email = :email");
                $db->bind(':email', $email);
                if ($db->single()) {
                    $error_messages[] = 'Email sudah digunakan!';
                }
                
                // Cek apakah NIS sudah ada
                $db->query("SELECT id FROM students WHERE nis = :nis");
                $db->bind(':nis', $nis);
                if ($db->single()) {
                    $error_messages[] = 'NIS sudah terdaftar!';
                }
                
                if (empty($error_messages)) {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert user
                    $db->query("INSERT INTO users (username, password, role, email) VALUES (:username, :password, 'student', :email)");
                    $db->bind(':username', $username);
                    $db->bind(':password', $hashed_password);
                    $db->bind(':email', $email);
                    $db->execute();
                    
                    $user_id = $db->lastInsertId();
                    
                    // Insert student
                    $db->query("INSERT INTO students (user_id, nis, nama_lengkap, kelas, alamat, nomor_hp, nama_wali, nomor_hp_wali) 
                               VALUES (:user_id, :nis, :nama_lengkap, :kelas, :alamat, :nomor_hp, :nama_wali, :nomor_hp_wali)");
                    $db->bind(':user_id', $user_id);
                    $db->bind(':nis', $nis);
                    $db->bind(':nama_lengkap', $nama_lengkap);
                    $db->bind(':kelas', $kelas);
                    $db->bind(':alamat', $alamat);
                    $db->bind(':nomor_hp', $nomor_hp);
                    $db->bind(':nama_wali', $nama_wali);
                    $db->bind(':nomor_hp_wali', $nomor_hp_wali);
                    $db->execute();
                    
                    $success_message = 'Pendaftaran berhasil! Silakan login dengan username dan password Anda.';
                }
            } catch (Exception $e) {
                $error_messages[] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            }
        }
    }
}

$page_title = 'Daftar Siswa Baru';
$show_navbar = true;
$show_footer = true;

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-person-plus"></i> Pendaftaran Siswa Baru</h4>
                </div>
                
                <div class="card-body">
                    <?php if (!empty($error_messages)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($error_messages as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <?php echo $success_message; ?>
                            <hr>
                            <a href="login.php" class="btn btn-success">
                                <i class="bi bi-box-arrow-in-right"></i> Login Sekarang
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="" id="registerForm">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <!-- Informasi Akun -->
                            <h5 class="text-primary mb-3">
                                <i class="bi bi-key"></i> Informasi Akun
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username *</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                           required minlength="3">
                                    <div class="form-text">Minimal 3 karakter, tanpa spasi</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                           required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                    <div class="form-text">Minimal 6 karakter</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <!-- Data Siswa -->
                            <h5 class="text-primary mb-3">
                                <i class="bi bi-person"></i> Data Siswa
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nis" class="form-label">NIS (Nomor Induk Siswa) *</label>
                                    <input type="text" class="form-control" id="nis" name="nis" 
                                           value="<?php echo isset($_POST['nis']) ? htmlspecialchars($_POST['nis']) : ''; ?>"
                                           required onkeypress="return numberOnly(event)">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                           value="<?php echo isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : ''; ?>"
                                           required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kelas" class="form-label">Kelas *</label>
                                    <select class="form-select" id="kelas" name="kelas" required>
                                        <option value="">Pilih Kelas</option>
                                        <option value="X-A" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'X-A') ? 'selected' : ''; ?>>X-A</option>
                                        <option value="X-B" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'X-B') ? 'selected' : ''; ?>>X-B</option>
                                        <option value="X-C" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'X-C') ? 'selected' : ''; ?>>X-C</option>
                                        <option value="XI-A" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'XI-A') ? 'selected' : ''; ?>>XI-A</option>
                                        <option value="XI-B" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'XI-B') ? 'selected' : ''; ?>>XI-B</option>
                                        <option value="XI-C" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'XI-C') ? 'selected' : ''; ?>>XI-C</option>
                                        <option value="XII-A" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'XII-A') ? 'selected' : ''; ?>>XII-A</option>
                                        <option value="XII-B" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'XII-B') ? 'selected' : ''; ?>>XII-B</option>
                                        <option value="XII-C" <?php echo (isset($_POST['kelas']) && $_POST['kelas'] == 'XII-C') ? 'selected' : ''; ?>>XII-C</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="nomor_hp" class="form-label">Nomor HP Siswa *</label>
                                    <input type="text" class="form-control" id="nomor_hp" name="nomor_hp" 
                                           value="<?php echo isset($_POST['nomor_hp']) ? htmlspecialchars($_POST['nomor_hp']) : ''; ?>"
                                           required onkeypress="return numberOnly(event)" placeholder="08xxxxxxxxxx">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat Lengkap *</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                            </div>
                            
                            <hr>
                            
                            <!-- Data Wali -->
                            <h5 class="text-primary mb-3">
                                <i class="bi bi-people"></i> Data Wali/Orang Tua
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nama_wali" class="form-label">Nama Wali/Orang Tua *</label>
                                    <input type="text" class="form-control" id="nama_wali" name="nama_wali" 
                                           value="<?php echo isset($_POST['nama_wali']) ? htmlspecialchars($_POST['nama_wali']) : ''; ?>"
                                           required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="nomor_hp_wali" class="form-label">Nomor HP Wali *</label>
                                    <input type="text" class="form-control" id="nomor_hp_wali" name="nomor_hp_wali" 
                                           value="<?php echo isset($_POST['nomor_hp_wali']) ? htmlspecialchars($_POST['nomor_hp_wali']) : ''; ?>"
                                           required onkeypress="return numberOnly(event)" placeholder="08xxxxxxxxxx">
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="agree_terms" required>
                                <label class="form-check-label" for="agree_terms">
                                    Saya menyetujui <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">syarat dan ketentuan</a> yang berlaku
                                </label>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="login.php" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-arrow-left"></i> Kembali ke Login
                                </a>
                                <button type="submit" class="btn btn-success" id="registerBtn">
                                    <i class="bi bi-person-plus"></i> Daftar Sekarang
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Syarat dan Ketentuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Penggunaan Akun</h6>
                <p>Siswa bertanggung jawab menjaga kerahasiaan username dan password akun.</p>
                
                <h6>2. Pembayaran</h6>
                <p>Semua pembayaran harus disertai dengan bukti transfer yang valid dan jelas.</p>
                
                <h6>3. Verifikasi</h6>
                <p>Proses verifikasi pembayaran dilakukan maksimal 1x24 jam setelah upload bukti transfer.</p>
                
                <h6>4. Data Pribadi</h6>
                <p>Data yang dimasukkan harus benar dan dapat dipertanggungjawabkan.</p>
                
                <h6>5. Pelanggaran</h6>
                <p>Penyalahgunaan sistem dapat berakibat pada pemblokiran akun.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Konfirmasi password tidak sesuai!');
            return false;
        }
        
        if (!document.getElementById('agree_terms').checked) {
            e.preventDefault();
            alert('Anda harus menyetujui syarat dan ketentuan!');
            return false;
        }
        
        // Show loading state
        const registerBtn = document.getElementById('registerBtn');
        registerBtn.disabled = true;
        registerBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mendaftar...';
    });
    
    // Real-time password confirmation validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword && password !== confirmPassword) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });
    
    // Username validation (no spaces)
    document.getElementById('username').addEventListener('input', function() {
        this.value = this.value.replace(/\s/g, '');
    });
});
</script>

<?php include 'includes/footer.php'; ?>
