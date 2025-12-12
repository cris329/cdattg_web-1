/**
 * card.js - Funcionalidad para la vista de catálogo de productos (ecommerce)
 * Maneja búsqueda, filtrado, ordenamiento y acciones de productos
 */

// Configuración global
const API_BASE_URL = '/inventario';
const STORAGE_KEY = 'inventario_carrito';

// Estado del carrito
let cart = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];

// Estado de búsqueda y filtrado
let showingSearchResults = false;
let currentFetchedProducts = [];
let currentFetchController = null;
let originalGridHTML = null;
let originalPaginationHTML = '';
let productModalOpen = false;

/**
 * Helper para mostrar/ocultar modales compatible con Bootstrap 4 y 5
 */
function showModal(elementId) {
    const element = document.getElementById(elementId);
    if (!element) {
        console.error('Modal no encontrado:', elementId);
        return;
    }
    
    if (element instanceof HTMLDialogElement) {
        // Para dialog elements, usar showModal() que maneja el display automáticamente
        element.showModal();
        // Asegurar que se muestre
        element.style.display = 'flex';
        element.style.setProperty('display', 'flex', 'important');
    } else {
        element.style.display = 'flex';
        element.style.setProperty('display', 'flex', 'important');
    }
}

function closeProductModal() {
    const modal = document.getElementById('productDetailModal');
    if (!modal) return;

    if (modal instanceof HTMLDialogElement) {
        modal.close();
        modal.removeAttribute('open');
    }

    modal.style.setProperty('display', 'none', 'important');
    productModalOpen = false;
}

/**
 * Expandir imagen en modal
 */
function expandirImagen(imageSrc) {
    const expandedImage = document.getElementById('expandedImage');
    const imageModal = document.getElementById('imageModal');
    
    if (!expandedImage || !imageModal) {
        return;
    }

    expandedImage.src = imageSrc;
    if (typeof $ !== 'undefined' && $?.fn?.modal) {
        $(imageModal).modal('show');
    }
}

/**
 * Agregar producto al carrito desde el modal
 */
function agregarAlCarritoDesdeModal(productId, productName, productStock) {
    try {
        addToCart(productId, productName, productStock);
        // Mantener el modal abierto para seguir revisando detalles o agregar más productos
        if (productModalOpen) {
            const modal = document.getElementById('productDetailModal');
            if (modal instanceof HTMLDialogElement) {
                modal.focus();
            }
        }
    } catch (error) {
        console.error('agregarAlCarritoDesdeModal: no se agregó el producto', error);
        alert('Error al agregar al carrito. Por favor intente de nuevo.');
    }
}

// Función para verificar si estamos en la página del catálogo
function isCatalogPage() {
    const pathname = globalThis.location.pathname;
    return pathname.includes('productos') && 
           (pathname.includes('catalogo') || 
            document.getElementById('products-grid') !== null);
}

/**
 * Inicializa la vista de catálogo
 */
function initializeCardView() {
    resetProductActions();
    
    setupSearchFilter();
    setupTypeFilter();
    setupSortFilter();
    setupProductActions();
    updateCartCount();
}

/**
 * Inicialización cuando el DOM está listo
 */
function setupCardInitialization() {
    if (productModalOpen) {
        return;
    }
    const productsGrid = document.getElementById('products-grid');
    if (!productsGrid) {
        return;
    }

    // Asegurar que el modal esté cerrado al inicializar
    closeProductModal();

    // Pequeño delay para asegurar que el DOM esté completamente renderizado
    setTimeout(() => {
        initializeCardView();
        updateCartCount();
        setupModalDismissHandlers();
        initializeSelect2();
    }, 50);
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupCardInitialization);
} else {
    setupCardInitialization();
}

if (isCatalogPage()) {
    setTimeout(setupCardInitialization, 100);
}

// Escuchar eventos de navegación de Livewire
function setupNavigationListener() {
    let navigationTimeout = null;
    
    const handleNavigation = () => {
        if (productModalOpen) {
            return;
        }

        if (navigationTimeout) {
            clearTimeout(navigationTimeout);
        }
        
        navigationTimeout = setTimeout(() => {
            if (isCatalogPage()) {
                setupCardInitialization();
            }
        }, 200);
    };

    if (typeof Livewire !== 'undefined') {
        if (typeof Livewire.on === 'function') {
            Livewire.on('navigate', handleNavigation);
        }

        if (typeof Livewire.hook === 'function') {
            Livewire.hook('morph.updated', handleNavigation);
        }
    }

    document.addEventListener('click', function(event) {
        const link = event.target.closest(String.raw`a[wire\:navigate], a[data-wire-navigate]`);
        const href = link?.href?.toLowerCase();
        if (href?.includes('productos') && href?.includes('catalogo')) {
            handleNavigation();
        }
    }, true);

    globalThis.addEventListener('popstate', handleNavigation);

    let lastUrl = location.href;
    const urlCheckInterval = setInterval(() => {
        const currentUrl = location.href;
        if (currentUrl !== lastUrl) {
            lastUrl = currentUrl;
            if (isCatalogPage()) {
                handleNavigation();
            }
        }
    }, 500);

        const domObserver = new MutationObserver((mutations) => {
        const hasSignificantChanges = mutations.some(mutation => {
                if (productModalOpen) {
                    return false;
                }

            const modalElement = document.getElementById('productDetailModal');
            const mutationTargetsModal = modalElement && (
                modalElement.contains(mutation.target) ||
                Array.from(mutation.addedNodes).some(node => modalElement.contains(node)) ||
                Array.from(mutation.removedNodes).some(node => modalElement.contains(node))
            );

            if (mutationTargetsModal) {
                return false;
            }

            return mutation.addedNodes.length > 0 || 
                   mutation.removedNodes.length > 0;
        });

        if (hasSignificantChanges && isCatalogPage()) {
            const productsGrid = document.getElementById('products-grid');
            if (productsGrid) {
                handleNavigation();
            }
        }
    });

    domObserver.observe(document.body, {
        childList: true,
        subtree: true
    });

    globalThis.addEventListener('beforeunload', () => {
        clearInterval(urlCheckInterval);
        domObserver.disconnect();
    });
}

setupNavigationListener();

globalThis.initializeCatalogPage = function() {
    if (isCatalogPage()) {
        setupCardInitialization();
    }
};

/**
 * Configura los métodos para cerrar el modal de detalles
 */
function setupModalDismissHandlers() {
    const modal = document.getElementById('productDetailModal');
    const modalContent = document.getElementById('product-detail-modal-content');

    if (modal && !modal.dataset.dismissInitialized) {
        if (modal instanceof HTMLDialogElement) {
            modal.addEventListener('cancel', function(event) {
                event.preventDefault();
                closeProductModal();
            });
        } else {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeProductModal();
                }
            });
        }
        modal.dataset.dismissInitialized = 'true';
    }

    if (modalContent && !modalContent.dataset.stopPropagationInitialized) {
        modalContent.addEventListener('click', function(event) {
            event.stopPropagation();
        });
        modalContent.addEventListener('keydown', function(event) {
            event.stopPropagation();
        });
        modalContent.dataset.stopPropagationInitialized = 'true';
    }

    if (!document.body.dataset.productModalEscapeListener) {
        document.addEventListener('keydown', handleProductModalEscape);
        document.body.dataset.productModalEscapeListener = 'true';
    }
}

function handleProductModalEscape(event) {
    if (event.key === 'Escape') {
        closeProductModal();
    }
}

// Variables para evitar listeners duplicados en filtros
let searchFilterInitialized = false;
let searchTimeout = null;
let searchInputHandler = null;
let searchKeypressHandler = null;

/**
 * Configurar búsqueda de productos
 */
function setupSearchFilter() {
    // Filters are applied manually using the "Aplicar" button.
}

/**
 * Configurar filtro por tipo de producto
 */
function setupTypeFilter() {
    // Filters are applied manually using the "Aplicar" button.
}

/**
 * Configurar ordenamiento de productos
 */
function setupSortFilter() {
    // Filters are applied manually using the "Aplicar" button.
}

function applyFilters() {
    const form = document.getElementById('catalog-filters-form');
    if (!form) {
        return;
    }

    form.submit();
}

/**
 * Restaurar la grilla original renderizada por Blade
 */
function restoreInitialState() {
    const grid = document.getElementById('products-grid');
    const pagination = document.getElementById('catalog-pagination');

    if (grid && originalGridHTML) {
        grid.innerHTML = originalGridHTML;
    }

    if (pagination && originalPaginationHTML !== '') {
        pagination.innerHTML = originalPaginationHTML;
        pagination.style.display = '';
    }

    showingSearchResults = false;
    currentFetchedProducts = [];
    setupProductActions();
    toggleNoResults(false);
}

/**
 * Consultar al backend para traer los productos filtrados
 */
async function fetchAndRenderProducts({ searchTerm, typeId, sortBy }) {
    const grid = document.getElementById('products-grid');
    if (!grid) return;

    toggleNoResults(false);
    setPaginationVisibility(false);

    if (currentFetchController) {
        currentFetchController.abort();
    }

    currentFetchController = new AbortController();
    const params = new URLSearchParams();

    if (searchTerm) params.append('search', searchTerm);
    if (typeId) params.append('tipo_producto_id', typeId);

    grid.innerHTML = `
        <div class="col-12 text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
            <p>Buscando productos...</p>
        </div>
    `;

    try {
        const response = await fetch(`${API_BASE_URL}/productos/buscar?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: currentFetchController.signal
        });

        if (!response.ok) {
            throw new Error('No se pudo obtener la información de productos');
        }

        const data = await response.json();
        const productos = Array.isArray(data.productos) ? data.productos : [];

        currentFetchedProducts = sortProductData(productos, sortBy);
        renderProducts(currentFetchedProducts, true);
        showingSearchResults = true;
    } catch (error) {
        if (error.name === 'AbortError') {
            return;
        }

        grid.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                    <p>Error al buscar productos.</p>
                    <small>${error.message}</small>
                </div>
            </div>
        `;
    }
}

/**
 * Ordenar productos
 */
function sortProducts(sortBy) {
    if (showingSearchResults) {
        currentFetchedProducts = sortProductData(currentFetchedProducts, sortBy);
        renderProducts(currentFetchedProducts, true);
        return;
    }

    const grid = document.getElementById('products-grid');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('.product-card'));
    
    cards.sort((a, b) => compareCards(a, b, sortBy));

    for (const card of cards) {
        grid.appendChild(card);
    }
}

function sortProductData(products, sortBy) {
    const sorted = [...products];

    sorted.sort((a, b) => {
        switch (sortBy) {
            case 'stock-asc':
                return (a.cantidad ?? 0) - (b.cantidad ?? 0);
            case 'stock-desc':
                return (b.cantidad ?? 0) - (a.cantidad ?? 0);
            case 'newest':
                return (b.id ?? 0) - (a.id ?? 0);
            case 'name':
            default:
                return (a.producto ?? '').toLowerCase().localeCompare((b.producto ?? '').toLowerCase());
        }
    });

    return sorted;
}

function compareCards(a, b, sortBy) {
    switch (sortBy) {
        case 'stock-asc':
            return extractCardStock(a) - extractCardStock(b);
        case 'stock-desc':
            return extractCardStock(b) - extractCardStock(a);
        case 'newest':
            return Number.parseInt(b.dataset?.id ?? '0', 10) - Number.parseInt(a.dataset?.id ?? '0', 10);
        case 'name':
        default:
            return (a.dataset?.name ?? '').localeCompare(b.dataset?.name ?? '');
    }
}

function extractCardStock(card) {
    const stockBadge = card.querySelector('.badge-success, .badge-warning, .badge-danger');
    if (!stockBadge) return 0;
    const matches = stockBadge.textContent.match(/\d+/);
    return matches ? Number.parseInt(matches[0], 10) : 0;
}

function renderProducts(products, skipSort = false) {
    const grid = document.getElementById('products-grid');
    if (!grid) return;

    if (!Array.isArray(products) || products.length === 0) {
        grid.innerHTML = '';
        toggleNoResults(true);
        return;
    }

    toggleNoResults(false);

    if (!skipSort) {
        const sortBy = document.getElementById('sort-by')?.value || 'name';
        products = sortProductData(products, sortBy);
    }

    const cardsHTML = products.map(product => createProductCardHTML(product)).join('');
    grid.innerHTML = cardsHTML;

    setupProductActions();
}

function createProductCardHTML(product) {
    const stockClass = getStockClass(product.cantidad);
    const categoriaNombre = product?.categoria?.name || 'Sin categoría';
    const tipoNombre = product?.tipo_producto?.parametro?.name || '';
    const marcaNombre = product?.marca?.name || '';
    const descripcion = product.descripcion || 'Sin descripción disponible';
    const codigoBarras = product.codigo_barras || 'S/N';
    const imagenSrc = product.imagen_url || product.imagen || null;
    const productoNombre = product.producto || '';

    return `
        <div class="col-lg-3 col-md-4 col-sm-6 mb-4 product-card"
             data-id="${product.id}"
             data-type="${product.tipo_producto_id || ''}"
             data-name="${productoNombre.toLowerCase()}"
             data-code="${(product.codigo_barras || '').toLowerCase()}">
            <div class="card h-100 shadow-sm hover-shadow">
                <div class="product-image-container">
                    ${imagenSrc ? `
                        <img src="${imagenSrc}" class="card-img-top product-image" alt="${productoNombre}">
                    ` : `
                        <div class="no-image-placeholder">
                            <i class="fas fa-box fa-4x text-muted"></i>
                            <p class="text-muted mt-2">Sin imagen</p>
                        </div>
                    `}
                    <span class="badge stock-badge stock-badge-${stockClass}"></span>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="mb-2">
                        ${tipoNombre ? `
                            <small class="text-muted d-block">
                                <i class="fas fa-box-open"></i> ${tipoNombre}
                            </small>
                        ` : ''}
                        <small class="text-muted">
                            <i class="fas fa-tag"></i> ${categoriaNombre}
                        </small>
                        ${marcaNombre ? `
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-copyright"></i> ${marcaNombre}
                            </small>
                        ` : ''}
                    </div>
                    <h5 class="card-title font-weight-bold mb-2">
                        ${truncateText(productoNombre, 50)}
                    </h5>
                    <p class="card-text text-muted small flex-grow-1">
                        ${truncateText(descripcion, 80)}
                    </p>
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="fas fa-barcode"></i>
                            <span class="badge badge-secondary">${codigoBarras}</span>
                        </small>
                    </div>
                    <div class="mb-3">
                        <strong>Stock: </strong>
                        <span class="badge badge-${stockClass}">
                            ${product.cantidad || 0} unidades
                        </span>
                    </div>
                    <div class="btn-group d-flex" role="group">
                        <button type="button"
                                class="btn btn-sm btn-info btn-view-details w-50"
                                data-id="${product.id}"
                                title="Ver detalles">
                            <i class="fas fa-eye"></i> Detalles
                        </button>
                        ${(product.cantidad || 0) > 0 ? `
                            <button type="button"
                                    class="btn btn-sm btn-success btn-add-to-cart w-50"
                                    data-id="${product.id}"
                                    data-name="${productoNombre}"
                                    data-stock="${product.cantidad}"
                                    title="Agregar al carrito">
                                <i class="fas fa-cart-plus"></i> Agregar
                            </button>
                        ` : `
                            <button type="button" class="btn btn-sm btn-secondary w-50" disabled>
                                <i class="fas fa-ban"></i> Agotado
                            </button>
                        `}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function initializeSelect2() {
    if (typeof $ === 'undefined' || !$?.fn?.select2) {
        return;
    }

    const elements = document.querySelectorAll('.select2');

    for (const element of elements) {
        const $element = $(element);
        
        if ($element.hasClass('select2-hidden-accessible')) {
            $element.select2('destroy');
        }

        if (element.options?.length > 0) {
            $element.select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: element.dataset.placeholder || 'Seleccione una opción',
                allowClear: true,
                minimumResultsForSearch: -1
            });
        }
    }
}

function truncateText(text, maxLength) {
    const value = text || '';
    if (value.length <= maxLength) {
        return value;
    }
    return `${value.slice(0, maxLength)}...`;
}

function getStockClass(quantity = 0) {
    if (quantity <= 0) {
        return 'danger';
    }

    if (quantity <= 5) {
        return 'warning';
    }

    return 'success';
}

function setPaginationVisibility(show) {
    const pagination = document.getElementById('catalog-pagination');
    if (!pagination) return;
    pagination.style.display = show ? '' : 'none';
}

let productActionsHandler = null;
let productActionsInitialized = false;

/**
 * Configurar acciones de productos usando event delegation
 */
function setupProductActions() {
    const productsGrid = document.getElementById('products-grid');
    if (!productsGrid) {
        return;
    }

    if (productActionsInitialized && productActionsHandler) {
        return;
    }

    if (productActionsHandler) {
        productsGrid.removeEventListener('click', productActionsHandler, true);
        productActionsHandler = null;
    }

    // Helper: Encontrar el botón objetivo en el árbol DOM
    const findTargetButton = (element) => {
        let target = element;
        while (target && target !== productsGrid) {
            if (target.classList?.contains('btn-view-details') || 
                target.classList?.contains('btn-add-to-cart')) {
                return target;
            }
            target = target.parentElement;
        }
        return null;
    };

    // Helper: Manejar vista de detalles
    const handleViewDetails = (button) => {
        const productId = button.dataset?.id ?? button.getAttribute?.('data-id');
        if (productId) {
            console.log('Abriendo detalles del producto:', productId);
            showProductDetails(productId);
        }
    };

    // Helper: Manejar agregar al carrito
    const handleAddToCart = (button) => {
        const productId = button.dataset?.id ?? button.getAttribute?.('data-id');
        const productName = button.dataset?.name ?? button.getAttribute?.('data-name');
        const productStockStr = button.dataset?.stock ?? button.getAttribute?.('data-stock');
        const productStock = Number.parseInt(productStockStr, 10);
        
        if (productId && productName && !Number.isNaN(productStock)) {
            addToCart(productId, productName, productStock);
        }
    };

    productActionsHandler = function(event) {
        if (event.defaultPrevented) return;

        const target = findTargetButton(event.target);
        if (!target) return;

        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();

        if (target.classList.contains('btn-view-details')) {
            handleViewDetails(target);
        } else if (target.classList.contains('btn-add-to-cart')) {
            handleAddToCart(target);
        }
    };

    productsGrid.addEventListener('click', productActionsHandler, true);
    productActionsInitialized = true;
}

/**
 * Resetear la inicialización de acciones
 */
function resetProductActions() {
    const productsGrid = document.getElementById('products-grid');
    if (productsGrid && productActionsHandler) {
        productsGrid.removeEventListener('click', productActionsHandler, true);
    }
    productActionsHandler = null;
    productActionsInitialized = false;
}

/**
 * Mostrar detalles del producto en modal
 */
async function showProductDetails(productId) {
    const contentDiv = document.getElementById('product-detail-content');
    if (!contentDiv) return;

    productModalOpen = true;
    contentDiv.innerHTML = `
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-3x"></i>
            <p class="mt-3">Cargando detalles...</p>
        </div>
    `;
    showModal('productDetailModal');

    try {
        const response = await fetch(`${API_BASE_URL}/productos/detalles/${productId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        
        if (!response.ok) {
            throw new Error('Error al cargar los detalles del producto');
        }

        const html = await response.text();
        if (!productModalOpen) {
            return;
        }
        contentDiv.innerHTML = html;
        
        // Mostrar el modal DESPUÉS de cargar el contenido
        console.log('Contenido cargado, abriendo modal...');
        showModal('productDetailModal');
        
        // Verificar que se abrió
        setTimeout(() => {
            const modal = document.getElementById('productDetailModal');
            if (!productModalOpen) {
                return;
            }
            if (modal && modal.style.display !== 'flex' && !modal.hasAttribute('open')) {
                console.warn('Modal no se abrió correctamente, forzando apertura...');
                modal.showModal();
                modal.style.display = 'flex';
                modal.setAttribute('open', '');
            }
        }, 100);
        
    } catch (error) {
        if (!productModalOpen) {
            return;
        }
        contentDiv.innerHTML = `
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <p>Error al cargar los detalles del producto</p>
                <small>${error.message}</small>
            </div>
        `;
        showModal('productDetailModal');
    }
}

/**
 * Agregar producto al carrito
 */
function addToCart(productId, productName, productStock) {
    const normalizedId = String(productId);
    const existingItem = cart.find(item => String(item.id) === normalizedId);

    if (existingItem) {
        if (existingItem.quantity >= productStock) {
            showStockAlert(productName, productStock);
            return;
        }
        existingItem.quantity++;
    } else {
        cart.push({
            id: normalizedId,
            name: productName,
            quantity: 1,
            maxStock: productStock
        });
    }

    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));

    updateCartCount();

    showSuccessNotification(`"${productName}" agregado al carrito`);
}

/**
 * Actualizar contador del carrito
 */
function updateCartCount() {
    if (typeof globalThis.updateCartCountFromStorage === 'function') {
        globalThis.updateCartCountFromStorage();
        return;
    }

    const countBadge = document.getElementById('cart-count');
    if (!countBadge) return;

    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    countBadge.textContent = totalItems;

    if (totalItems > 0) {
        countBadge.classList.add('badge-warning');
        countBadge.classList.remove('badge-light');
    } else {
        countBadge.classList.remove('badge-warning');
        countBadge.classList.add('badge-light');
    }
}

/**
 * Mostrar/ocultar mensaje de "no hay resultados"
 */
function toggleNoResults(show) {
    const noResultsDiv = document.getElementById('no-results');
    const gridDiv = document.getElementById('products-grid');
    
    if (noResultsDiv && gridDiv) {
        if (show) {
            noResultsDiv.classList.remove('d-none');
            gridDiv.classList.add('d-none');
        } else {
            noResultsDiv.classList.add('d-none');
            gridDiv.classList.remove('d-none');
        }
    }
}

/**
 * Mostrar alerta de stock insuficiente
 */
function showStockAlert(productName, maxStock) {
    Swal.fire({
        icon: 'warning',
        title: 'Stock Insuficiente',
        html: `
            <p>Ya has agregado la cantidad máxima disponible de <strong>"${productName}"</strong></p>
            <p class="text-muted">Stock disponible: ${maxStock} unidades</p>
        `,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#3085d6'
    });
}

/**
 * Mostrar notificación de éxito
 */
function showSuccessNotification(message) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: 'success',
        title: message
    });
}

/**
 * Limpiar todos los filtros
 */
function clearFilters() {
    const searchInput = document.getElementById('search-product');
    const typeSelect = document.getElementById('filter-type');
    const sortSelect = document.getElementById('sort-by');

    if (searchInput) searchInput.value = '';
    if (typeSelect) {
        typeSelect.value = '';
        if (typeof $ !== 'undefined' && $?.fn?.select2) {
            $(typeSelect).val(null).trigger('change');
        }
    }
    if (sortSelect) sortSelect.value = 'name';

    applyFilters();
}

// Exportar funciones para uso externo
globalThis.inventarioCard = {
    applyFilters,
    sortProducts,
    clearFilters,
    addToCart,
    updateCartCount
};

globalThis.closeProductModal = closeProductModal;
globalThis.agregarAlCarritoDesdeModal = agregarAlCarritoDesdeModal;
globalThis.expandirImagen = expandirImagen;
