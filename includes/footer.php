    <?php if (isset($show_footer) && $show_footer): ?>
    <!-- Footer -->
    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <h5><i class="bi bi-bank2"></i> <?php echo SITE_NAME; ?></h5>
                    <p class="mb-2">Sistem pembayaran sekolah yang mudah, aman, dan terpercaya.</p>
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-lg-4">
                    <h6>Menu Utama</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>" class="text-decoration-none text-light">Beranda</a></li>
                        <?php if (is_logged_in()): ?>
                            <?php if (is_admin()): ?>
                                <li><a href="admin/dashboard.php" class="text-decoration-none text-light">Dashboard Admin</a></li>
                                <li><a href="admin/students.php" class="text-decoration-none text-light">Data Siswa</a></li>
                            <?php else: ?>
                                <li><a href="student/dashboard.php" class="text-decoration-none text-light">Dashboard Siswa</a></li>
                                <li><a href="student/payments.php" class="text-decoration-none text-light">Pembayaran</a></li>
                            <?php endif; ?>
                        <?php else: ?>
                            <li><a href="login.php" class="text-decoration-none text-light">Login</a></li>
                            <li><a href="register.php" class="text-decoration-none text-light">Daftar</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6>Informasi Kontak</h6>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-geo-alt"></i> Jl. Pendidikan No. 123, Jakarta</li>
                        <li><i class="bi bi-telephone"></i> (021) 1234-5678</li>
                        <li><i class="bi bi-envelope"></i> info@sekolah.com</li>
                        <li><i class="bi bi-clock"></i> Senin - Jumat: 07:00 - 16:00</li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="/assets/js/main.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Confirm delete actions
        function confirmDelete(message = 'Apakah Anda yakin ingin menghapus data ini?') {
            return confirm(message);
        }

        // Format currency input
        function formatCurrency(input) {
            let value = input.value.replace(/[^\d]/g, '');
            value = parseInt(value) || 0;
            input.value = value.toLocaleString('id-ID');
        }

        // Preview image before upload
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).src = e.target.result;
                    document.getElementById(previewId).style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Sidebar toggle for mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
        }

        // Form validation
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return false;
            
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                alert('Mohon lengkapi semua field yang wajib diisi!');
            }
            
            return isValid;
        }

        // Number only input
        function numberOnly(event) {
            const charCode = (event.which) ? event.which : event.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                event.preventDefault();
                return false;
            }
            return true;
        }

        // Loading button state
        function setLoadingButton(buttonId, loading = true) {
            const button = document.getElementById(buttonId);
            if (!button) return;
            
            if (loading) {
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Loading...';
            } else {
                button.disabled = false;
                button.innerHTML = button.getAttribute('data-original-text');
            }
        }

        // DataTable initialization (if needed)
        function initDataTable(tableId) {
            if (typeof DataTable !== 'undefined') {
                new DataTable('#' + tableId, {
                    responsive: true,
                    pageLength: 25,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                    }
                });
            }
        }

        // Search functionality
        function searchTable(inputId, tableId) {
            const input = document.getElementById(inputId);
            const table = document.getElementById(tableId);
            
            if (!input || !table) return;
            
            input.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
                
                for (let i = 0; i < rows.length; i++) {
                    const cells = rows[i].getElementsByTagName('td');
                    let found = false;
                    
                    for (let j = 0; j < cells.length; j++) {
                        if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                    
                    rows[i].style.display = found ? '' : 'none';
                }
            });
        }
    </script>
    
    <?php if (isset($additional_js)): ?>
        <?php echo $additional_js; ?>
    <?php endif; ?>
</body>
</html>
