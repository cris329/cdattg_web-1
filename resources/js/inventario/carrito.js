/**
 * carrito.js - Funcionalidad del carrito de compras
 * Maneja la gestión del carrito, actualización de cantidades y envío de órdenes
 */

// Configuración global
const API_BASE_URL = '/inventario';
const STORAGE_KEY = 'inventario_carrito';
const DRAFT_KEY = 'inventario_draft';

// Estado del carrito
let cart = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
let productsDetails = {}; // Cache de detalles de productos

/**
 * Helper para mostrar/ocultar modales 
 */
function showModal(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            // Bootstrap 5
            const modal = new bootstrap.Modal(element);
            modal.show();
        } else if (typeof jQuery !== 'undefined') {
            // Bootstrap 4 con jQuery
            jQuery(element).modal('show');
        }
    } catch (error) {
        console.error('Error mostrando modal:', error);
    }
}

/**
 * Helper para ocultar modales
 */
function hideModal(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    try {
        // Intentar primero con Bootstrap 5
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal && typeof bootstrap.Modal.getInstance === 'function') {
            const modal = bootstrap.Modal.getInstance(element);
            if (modal) {
                modal.hide();
                return;
            }
        }
        
        // Fallback para Bootstrap 4 con jQuery
        if (typeof jQuery !== 'undefined') {
            jQuery(element).modal('hide');
        }
    } catch (error) {
        // Log silencioso - esto es normal si el modal no estaba abierto
        // Solo registrar si es un error inesperado (no relacionado con modal no inicializado)
        const isExpectedError = error instanceof TypeError && 
                                error.message && 
                                (error.message.includes('Cannot read') || 
                                 error.message.includes('null') ||
                                 error.message.includes('undefined'));
        
        if (!isExpectedError) {
            console.debug('Modal no activo o error al cerrar:', elementId, error);
        }
        // Error esperado: modal no inicializado o elemento no encontrado - no hacer nada
    }
}

/**
 * Inicialización cuando el DOM está listo
 */
function setupCartInitialization() {
    // Verificar que estamos en la página del carrito
    if (!isCartPage()) {
        return;
    }

    // Verificar que los elementos del DOM estén presentes
    const cartContainer = document.getElementById('cart-items-container');
    if (!cartContainer) {
        // Esperar un poco más si los elementos no están listos
        setTimeout(setupCartInitialization, 100);
        return;
    }

    // Recargar el carrito desde localStorage antes de inicializar
    cart = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    
    initializeCart();
}

/**
 * Inicializa el carrito
 */
async function initializeCart() {
    // Recargar el carrito desde localStorage para asegurar datos actualizados
    cart = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    
    await loadCartItems();
    setupCartActions();
    updateCartSummary();
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupCartInitialization);
} else {
    // DOM ya está listo
    setupCartInitialization();
}

// Función para verificar si estamos en la página del carrito
function isCartPage() {
    const pathname = globalThis.location.pathname;
    const hasCartContainer = document.getElementById('cart-items-container') !== null;
    return pathname.includes('carrito-sena') || 
           pathname.includes('carrito') ||
           hasCartContainer;
}

// Exportar función para uso externo (desde script global)
globalThis.initializeCartPage = function() {
    if (isCartPage()) {
        setupCartInitialization();
    }
};

// Escuchar eventos de navegación de Livewire
function setupNavigationListener() {
    let navigationTimeout = null;
    
    const handleNavigation = () => {
        if (navigationTimeout) {
            clearTimeout(navigationTimeout);
        }
        
        navigationTimeout = setTimeout(() => {
            if (isCartPage()) {
                setupCartInitialization();
            }
        }, 300);
    };

    // Escuchar cuando Livewire navega (wire:navigate)
    if (typeof Livewire !== 'undefined') {
        // Livewire 3
        if (typeof Livewire.on === 'function') {
            Livewire.on('navigate', handleNavigation);
        }

        // También escuchar eventos de hook
        if (typeof Livewire.hook === 'function') {
            Livewire.hook('morph.updated', handleNavigation);
        }
    }

    // Escuchar clics en enlaces con wire:navigate
    document.addEventListener('click', function(event) {
        const link = event.target.closest(String.raw`a[wire\:navigate], a[data-wire-navigate]`);
        if (link?.href) {
            const href = link.href.toLowerCase();
            if (href.includes('carrito-sena') || href.includes('carrito')) {
                handleNavigation();
            }
        }
    }, true);

    // Escuchar cambios en la URL usando popstate
    globalThis.addEventListener('popstate', handleNavigation);

    // Observar cambios en la URL directamente
    let lastUrl = location.href;
    const urlCheckInterval = setInterval(() => {
        const currentUrl = location.href;
        if (currentUrl !== lastUrl) {
            lastUrl = currentUrl;
            if (isCartPage()) {
                handleNavigation();
            }
        }
    }, 500);

    // Observar cambios en el DOM que puedan indicar navegación
    // Deshabilitado en carrito para evitar re-renderizados constantes y parpadeos en los botones
    const domObserver = new MutationObserver(() => {
        // Intencionalmente vacío
    });

    // Limpiar intervalo cuando se descargue la página
    window.addEventListener('beforeunload', () => {
        clearInterval(urlCheckInterval);
        domObserver.disconnect();
    });
}

// Configurar listener de navegación
setupNavigationListener();

/**
 * Cargar items del carrito y sus detalles
 */
async function loadCartItems() {
    // Recargar el carrito desde localStorage para asegurar datos actualizados
    cart = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    
    if (cart.length === 0) {
        showEmptyCart();
        return;
    }

    // Mostrar la tabla del carrito
    document.getElementById('empty-cart-message')?.classList.add('d-none');
    document.getElementById('cart-items-table')?.classList.remove('d-none');

    const tbody = document.getElementById('cart-items-body');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando productos...</td></tr>';

    try {
        // Cargar detalles de todos los productos
        const productPromises = [];
        for (const item of cart) {
            productPromises.push(loadProductDetails(item.id));
        }
        await Promise.all(productPromises);

        renderCartItems();
    } catch (error) {
        console.error('Error al cargar productos:', error);
        showError('Error al cargar los productos del carrito');
    }
}

/**
 * Cargar detalles de un producto
 */
async function loadProductDetails(productId) {
    // Si ya tenemos los detalles, no volver a cargar
    if (productsDetails[productId]) {
        return productsDetails[productId];
    }

    try {
        const response = await fetch(`${API_BASE_URL}/productos/detalles/${productId}`);
        if (!response.ok) {
            throw new Error('Producto no encontrado');
        }
        
        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Extraer información del producto
        const productData = {
            id: productId,
            name: doc.querySelector('h3')?.textContent?.trim() ?? 'Producto',
            image: doc.querySelector('.product-image-wrapper img')?.src ?? 
                   doc.querySelector('img[alt]')?.src ?? 
                   '/img/inventario/producto-default.png',
            stock: Number.parseInt(doc.querySelector('.stat-card-value')?.textContent, 10) || 
                   Number.parseInt(Array.from(doc.querySelectorAll('.badge')).find(el => el.textContent.includes('unidades'))?.textContent, 10) || 0,
            code: doc.querySelector('.badge-secondary')?.textContent?.trim() ?? '',
            description: doc.querySelector('.card-text')?.textContent?.trim() ?? ''
        };

        productsDetails[productId] = productData;
        return productData;
    } catch (error) {
        console.error(`Error al cargar producto ${productId}:`, error);
        throw error;
    }
}

/**
 * Renderizar items del carrito
 */
function renderCartItems() {
    const tbody = document.getElementById('cart-items-body');
    if (!tbody) return;

    tbody.innerHTML = '';

    for (const [index, item] of cart.entries()) {
        // Usar el nombre del item directamente, o cargar detalles si no existe
        const productName = item.name || (productsDetails[item.id]?.name || 'Producto desconocido');
        const product = productsDetails[item.id] || {};
        const displayName = productName;
        const displayImage = product.image || '/img/inventario/producto-default.png';
        const displayCode = product.code || '';
        const displayStock = product.stock || item.maxStock || 0;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <img src="${displayImage}" 
                     alt="${displayName}" 
                     class="img-thumbnail" 
                     style="max-width: 60px; max-height: 60px; object-fit: cover;"
                     onerror="this.src='/img/inventario/producto-default.png'">
            </td>
            <td>
                <strong>${displayName}</strong>
                <br>
                <small class="text-muted">
                    <i class="fas fa-barcode"></i> ${displayCode}
                </small>
            </td>
            <td class="text-center">
                <span class="badge badge-info">${displayStock} unidades</span>
            </td>
            <td class="text-center">
                <div class="input-group input-group-sm" style="max-width: 150px; margin: 0 auto;">
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-secondary btn-decrease" 
                               data-index="${index}" 
                                type="button"
                                ${item.quantity <= 1 ? 'disabled' : ''}>
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                    <input type="number" 
                           class="form-control text-center quantity-input" 
                           data-index="${index}"
                           value="${item.quantity}" 
                           min="1" 
                           max="${displayStock}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary btn-increase" 
                                data-index="${index}" 
                                type="button"
                                ${item.quantity >= displayStock ? 'disabled' : ''}>
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                ${item.quantity >= displayStock ? '<small class="text-warning d-block mt-1">Máximo alcanzado</small>' : ''}
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-danger btn-remove" 
                        data-index="${index}"
                        title="Eliminar del carrito">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(row);
    }

    // Reconfigurar event listeners
    setupQuantityControls();
}

/**
 * Configurar controles de cantidad
 */
function setupQuantityControls() {
    // Botones de disminuir
    for (const btn of document.querySelectorAll('.btn-decrease')) {
        btn.addEventListener('click', function() {
            const index = Number.parseInt(this.dataset.index, 10);
            decreaseQuantity(index);
        });
    }

    // Botones de aumentar
    for (const btn of document.querySelectorAll('.btn-increase')) {
        btn.addEventListener('click', function() {
            const index = Number.parseInt(this.dataset.index, 10);
            increaseQuantity(index);
        });
    }

    // Inputs de cantidad
    for (const input of document.querySelectorAll('.quantity-input')) {
        input.addEventListener('change', function() {
            const index = Number.parseInt(this.dataset.index, 10);
            const newQuantity = Number.parseInt(this.value, 10);
            updateQuantity(index, newQuantity);
        });
    }

    // Botones de eliminar
    for (const btn of document.querySelectorAll('.btn-remove')) {
        btn.addEventListener('click', function() {
            const index = Number.parseInt(this.dataset.index, 10);
            removeItem(index);
        });
    }
}

/**
 * Configurar acciones del carrito
 */
function setupCartActions() {
    // Botón de vaciar carrito
    const emptyCartBtn = document.getElementById('btn-empty-cart');
    if (emptyCartBtn) {
        emptyCartBtn.addEventListener('click', confirmEmptyCart);
    }

    // Botón de confirmar orden
    const confirmBtn = document.getElementById('btn-confirm-order');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', confirmOrder);
    }

    // Botón de guardar borrador
    const saveDraftBtn = document.getElementById('btn-save-draft');
    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', saveDraft);
    }

    // Botón de confirmación final
    const finalConfirmBtn = document.getElementById('btn-final-confirm');
    if (finalConfirmBtn) {
        finalConfirmBtn.addEventListener('click', submitOrder);
    }
}

/**
 * Disminuir cantidad de un item
 */
function decreaseQuantity(index) {
    if (cart[index].quantity > 1) {
        cart[index].quantity--;
        saveCart();
        renderCartItems();
        updateCartSummary();
    }
}

/**
 * Aumentar cantidad de un item
 */
function increaseQuantity(index) {
    const item = cart[index];
    const product = productsDetails[item.id];
    const maxStock = product?.stock || item.maxStock || 0;
    const productName = item.name || product?.name || 'Producto';
    
    if (item.quantity < maxStock) {
        item.quantity++;
        saveCart();
        renderCartItems();
        updateCartSummary();
    } else {
        showStockWarning(productName, maxStock);
    }
}

/**
 * Actualizar cantidad de un item
 */
function updateQuantity(index, newQuantity) {
    const item = cart[index];
    const product = productsDetails[item.id];
    const maxStock = product?.stock || item.maxStock || 0;
    const productName = item.name || product?.name || 'Producto';
    
    if (newQuantity < 1) {
        newQuantity = 1;
    } else if (newQuantity > maxStock) {
        newQuantity = maxStock;
        showStockWarning(productName, maxStock);
    }

    item.quantity = newQuantity;
    saveCart();
    renderCartItems();
    updateCartSummary();
}

/**
 * Eliminar item del carrito
 */
function removeItem(index) {
    const item = cart[index];
    const product = productsDetails[item.id];
    const productName = item.name || product?.name || 'Producto';
    
    Swal.fire({
        title: '¿Eliminar producto?',
        html: `¿Estás seguro de eliminar <strong>"${productName}"</strong> del carrito?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            cart.splice(index, 1);
            saveCart();
            
            if (cart.length === 0) {
                showEmptyCart();
            } else {
                renderCartItems();
            }
            
            updateCartSummary();
            
            Swal.fire({
                icon: 'success',
                title: 'Producto eliminado',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}
/**
 * Confirmar vaciar el carrito
 * 
 */
function confirmEmptyCart() {
    if (cart.length === 0) return;

    Swal.fire({
        title: '¿Vaciar carrito?',
        text: 'Se eliminarán todos los productos del carrito',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, vaciar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            cart = [];
            saveCart();
            localStorage.removeItem(DRAFT_KEY);
            sessionStorage.removeItem('carrito_data');
            showEmptyCart();
            updateCartSummary();
            
            Swal.fire({
                icon: 'success',
                title: 'Carrito vaciado',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

/**
 * Guardar carrito en localStorage
 */
function saveCart() {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
}

/**
 * Actualizar resumen del carrito
 */
function updateCartSummary() {
    const totalProducts = cart.length;
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

    document.getElementById('total-products').textContent = totalProducts;
    document.getElementById('total-items').textContent = totalItems;

    // Habilitar/deshabilitar botón de confirmar
    const confirmBtn = document.getElementById('btn-confirm-order');
    if (confirmBtn) {
        confirmBtn.disabled = totalItems === 0;
    }
}

/**
 * Mostrar carrito vacío
 */
function showEmptyCart() {
    document.getElementById('empty-cart-message')?.classList.remove('d-none');
    document.getElementById('cart-items-table')?.classList.add('d-none');
}

/**
 * Confirmar orden (mostrar resumen)
 */
function confirmOrder() {
    if (cart.length === 0) return;

    // Generar resumen
    let summaryHTML = '<table class="table table-sm"><tbody>';
    
    for (const item of cart) {
        const product = productsDetails[item.id];
        const productName = item.name || product?.name || 'Producto';
        summaryHTML += `
            <tr>
                <td><strong>${productName}</strong></td>
                <td class="text-right">${item.quantity} unidades</td>
            </tr>
        `;
    }
    
    summaryHTML += '</tbody></table>';
    
    const notes = document.getElementById('order-notes')?.value || '';
    if (notes) {
        summaryHTML += `
            <div class="mt-3">
                <strong>Notas:</strong>
                <p class="text-muted">${notes}</p>
            </div>
        `;
    }

    document.getElementById('order-summary-content').innerHTML = summaryHTML;
    
    // Mostrar modal
    showModal('confirmOrderModal');
}

/**
 * Enviar orden al servidor - Redirigir a préstamo/salida
 */
async function submitOrder() {
    const notes = document.getElementById('order-notes')?.value || '';
    
    // Calcular totales
    const totalProductos = cart.length;
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    // Preparar datos para enviar a la vista de préstamo/salida
    const orderData = {
        items: cart,
        notas: notes,
        totalProductos: totalProductos,
        totalItems: totalItems
    };

    try {
        // Guardar datos en sessionStorage para pasar a la siguiente vista
        sessionStorage.setItem('carrito_data', JSON.stringify(orderData));

        // No vaciar el carrito aquí: debe mantenerse hasta que se cree la solicitud
        // Redirigir a la vista de préstamo/salida
        globalThis.location.href = '/inventario/ordenes/prestamos-salidas?desde_carrito=true';

    } catch (error) {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Ocurrió un error al procesar tu solicitud'
        });
    }
}

/**
 * Guardar borrador
 */
function saveDraft() {
    const notes = document.getElementById('order-notes')?.value || '';
    
    const draft = {
        cart: cart,
        notes: notes,
        timestamp: new Date().toISOString()
    };

    localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));

    Swal.fire({
        icon: 'success',
        title: 'Borrador guardado',
        text: 'Puedes continuar más tarde',
        timer: 2000,
        showConfirmButton: false
    });
}

/**
 * Mostrar advertencia de stock
 */
function showStockWarning(productName, maxStock) {
    const content = document.getElementById('stock-warning-content');
    
    content.innerHTML = `
        <p>Has alcanzado la cantidad máxima disponible de:</p>
        <p class="text-center"><strong>${productName}</strong></p>
        <p class="text-muted text-center">Stock disponible: ${maxStock} unidades</p>
    `;
    
    showModal('stockWarningModal');
}

/**
 * Mostrar error
 */
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message
    });
}

// Exportar funciones para uso externo
globalThis.inventarioCarrito = {
    loadCartItems,
    updateCartSummary,
    confirmOrder,
    saveDraft
};