<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
require_admin();

$page_title = 'Data Siswa';
$show_navbar = true;
$show_footer = true;

$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Add new student logic here
                break;
            case 'edit':
                // Edit student logic here
                break;
            case 'delete':
                if (verify_csrf_token($_POST['csrf_token'])) {
                    try {
                        $db = new Database();
                        $student_id = (int)$_POST['student_id'];
                        
                        // Get user_id first
                        $db->query("SELECT user_id FROM students WHERE id = :id");
                        $db->bind(':id', $student_id);
                        $student = $db->single();
                        
                        if ($student) {
                            // Delete student (cascade will delete user)
                            $db->query("DELETE FROM students WHERE id = :id");
                            $db->bind(':id', $student_id);
                            $db->execute();
                            
                            $success_message = 'Data siswa berhasil dihapus!';
                        } else {
                            $error_message = 'Siswa tidak ditemukan!';
                        }
                    } catch (Exception $e) {
                        $error_message = 'Error: ' . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Get students data
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

try {
    $db = new Database();
    
    // Build search query
    $where_clause = "";
    $search_param = "";
    if (!empty($search)) {
        $where_clause = "WHERE s.nama_lengkap LIKE :search OR s.nis LIKE :search OR s.kelas LIKE :search";
        $search_param = "%$search%";
    }
    
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM students s $where_clause";
    $db->query($count_query);
    if (!empty($search)) {
        $db->bind(':search', $search_param);
    }
    $total_students = $db->single()->total;
    $total_pages = ceil($total_students / $limit);
    
    // Get students with pagination
    $db->query("SELECT s.*, u.username, u.email 
                FROM students s 
                JOIN users u ON s.user_id = u.id 
                $where_clause 
                ORDER BY s.created_at DESC 
                LIMIT :limit OFFSET :offset");
    
    if (!empty($search)) {
        $db->bind(':search', $search_param);
    }
    $db->bind(':limit', $limit);
    $db->bind(':offset', $offset);
    
    $students = $db->resultset();
    
} catch (Exception $e) {
    $error_message = 'Error loading students: ' . $e->getMessage();
    $students = [];
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
                        <a class="nav-link active" href="students.php">
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
                <h1 class="h2">Data Siswa</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="bi bi-person-plus"></i> Tambah Siswa
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

            <!-- Search and Filter -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <form method="GET" action="">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Cari nama, NIS, atau kelas..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="students.php" class="btn btn-secondary">
                                    <i class="bi bi-x"></i> Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <small class="text-muted">
                        Menampilkan <?php echo count($students); ?> dari <?php echo number_format($total_students); ?> siswa
                    </small>
                </div>
            </div>

            <!-- Students Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (!empty($students)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>NIS</th>
                                        <th>Nama Lengkap</th>
                                        <th>Kelas</th>
                                        <th>Username</th>
                                        <th>Nomor HP</th>
                                        <th>Nama Wali</th>
                                        <th>Terdaftar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student->nis); ?></td>
                                            <td>
                                                <div class="fw-bold"><?php echo htmlspecialchars($student->nama_lengkap); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($student->email); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($student->kelas); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($student->username); ?></td>
                                            <td><?php echo htmlspecialchars($student->nomor_hp); ?></td>
                                            <td>
                                                <div><?php echo htmlspecialchars($student->nama_wali); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($student->nomor_hp_wali); ?></small>
                                            </td>
                                            <td><?php echo format_date_id($student->created_at); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-info" 
                                                            onclick="viewStudent(<?php echo $student->id; ?>)"
                                                            title="Lihat Detail">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-warning" 
                                                            onclick="editStudent(<?php echo $student->id; ?>)"
                                                            title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger" 
                                                            onclick="deleteStudent(<?php echo $student->id; ?>, '<?php echo htmlspecialchars($student->nama_lengkap); ?>')"
                                                            title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
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
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people" style="font-size: 4rem; color: #ccc;"></i>
                            <h4 class="text-muted mt-3">Belum Ada Data Siswa</h4>
                            <p class="text-muted">Klik tombol "Tambah Siswa" untuk menambahkan siswa baru</p>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                                <i class="bi bi-person-plus"></i> Tambah Siswa Pertama
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Siswa Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Untuk menambah siswa baru, arahkan siswa untuk mendaftar melalui halaman registrasi.</p>
                <div class="d-grid gap-2">
                    <a href="../register.php" class="btn btn-success" target="_blank">
                        <i class="bi bi-person-plus"></i> Buka Halaman Registrasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
    <input type="hidden" name="student_id" id="deleteStudentId">
</form>

<script>
function viewStudent(studentId) {
    // Implement view student details
    alert('Fitur lihat detail akan segera tersedia');
}

function editStudent(studentId) {
    // Implement edit student
    alert('Fitur edit siswa akan segera tersedia');
}

function deleteStudent(studentId, studentName) {
    if (confirm('Apakah Anda yakin ingin menghapus siswa "' + studentName + '"?\n\nPerhatian: Semua data pembayaran siswa ini juga akan terhapus!')) {
        document.getElementById('deleteStudentId').value = studentId;
        document.getElementById('deleteForm').submit();
    }
}

// Auto-refresh every 30 seconds
setTimeout(function() {
    location.reload();
}, 30000);
</script>

<?php include '../includes/footer.php'; ?>
