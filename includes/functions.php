<?php
/**
 * Fungsi-fungsi umum untuk Sistem Pembayaran Sekolah
 */

// Start session jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Redirect ke halaman lain
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check apakah user sudah login
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check apakah user adalah admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check apakah user adalah student
 */
function is_student() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'student';
}

/**
 * Require login - redirect ke login jika belum login
 */
function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

/**
 * Require admin - redirect jika bukan admin
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        redirect('../index.php');
    }
}

/**
 * Require student - redirect jika bukan student
 */
function require_student() {
    require_login();
    if (!is_student()) {
        redirect('../index.php');
    }
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format currency dalam Rupiah
 */
function format_currency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Format tanggal Indonesia
 */
function format_date_id($date) {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp)];
    $year = date('Y', $timestamp);
    
    return $day . ' ' . $month . ' ' . $year;
}

/**
 * Upload file dengan validasi
 */
function upload_file($file, $destination_dir = 'uploads/') {
    global $allowed_file_types;
    
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error uploading file'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File terlalu besar. Maksimal 5MB'];
    }
    
    // Check file type
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_file_types)) {
        return ['success' => false, 'message' => 'Format file tidak diizinkan. Gunakan JPG, PNG, atau PDF'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $destination = $destination_dir . $filename;
    
    // Create directory if not exists
    if (!file_exists($destination_dir)) {
        mkdir($destination_dir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $destination,
            'filesize' => $file['size'],
            'mimetype' => $file['type']
        ];
    } else {
        return ['success' => false, 'message' => 'Gagal menyimpan file'];
    }
}

/**
 * Get status badge HTML
 */
function get_status_badge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="badge bg-warning">Menunggu Verifikasi</span>';
        case 'verified':
            return '<span class="badge bg-success">Terverifikasi</span>';
        case 'rejected':
            return '<span class="badge bg-danger">Ditolak</span>';
        default:
            return '<span class="badge bg-secondary">Unknown</span>';
    }
}

/**
 * Show alert message
 */
function show_alert($message, $type = 'info') {
    echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
    echo $message;
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
}

/**
 * Show flash message and remove it from session
 */
function show_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        echo '<div class="alert alert-' . $message['type'] . ' alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-info-circle me-2"></i>' . htmlspecialchars($message['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
        unset($_SESSION['flash_message']);
    }
}

/**
 * Generate pagination
 */
function generate_pagination($current_page, $total_pages, $base_url) {
    if ($total_pages <= 1) return '';
    
    $pagination = '<nav><ul class="pagination justify-content-center">';
    
    // Previous
    if ($current_page > 1) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . ($current_page - 1) . '">Previous</a></li>';
    }
    
    // Pages
    for ($i = 1; $i <= $total_pages; $i++) {
        $active = ($i == $current_page) ? 'active' : '';
        $pagination .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base_url . '?page=' . $i . '">' . $i . '</a></li>';
    }
    
    // Next
    if ($current_page < $total_pages) {
        $pagination .= '<li class="page-item"><a class="page-link" href="' . $base_url . '?page=' . ($current_page + 1) . '">Next</a></li>';
    }
    
    $pagination .= '</ul></nav>';
    return $pagination;
}
?>
