@extends('inventario.layouts.base')

@section('title', 'Catálogo de Productos')

@include('inventario._components.common-css')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark">
                <i class="fas fa-store"></i> Catálogo de Productos
            </h1>
            <small class="text-muted">Vista moderna de productos disponibles</small>
        </div>
        <div>
            <a href="{{ route('inventario.carrito.ecommerce') }}" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Ver Carrito
                <span class="badge badge-light" id="cart-count">0</span>
            </a>
        </div>
    </div>
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            {{-- Filtros y búsqueda --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="search-product">
                                            <i class="fas fa-search"></i> Buscar Producto
                                        </label>
                                        <input
                                            type="text"
                                            id="search-product"
                                            class="form-control"
                                            placeholder="Buscar por nombre..."
                                            value="{{ request('search') }}"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="filter-type">
                                            <i class="fas fa-box-open"></i> Tipo de producto
                                        </label>
                                        <select
                                            id="filter-type"
                                            name="filter-type"
                                            class="form-control select2"
                                            data-placeholder="Todos los tipos"
                                        >
                                            <option value="">Todos los tipos</option>
                                            @foreach($tiposProductos as $tipoProducto)
                                                <option value="{{ $tipoProducto->id }}"
                                                    {{ request('tipo_producto_id') == $tipoProducto->id ? 'selected' : '' }}>
                                                    {{ $tipoProducto->parametro->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="sort-by">
                                            <i class="fas fa-sort"></i> Ordenar por
                                        </label>
                                        <select
                                            id="sort-by"
                                            class="form-control"
                                        >
                                            <option value="name" {{ request('sort_by', 'name') == 'name' ? 'selected' : '' }}>Nombre</option>
                                            <option value="stock-asc" {{ request('sort_by') == 'stock-asc' ? 'selected' : '' }}>Stock Menor</option>
                                            <option value="stock-desc" {{ request('sort_by') == 'stock-desc' ? 'selected' : '' }}>Stock Mayor</option>
                                            <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Más Recientes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grid de productos --}}
            <div class="row" id="products-grid">
                @forelse($productos as $producto)
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4 product-card"
                         data-id="{{ $producto->id }}"
                         data-type="{{ $producto->tipo_producto_id }}"
                         data-name="{{ strtolower($producto->name) }}"
                         data-code="{{ strtolower($producto->codigo_barras) }}">
                        <div class="card h-100 shadow-sm hover-shadow">
                            {{-- Imagen del producto --}}
                            <div class="product-image-container">
                                @if($producto->imagen)
                                    <img src="{{ asset($producto->imagen) }}"
                                         class="card-img-top product-image"
                                         alt="{{ $producto->name }}"
                                @else
                                    <div class="no-image-placeholder">
                                        <i class="fas fa-box fa-4x text-muted"></i>
                                        <p class="text-muted mt-2">Sin imagen</p>
                                    </div>
                                @endif
                                
                                {{-- Badge de stock --}}
                                @php
                                    $stockClass = 'success';
                                    if ($producto->cantidad <= 0) {
                                        $stockClass = 'danger';
                                    } elseif ($producto->cantidad <= 5) {
                                        $stockClass = 'warning';
                                    }
                                @endphp
                                <span class="badge stock-badge stock-badge-{{ $stockClass }}">
                                </span>
                            </div>
                            <div class="card-body d-flex flex-column">
                                {{-- Nombre del producto --}}
                                <h5 class="card-title font-weight-bold mb-3 text-center">
                                    {{ Str::limit($producto->name, 50) }}
                                </h5>

                                {{-- Código de barras --}}
                                <div class="mb-3 text-center">
                                    <small class="text-muted d-block mb-1">
                                        <i class="fas fa-barcode"></i> Código
                                    </small>
                                    <span class="badge badge-secondary badge-lg">{{ $producto->codigo_barras ?? 'N/A' }}</span>
                                </div>

                                {{-- Acciones --}}
                                <fieldset class="btn-group d-flex border-0 p-0 m-0 mt-auto">
                                    <legend class="sr-only">Acciones del producto {{ $producto->name }}</legend>
                                    <button type="button"
                                            class="btn btn-sm btn-info btn-view-details w-50"
                                            data-id="{{ $producto->id }}"
                                            title="Ver detalles">
                                        <i class="fas fa-eye"></i> Detalles
                                    </button>
                                    @if($producto->cantidad > 0)
                                        <button type="button"
                                                class="btn btn-sm btn-success btn-add-to-cart w-50"
                                                data-id="{{ $producto->id }}"
                                                data-name="{{ $producto->name }}"
                                                data-stock="{{ $producto->cantidad }}"
                                                title="Agregar al carrito">
                                            <i class="fas fa-cart-plus"></i> Agregar
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-secondary w-50" disabled>
                                            <i class="fas fa-ban"></i> Agotado
                                        </button>
                                    @endif
                                </fieldset>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <h5>No hay productos disponibles</h5>
                            <p>Actualmente no hay productos en el catálogo.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            <div class="row">
                <div class="col-12 d-flex justify-content-center" id="catalog-pagination">
                    {{ $productos->links() }}
                </div>
            </div>

            {{-- Mensaje cuando no hay resultados de búsqueda --}}
            <div class="row d-none" id="no-results">
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <h5>No se encontraron resultados</h5>
                        <p>Intenta con otros términos de búsqueda o filtros.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modal simple de detalles del producto --}}
    <dialog
        id="productDetailModal"
        aria-labelledby="product-detail-modal-title"
        style="
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,0.5);
            z-index:9999;
            align-items:center;
            justify-content:center;
            border:none;
            padding:0;
        "
    >
        <div
            id="product-detail-modal-content"
            style="
                background:white;
                border-radius:8px;
                width:90%;
                max-width:600px;
                max-height:90vh;
                overflow-y:auto;
                box-shadow:0 4px 20px rgba(0,0,0,0.3);
            "
        >
            <!-- Header -->
            <div
                style="
                    padding:20px;
                    background:#17a2b8;
                    color:white;
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    border-radius:8px 8px 0 0;
                "
            >
                <h5 id="product-detail-modal-title" style="margin:0; font-size:18px;">
                    <i class="fas fa-box"></i> Detalles del Producto
                </h5>
                <button
                    type="button"
                    onclick="closeProductModal()"
                    aria-label="Cerrar modal de detalles del producto"
                    style="
                        background:none;
                        border:none;
                        color:white;
                        font-size:24px;
                        cursor:pointer;
                        padding:0;
                        margin:0;
                    "
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <!-- Body -->
            <div id="product-detail-content" style="padding:20px;">
                <div style="text-align:center;">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p>Cargando detalles...</p>
                </div>
            </div>
        </div>
    </dialog>

    {{-- Alertas --}}
    {{-- Notificaciones manejadas globalmente por sweetalert2-notifications --}}
@endsection

@push('css')
    @vite(['resources/css/inventario/card.css'])
@endpush

@section('js')
    @vite(['resources/js/inventario/card.js'])
@endsection

@section('footer')
    {{-- Footer SENA --}}
@include('inventario._components.common-footer')

