// FILE: /public/assets/js/app.js

/**
 * SplashLeadRouter - Main JavaScript
 */

(function() {
    'use strict';

    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }, 5000);
        });
    });

    // Confirm before delete
    document.addEventListener('click', function(e) {
        const deleteButton = e.target.closest('[data-confirm]');
        if (deleteButton) {
            const message = deleteButton.getAttribute('data-confirm') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        }
    });

    // Form validation helper
    window.validateForm = function(formId) {
        const form = document.getElementById(formId);
        if (!form) return true;

        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                field.style.borderColor = 'red';
                isValid = false;
            } else {
                field.style.borderColor = '';
            }
        });

        return isValid;
    };

    // Auto-update status via AJAX (for lead status updates)
    window.updateLeadStatus = function(leadId, status) {
        const csrfToken = document.querySelector('input[name="csrf_token"]').value;

        fetch('/leads/' + leadId + '/status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'csrf_token=' + encodeURIComponent(csrfToken) + '&status=' + encodeURIComponent(status)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Status updated successfully', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                showNotification('Failed to update status', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred', 'error');
        });
    };

    // Show notification
    window.showNotification = function(message, type) {
        const notification = document.createElement('div');
        notification.className = 'alert alert-' + (type === 'success' ? 'success' : 'error');
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.style.minWidth = '250px';

        document.body.appendChild(notification);

        setTimeout(function() {
            notification.style.transition = 'opacity 0.5s';
            notification.style.opacity = '0';
            setTimeout(function() {
                notification.remove();
            }, 500);
        }, 3000);
    };

    // Debounce function for search inputs
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = function() {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // Live search for tables
    const searchInputs = document.querySelectorAll('[data-search-table]');
    searchInputs.forEach(function(input) {
        const tableId = input.getAttribute('data-search-table');
        const table = document.getElementById(tableId);
        if (!table) return;

        input.addEventListener('keyup', debounce(function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }, 300));
    });

    // Table row click to navigate
    document.querySelectorAll('[data-href]').forEach(function(row) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            if (!e.target.closest('a, button')) {
                window.location.href = this.getAttribute('data-href');
            }
        });
    });

    console.log('SplashLeadRouter initialized');
})();
