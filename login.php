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

$error_message = '';
$page_title = 'Login';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi!';
    } else {
        try {
            $db = new Database();
            $db->query("SELECT * FROM users WHERE username = :username");
            $db->bind(':username', $username);
            $user = $db->single();
            
            if ($user && password_verify($password, $user->password)) {
                // Login berhasil
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['role'] = $user->role;
                
                // Set flash message
                $_SESSION['flash_message'] = [
                    'message' => 'Login berhasil! Selamat datang, ' . $user->username,
                    'type' => 'success'
                ];
                
                // Redirect berdasarkan role
                if ($user->role === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('student/dashboard.php');
                }
            } else {
                $error_message = 'Username atau password salah!';
            }
        } catch (Exception $e) {
            $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}

$page_title = 'Login';
$show_navbar = false;
$show_footer = false;

include 'includes/header.php';
?>

<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <div class="logo-placeholder">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h2><?php echo SITE_NAME; ?></h2>
                <p class="text-muted">Sistem Pembayaran Sekolah</p>
            </div>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="fas fa-user me-1"></i> Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           required autocomplete="username" placeholder="Masukkan username">
                    <div class="invalid-feedback">
                        Username harus diisi!
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-1"></i> Password
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="password" name="password" 
                               required autocomplete="current-password" placeholder="Masukkan password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div class="invalid-feedback">
                            Password harus diisi!
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">
                            Ingat saya
                        </label>
                    </div>
                </div>
                
                <div class="d-grid gap-2 mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Masuk
                    </button>
                </div>
            </form>
            
            <div class="text-center">
                <div class="divider">
                    <span>atau</span>
                </div>
                
                <div class="mt-3">
                    <a href="register.php" class="btn btn-outline-success w-100">
                        <i class="fas fa-user-plus me-2"></i>
                        Daftar Siswa Baru
                    </a>
                </div>
                
                <div class="mt-3">
                    <a href="index.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Demo Accounts Info -->
    <div class="position-fixed bottom-0 end-0 m-3">
        <div class="card shadow-lg" style="max-width: 280px;">
            <div class="card-header bg-info text-white py-2">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Demo Accounts
                </h6>
            </div>
            <div class="card-body p-3">
                <div class="mb-2">
                    <strong class="text-primary">Admin:</strong><br>
                    <small class="text-muted">
                        Username: <code>admin</code><br>
                        Password: <code>admin123</code>
                    </small>
                </div>
                <div>
                    <strong class="text-success">Student:</strong><br>
                    <small class="text-muted">
                        Daftar siswa baru atau gunakan yang sudah ada
                    </small>
                </div>
            </div>
        </div>
    </div>

    <style>
        .login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .divider {
            position: relative;
            text-align: center;
            margin: 20px 0;
        }
        
        .divider:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #dee2e6;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            color: #6c757d;
            font-size: 0.875rem;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
        
        // Focus on username field
        document.getElementById('username').focus();
    });
    </script>

<?php include 'includes/footer.php'; ?>
