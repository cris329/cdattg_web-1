/**
 * global.js - Script global para el módulo de Inventario
 * Maneja funcionalidades compartidas como el contador del carrito
 */

const STORAGE_KEY = 'inventario_carrito';

/**
 * Actualizar contador del carrito desde localStorage
 * Busca todos los elementos con id 'cart-count' y 'cartCount'
 */
function updateCartCountFromStorage() {
    // Buscar todos los posibles elementos del contador
    const countBadges = [
        document.getElementById('cart-count'),
        document.getElementById('cartCount'),
        ...document.querySelectorAll('[id*="cart"][id*="count" i]')
    ].filter(Boolean);

    if (countBadges.length === 0) {
        return;
    }

    // Obtener carrito desde localStorage
    const cart = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

    // Actualizar todos los badges encontrados
    for (const badge of countBadges) {
        badge.textContent = totalItems;

        // Animar el badge si hay items
        if (totalItems > 0) {
            badge.classList.add('badge-warning');
            badge.classList.remove('badge-light');
            badge.style.display = '';
        } else {
            badge.classList.remove('badge-warning');
            badge.classList.add('badge-light');
            // Ocultar si está vacío (solo si tiene display: none por defecto)
            if (badge.id === 'cartCount' && badge.style.display === 'none') {
                badge.style.display = 'none';
            }
        }
    }
}

/**
 * Inicializar el contador del carrito
 */
function initializeCartCount() {
    updateCartCountFromStorage();
}

/**
 * Escuchar cambios en localStorage (cuando se actualiza desde otra pestaña/ventana)
 */
function setupStorageListener() {
    window.addEventListener('storage', function(event) {
        if (event.key === STORAGE_KEY) {
            updateCartCountFromStorage();
        }
    });
}

/**
 * Escuchar eventos de navegación de Livewire
 */
function setupLivewireNavigationListener() {
    let lastUrl = location.href;
    let navigationTimeout = null;

    // Función para actualizar después de navegación
    const handleNavigation = () => {
        if (navigationTimeout) {
            clearTimeout(navigationTimeout);
        }
        navigationTimeout = setTimeout(() => {
            updateCartCountFromStorage();
            
            // Si estamos en la página del carrito, intentar inicializarlo
            const isCartPage = window.location.pathname.includes('carrito-sena') || 
                              window.location.pathname.includes('carrito') ||
                              document.getElementById('cart-items-container') !== null;
            
            if (isCartPage) {
                // Intentar usar la función del script carrito.js si está disponible
                if (typeof globalThis.initializeCartPage === 'function') {
                    globalThis.initializeCartPage();
                } else {
                    // Fallback: cargar items básicos
                    loadCartItemsFallback();
                }
            }

            // Si estamos en la página del catálogo, intentar inicializarlo
            const isCatalogPage = (window.location.pathname.includes('productos') && 
                                   window.location.pathname.includes('catalogo')) ||
                                  document.getElementById('products-grid') !== null;
            
            if (isCatalogPage && typeof globalThis.initializeCatalogPage === 'function') {
                globalThis.initializeCatalogPage();
            }
        }, 200);
    };

    // Escuchar cuando Livewire navega (wire:navigate)
    if (typeof Livewire !== 'undefined') {
        // Livewire 3 - eventos de navegación
        if (typeof Livewire.on === 'function') {
            Livewire.on('navigate', handleNavigation);
        }

        // También escuchar eventos de hook cuando se actualiza el DOM
        if (typeof Livewire.hook === 'function') {
            Livewire.hook('morph.updated', handleNavigation);
        }
    }

    // Escuchar eventos de navegación del navegador (turbo, etc.)
    document.addEventListener('turbo:load', handleNavigation);
    document.addEventListener('turbo:render', handleNavigation);

    // Escuchar clics en enlaces con wire:navigate
    document.addEventListener('click', function(event) {
        const link = event.target.closest('a[wire\\:navigate], a[data-wire-navigate]');
        if (link && link.href && link.href !== '#') {
            // Actualizar después de un delay para que Livewire procese la navegación
            handleNavigation();
        }
    }, true);

    // Escuchar cambios en la URL usando popstate (para navegación SPA)
    window.addEventListener('popstate', handleNavigation);

    // Observar cambios en la URL directamente
    const urlObserver = setInterval(() => {
        const currentUrl = location.href;
        if (currentUrl !== lastUrl) {
            lastUrl = currentUrl;
            handleNavigation();
        }
    }, 500);

    // Limpiar observer cuando se descargue la página
    window.addEventListener('beforeunload', () => {
        clearInterval(urlObserver);
    });

    // Observar cambios en el DOM que puedan indicar navegación
    const domObserver = new MutationObserver((mutations) => {
        // Solo actualizar si hay cambios significativos en el contenido
        const hasSignificantChanges = mutations.some(mutation => {
            return mutation.addedNodes.length > 0 || 
                   mutation.removedNodes.length > 0 ||
                   (mutation.type === 'attributes' && mutation.attributeName === 'class');
        });

        if (hasSignificantChanges) {
            const currentUrl = location.href;
            if (currentUrl !== lastUrl) {
                lastUrl = currentUrl;
                handleNavigation();
            }
        }
    });

    // Observar cambios en el body (cuando se actualiza el contenido)
    domObserver.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class']
    });
}

/**
 * Inicialización cuando el DOM está listo
 */
function initializeGlobalCart() {
    initializeCartCount();
    setupStorageListener();
    setupLivewireNavigationListener();

    // También actualizar periódicamente como fallback (cada 5 segundos)
    // Esto asegura que el contador se actualice incluso si los eventos fallan
    // Solo se ejecuta si hay elementos del contador en la página
    setInterval(() => {
        const hasCartElements = document.getElementById('cart-count') || 
                               document.getElementById('cartCount');
        if (hasCartElements) {
            updateCartCountFromStorage();
        }
    }, 5000);
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
    // DOM aún cargando, esperar DOMContentLoaded
    document.addEventListener('DOMContentLoaded', initializeGlobalCart);
} else {
    // DOM ya está listo, ejecutar inmediatamente
    initializeGlobalCart();
}

/**
 * Cargar items del carrito en la página del carrito (fallback)
 */
function loadCartItemsFallback() {
    const cartContainer = document.getElementById('cart-items-container');
    const cartItemsBody = document.getElementById('cart-items-body');
    const emptyMessage = document.getElementById('empty-cart-message');
    const cartTable = document.getElementById('cart-items-table');
    
    if (!cartContainer) {
        return; // No estamos en la página del carrito
    }

    // Recargar carrito desde localStorage
    const cart = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    
    if (cart.length === 0) {
        // Mostrar mensaje de carrito vacío
        if (emptyMessage) {
            emptyMessage.classList.remove('d-none');
        }
        if (cartTable) {
            cartTable.classList.add('d-none');
        }
        return;
    }

    // Ocultar mensaje vacío y mostrar tabla
    if (emptyMessage) {
        emptyMessage.classList.add('d-none');
    }
    if (cartTable) {
        cartTable.classList.remove('d-none');
    }

    // Si el script carrito.js está disponible, dejar que él maneje el renderizado
    // Si no, al menos actualizamos la visibilidad
    if (typeof globalThis.initializeCartPage === 'function') {
        globalThis.initializeCartPage();
    }
}

// Exportar funciones para uso externo
globalThis.updateCartCountFromStorage = updateCartCountFromStorage;
globalThis.loadCartItemsFallback = loadCartItemsFallback;

