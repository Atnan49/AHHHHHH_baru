<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
    <link href="/assets/css/icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: bold;
            font-size: 1.25rem;
        }
        
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 0.375rem;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #343a40;
        }
        
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1rem;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: #495057;
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: var(--primary-color);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .alert {
            border-radius: 0.375rem;
        }
        
        .footer {
            background-color: #343a40;
            color: #adb5bd;
            padding: 2rem 0;
            margin-top: auto;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), #0b5ed7);
            color: white;
            text-align: center;
            padding: 2rem;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .status-pending { color: var(--warning-color); }
        .status-verified { color: var(--success-color); }
        .status-rejected { color: var(--danger-color); }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 56px;
                left: -250px;
                width: 250px;
                height: calc(100vh - 56px);
                transition: left 0.3s;
                z-index: 1000;
            }
            
            .sidebar.show {
                left: 0;
            }
            
            .content {
                margin-left: 0 !important;
            }
        }
    </style>
    
    <?php if (isset($additional_css)): ?>
        <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body>
    <?php if (isset($show_navbar) && $show_navbar): ?>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <i class="bi bi-bank2"></i> <?php echo SITE_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> 
                                <?php echo $_SESSION['username']; ?>
                                <span class="badge bg-light text-dark ms-1"><?php echo ucfirst($_SESSION['role']); ?></span>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (is_admin()): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="admin/students.php"><i class="bi bi-people"></i> Data Siswa</a></li>
                                    <li><a class="dropdown-item" href="admin/payments.php"><i class="bi bi-credit-card"></i> Pembayaran</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="student/dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="student/payments.php"><i class="bi bi-credit-card"></i> Pembayaran Saya</a></li>
                                    <li><a class="dropdown-item" href="student/profile.php"><i class="bi bi-person"></i> Profil</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="bi bi-person-plus"></i> Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="container mt-3">
            <?php 
                show_alert($_SESSION['flash_message']['message'], $_SESSION['flash_message']['type']);
                unset($_SESSION['flash_message']);
            ?>
        </div>
    <?php endif; ?>
