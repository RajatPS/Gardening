// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle for Mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // Close sidebar when clicking outside on mobile
    if (window.innerWidth < 768) {
        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = sidebarToggle && sidebarToggle.contains(event.target);
            
            if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
    }

    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Global Search functionality
    const globalSearch = document.getElementById('globalSearch');
    if (globalSearch) {
        let searchTimeout;
        globalSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchTerm = this.value.trim();
            
            if (searchTerm.length > 2) {
                searchTimeout = setTimeout(() => {
                    // Implement AJAX search
                    performSearch(searchTerm);
                }, 300);
            }
        });
    }

    // Confirmation modals for destructive actions
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const form = this.closest('form');
            if (!form) {
                return;
            }

            e.preventDefault();

            Swal.fire({
                title: this.dataset.confirmTitle || 'Please confirm',
                text: this.dataset.confirm || 'Are you sure?',
                icon: this.dataset.confirmIcon || 'warning',
                showCancelButton: true,
                confirmButtonText: this.dataset.confirmButtonText || 'Yes, continue',
                cancelButtonText: this.dataset.cancelButtonText || 'Cancel',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Setup pagination
    setupPagination();

    // Setup tooltips
    setupTooltips();

    // Setup admin detail modal triggers
    registerAdminDetailModal();
});

// Perform global search
function performSearch(searchTerm) {
    const currentUrl = window.location.pathname;
    const params = new URLSearchParams({
        search: searchTerm,
        page: 1
    });

    fetch(`${currentUrl}?${params}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Parse and update results
        console.log('Search results:', html);
    })
    .catch(error => console.error('Search error:', error));
}

// Setup pagination
function setupPagination() {
    const paginationLinks = document.querySelectorAll('.pagination a');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Preserve filters and sorting
        });
    });
}

function registerAdminDetailModal() {
    document.body.addEventListener('click', function(event) {
        const button = event.target.closest('[data-admin-detail-url]');
        if (!button) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const url = button.dataset.adminDetailUrl;
        if (!url) {
            return;
        }

        const modalElement = document.getElementById('adminDetailModal');
        if (!modalElement) {
            window.location.href = url;
            return;
        }

        const modal = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true
        });
        const modalBody = modalElement.querySelector('.modal-body');
        const modalTitle = modalElement.querySelector('.modal-title');

        modalTitle.textContent = 'Loading details...';
        modalBody.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 mb-0">Loading details...</p>
            </div>
        `;

        modal.show();

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Unable to load details.');
            }
            return response.text();
        })
        .then(html => {
            const temp = document.createElement('div');
            temp.innerHTML = html;
            const detailContent = temp.querySelector('#admin-detail-content');
            const detailTitle = temp.querySelector('#admin-detail-title');

            if (detailContent) {
                modalBody.innerHTML = detailContent.innerHTML;
            } else {
                modalBody.innerHTML = html;
            }

            modalTitle.textContent = detailTitle ? detailTitle.textContent.trim() || 'Details' : 'Details';
        })
        .catch(() => {
            modalBody.innerHTML = `
                <div class="alert alert-danger mb-0">
                    Unable to load details. Please try again.
                </div>
            `;
            modalTitle.textContent = 'Error';
        });
    });
}

// Setup tooltips (if using Bootstrap tooltips)
function setupTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Export functions for use in views
window.adminUtils = {
    confirmAction: function(message, title = 'Please confirm', icon = 'warning', confirmButtonText = 'Yes, continue') {
        return Swal.fire({
            title: title,
            text: message,
            icon: icon,
            showCancelButton: true,
            confirmButtonText: confirmButtonText,
            cancelButtonText: 'Cancel',
            reverseButtons: true,
        }).then(result => result.isConfirmed);
    },
    
    showLoading: function() {
        document.body.style.opacity = '0.6';
        document.body.style.pointerEvents = 'none';
    },
    
    hideLoading: function() {
        document.body.style.opacity = '1';
        document.body.style.pointerEvents = 'auto';
    },
    
    showAlert: function(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        const pageContent = document.querySelector('.page-content');
        pageContent.insertAdjacentHTML('afterbegin', alertHtml);
        
        setTimeout(() => {
            const alert = pageContent.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
};
