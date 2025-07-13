// JavaScript for School Payment System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeFileUploads();
    initializeFormValidation();
    initializeDataTables();
    initializeTooltips();
    initializeModals();
    initializeAlerts();
    
    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 100);
    });
});

// File Upload Enhancement
function initializeFileUploads() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        const wrapper = input.closest('.file-upload') || createFileUploadWrapper(input);
        
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name || '';
            const label = wrapper.querySelector('label');
            
            if (fileName) {
                label.innerHTML = `<i class="fas fa-check-circle"></i> ${fileName}`;
                wrapper.classList.add('has-file');
            } else {
                label.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Pilih file atau drag & drop';
                wrapper.classList.remove('has-file');
            }
        });
        
        // Drag and drop functionality
        const label = wrapper.querySelector('label');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            label.addEventListener(eventName, preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            label.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            label.addEventListener(eventName, unhighlight, false);
        });
        
        label.addEventListener('drop', handleDrop, false);
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        function highlight(e) {
            label.style.borderColor = '#007bff';
            label.style.backgroundColor = '#f0f7ff';
        }
        
        function unhighlight(e) {
            label.style.borderColor = '#ddd';
            label.style.backgroundColor = '#f8f9fa';
        }
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            input.files = files;
            
            // Trigger change event
            const event = new Event('change', { bubbles: true });
            input.dispatchEvent(event);
        }
    });
}

function createFileUploadWrapper(input) {
    const wrapper = document.createElement('div');
    wrapper.className = 'file-upload';
    
    const label = document.createElement('label');
    label.setAttribute('for', input.id);
    label.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Pilih file atau drag & drop';
    
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);
    wrapper.appendChild(label);
    
    return wrapper;
}

// Form Validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            this.classList.add('was-validated');
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

function validateField(field) {
    let isValid = true;
    const value = field.value.trim();
    
    // Remove previous validation classes
    field.classList.remove('is-valid', 'is-invalid');
    
    // Required validation
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        showFieldError(field, 'Field ini wajib diisi');
    }
    
    // Email validation
    if (field.type === 'email' && value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(value)) {
            isValid = false;
            showFieldError(field, 'Format email tidak valid');
        }
    }
    
    // Phone validation
    if (field.name === 'phone' && value) {
        const phoneRegex = /^[0-9+\-\s]+$/;
        if (!phoneRegex.test(value) || value.length < 10) {
            isValid = false;
            showFieldError(field, 'Nomor telepon tidak valid');
        }
    }
    
    // File validation
    if (field.type === 'file' && field.files.length > 0) {
        const file = field.files[0];
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!allowedTypes.includes(file.type)) {
            isValid = false;
            showFieldError(field, 'Tipe file tidak diizinkan. Gunakan JPG, PNG, atau PDF');
        } else if (file.size > maxSize) {
            isValid = false;
            showFieldError(field, 'Ukuran file maksimal 5MB');
        }
    }
    
    if (isValid) {
        field.classList.add('is-valid');
        hideFieldError(field);
    } else {
        field.classList.add('is-invalid');
    }
    
    return isValid;
}

function showFieldError(field, message) {
    hideFieldError(field);
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'invalid-feedback';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

function hideFieldError(field) {
    const existingError = field.parentNode.querySelector('.invalid-feedback');
    if (existingError) {
        existingError.remove();
    }
}

// DataTables Enhancement
function initializeDataTables() {
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        // Add search functionality
        if (table.rows.length > 1) {
            addTableSearch(table);
        }
        
        // Add sorting functionality
        addTableSorting(table);
    });
}

function addTableSearch(table) {
    const wrapper = table.parentNode;
    const searchDiv = document.createElement('div');
    searchDiv.className = 'mb-3';
    searchDiv.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <input type="text" class="form-control" placeholder="Cari..." id="tableSearch_${Date.now()}">
            </div>
        </div>
    `;
    
    wrapper.insertBefore(searchDiv, table);
    
    const searchInput = searchDiv.querySelector('input');
    searchInput.addEventListener('input', function() {
        filterTable(table, this.value);
    });
}

function filterTable(table, searchTerm) {
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = text.includes(searchTerm.toLowerCase());
        row.style.display = match ? '' : 'none';
    });
}

function addTableSorting(table) {
    const headers = table.querySelectorAll('th');
    
    headers.forEach((header, index) => {
        if (header.textContent.trim()) {
            header.style.cursor = 'pointer';
            header.innerHTML += ' <i class="fas fa-sort text-muted"></i>';
            
            header.addEventListener('click', function() {
                sortTable(table, index);
            });
        }
    });
}

function sortTable(table, columnIndex) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aText = a.cells[columnIndex].textContent.trim();
        const bText = b.cells[columnIndex].textContent.trim();
        
        // Try to parse as numbers
        const aNum = parseFloat(aText.replace(/[^\d.-]/g, ''));
        const bNum = parseFloat(bText.replace(/[^\d.-]/g, ''));
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return aNum - bNum;
        }
        
        return aText.localeCompare(bText);
    });
    
    // Clear tbody and append sorted rows
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
}

// Tooltips
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipElements.forEach(element => {
        new bootstrap.Tooltip(element);
    });
}

// Modals
function initializeModals() {
    // Auto-focus first input in modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = this.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });
}

// Auto-hide alerts
function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

// Utility Functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(amount);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(date);
}

// Confirmation dialogs
function confirmDelete(message = 'Apakah Anda yakin ingin menghapus data ini?') {
    return confirm(message);
}

// Loading states
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="loading"></span> Loading...';
    button.disabled = true;
    button.dataset.originalText = originalText;
}

function hideLoading(button) {
    const originalText = button.dataset.originalText || 'Submit';
    button.innerHTML = originalText;
    button.disabled = false;
}

// AJAX helper
function ajaxRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    return fetch(url, { ...defaultOptions, ...options })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
}

// Show notifications
function showNotification(message, type = 'info', duration = 5000) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            const bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }
    }, duration);
}

// Payment status update
function updatePaymentStatus(paymentId, status, button) {
    if (!confirm(`Apakah Anda yakin ingin mengubah status pembayaran menjadi ${status}?`)) {
        return;
    }
    
    showLoading(button);
    
    const formData = new FormData();
    formData.append('payment_id', paymentId);
    formData.append('status', status);
    formData.append('action', 'update_status');
    
    fetch('payments.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading(button);
        if (data.success) {
            showNotification('Status pembayaran berhasil diupdate', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification(data.message || 'Terjadi kesalahan', 'danger');
        }
    })
    .catch(error => {
        hideLoading(button);
        showNotification('Terjadi kesalahan sistem', 'danger');
        console.error('Error:', error);
    });
}

// Export functions for global use
window.SchoolPayment = {
    formatCurrency,
    formatDate,
    confirmDelete,
    showLoading,
    hideLoading,
    showNotification,
    updatePaymentStatus
};
