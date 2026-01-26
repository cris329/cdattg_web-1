/**
 * Script específico para la página moderna de instructores
 * Basado en la arquitectura de los módulos modernos del sistema
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Instructores - Página moderna inicializada');

    // Sistema de notificaciones toast
    window.showToast = function(type, message) {
        // Buscar solo el primer toast dentro de vista-instructores para evitar duplicados
        const toast = document.querySelector('.vista-instructores .toast.toast-minimal');
        if (!toast) {
            console.warn('Toast element not found');
            return;
        }
        
        // Evitar mostrar el mismo mensaje múltiples veces
        const currentMessage = toast.querySelector('.toast-text').textContent;
        if (currentMessage === message) {
            console.log('Same message already showing, skipping');
            return;
        }
        
        const icon = toast.querySelector('.toast-icon');
        const text = toast.querySelector('.toast-text');
        
        // Set content
        text.textContent = message;
        
        // Set icon based on type
        icon.className = 'toast-icon fas';
        toast.className = 'vista-instructores toast toast-minimal';
        
        switch(type) {
            case 'success':
                icon.classList.add('fa-check-circle');
                toast.classList.add('success');
                break;
            case 'error':
                icon.classList.add('fa-exclamation-circle');
                toast.classList.add('error');
                break;
            case 'warning':
                icon.classList.add('fa-exclamation-triangle');
                toast.classList.add('warning');
                break;
            case 'info':
            default:
                icon.classList.add('fa-info-circle');
                toast.classList.add('info');
                break;
        }
        
        // Hide any existing toast first
        toast.classList.remove('show');
        
        // Small delay to ensure clean state
        setTimeout(() => {
            // Show toast
            toast.classList.add('show');
            
            // Hide after 4 seconds
            setTimeout(() => {
                toast.classList.remove('show');
            }, 4000);
        }, 50);
    };

    // Livewire event listeners
    // NOTA: El listener global de notify está en global-notifications.js
    // No necesitamos un listener duplicado aquí para evitar doble notificación

    // Initialize Select2 when Livewire updates
    function initializeSelect2() {
        // Destroy existing Select2 instances to avoid conflicts
        $('.vista-instructores .form-control-erp[multiple]').select2('destroy');
        $('.vista-instructores .form-control-erp:not([multiple])').select2('destroy');
        
        // Re-initialize Select2
        $('.vista-instructores .form-control-erp[multiple]').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: function() {
                return $(this).attr('data-placeholder') || '-- Selecciona opciones --';
            }
        });
        
        $('.vista-instructores .form-control-erp:not([multiple])').not('[wire\\:ignore]').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: function() {
                return $(this).attr('data-placeholder') || '-- Selecciona una opción --';
            }
        });
    }

    // Initialize on page load
    initializeSelect2();

    // Re-initialize when Livewire updates
    Livewire.hook('message.processed', (message, component) => {
        setTimeout(() => {
            initializeSelect2();
        }, 100);
    });

    // Handle modal close with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('.vista-instructores .modal-overlay');
            modals.forEach(modal => {
                if (modal.style.display !== 'none' && modal.style.display !== '') {
                    Livewire.dispatch('closeModal');
                }
            });
        }
    });

    // Auto-focus search input
    const searchInput = document.querySelector('.vista-instructores .search-input');
    if (searchInput) {
        searchInput.focus();
    }

    // Handle form submissions with loading states
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.classList.contains('instructor-form')) {
            // Add loading state to submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            }
        }
    });

    // Handle dynamic field additions
    window.addEventListener('click', function(e) {
        if (e.target.matches('[wire\\:click*="addCampo"]')) {
            setTimeout(() => {
                initializeSelect2();
            }, 100);
        }
    });

    // Handle dynamic field removals
    window.addEventListener('click', function(e) {
        if (e.target.matches('[wire\\:click*="removeCampo"]')) {
            setTimeout(() => {
                initializeSelect2();
            }, 100);
        }
    });

    // Handle modal backdrop clicks
    document.addEventListener('click', function(e) {
        if (e.target.matches('.vista-instructores .modal-overlay')) {
            Livewire.dispatch('closeModal');
        }
    });

    // Handle pagination links
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const url = e.target.closest('a').href;
            
            // Extract query parameters and update Livewire
            const urlParams = new URLSearchParams(url.split('?')[1] || '');
            
            // Update per_page if present
            if (urlParams.has('per_page')) {
                Livewire.dispatch('setPerPage', { perPage: urlParams.get('per_page') });
            }
            
            // Update page if present
            if (urlParams.has('page')) {
                Livewire.dispatch('setPage', { page: urlParams.get('page') });
            }
        }
    });

    // Handle filter changes with debouncing
    let filterTimeout;
    document.addEventListener('input', function(e) {
        if (e.target.matches('.vista-instructores .search-input')) {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                // Livewire will handle the update automatically through wire:model
            }, 300);
        }
    });

    // Handle filter select changes
    document.addEventListener('change', function(e) {
        if (e.target.matches('.vista-instructores select[wire\\:model]')) {
            // Livewire will handle the update automatically through wire:model
        }
    });

    // Handle action buttons with confirmation
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-action');
        if (btn && btn.classList.contains('btn-delete')) {
            if (!confirm('¿Está seguro de que desea eliminar este instructor? Esta acción no se puede deshacer.')) {
                e.preventDefault();
                e.stopPropagation();
            }
        }
    });

    // Handle table row hover effects
    const tableRows = document.querySelectorAll('.vista-instructores .modern-table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8fafc';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });

    // Handle responsive table actions
    function handleResponsiveActions() {
        const table = document.querySelector('.vista-instructores .modern-table');
        if (table) {
            const tableWidth = table.offsetWidth;
            const containerWidth = table.parentElement.offsetWidth;
            
            if (tableWidth > containerWidth) {
                table.style.fontSize = '12px';
            } else {
                table.style.fontSize = '';
            }
        }
    }

    // Check responsiveness on window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleResponsiveActions, 250);
    });

    // Initial responsiveness check
    handleResponsiveActions();

    // Handle keyboard navigation in tables
    document.addEventListener('keydown', function(e) {
        if (e.target.closest('.vista-instructores .modern-table')) {
            const currentRow = e.target.closest('tr');
            const table = e.target.closest('.modern-table');
            
            if (e.key === 'ArrowDown' && currentRow) {
                e.preventDefault();
                const nextRow = currentRow.nextElementSibling;
                if (nextRow && nextRow.tagName === 'TR') {
                    const firstAction = nextRow.querySelector('.btn-action');
                    if (firstAction) firstAction.focus();
                }
            } else if (e.key === 'ArrowUp' && currentRow) {
                e.preventDefault();
                const prevRow = currentRow.previousElementSibling;
                if (prevRow && prevRow.tagName === 'TR') {
                    const firstAction = prevRow.querySelector('.btn-action');
                    if (firstAction) firstAction.focus();
                }
            }
        }
    });

    // Handle print functionality
    window.addEventListener('beforeprint', function() {
        // Hide unnecessary elements for printing
        const elementsToHide = document.querySelectorAll('.vista-instructores .toolbar, .vista-instructores .btn-action');
        elementsToHide.forEach(el => el.style.display = 'none');
    });

    window.addEventListener('afterprint', function() {
        // Restore elements after printing
        const elementsToRestore = document.querySelectorAll('.vista-instructores .toolbar, .vista-instructores .btn-action');
        elementsToRestore.forEach(el => el.style.display = '');
    });

    // Handle accessibility
    function enhanceAccessibility() {
        // Add ARIA labels to dynamic elements
        const modals = document.querySelectorAll('.vista-instructores .modal-container');
        modals.forEach(modal => {
            if (!modal.hasAttribute('role')) {
                modal.setAttribute('role', 'dialog');
                modal.setAttribute('aria-modal', 'true');
            }
        });

        // Add keyboard navigation to action buttons
        const actionButtons = document.querySelectorAll('.vista-instructores .btn-action');
        actionButtons.forEach(btn => {
            if (!btn.hasAttribute('tabindex')) {
                btn.setAttribute('tabindex', '0');
            }
            if (!btn.hasAttribute('role')) {
                btn.setAttribute('role', 'button');
            }
        });
    }

    // Enhance accessibility on page load and after Livewire updates
    enhanceAccessibility();
    Livewire.hook('message.processed', (message, component) => {
        setTimeout(enhanceAccessibility, 100);
    });

    console.log('✅ Instructores - Todos los sistemas modernos listos');
});

// Global utility functions
window.utils = {
    // Format date for display
    formatDate: function(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-CO', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    },

    // Format number with commas
    formatNumber: function(number) {
        return new Intl.NumberFormat('es-CO').format(number);
    },

    // Truncate text with ellipsis
    truncateText: function(text, maxLength = 50) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },

    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};
