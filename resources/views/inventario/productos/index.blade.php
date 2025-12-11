@extends('inventario.layouts.base')

@section('title', 'Gestión de Productos')

@include('inventario._components.common-css')

@section('content_header')
    <x-page-header
        icon="fas fa-boxes"
        title="Gestión de Productos"
        subtitle="Administra los productos del inventario"
        :breadcrumb="[
            ['label' => 'Inicio', 'url' => '#'],
            ['label' => 'Inventario', 'active' => true],
            ['label' => 'Productos', 'active' => true]
        ]"
    />
@endsection

@section('content')
    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-create-card
                        url="{{ route('inventario.productos.create') }}"
                        title="Crear Producto"
                        icon="fa-plus-circle"
                        permission="CREAR PRODUCTO"
                    />

                    {{-- Filtros adicionales --}}
                    <div class="row mb-3 mt-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="GET" action="{{ route('inventario.productos.index') }}">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="filtro_estado">
                                                        <i class="fas fa-info-circle"></i> Estado del producto
                                                    </label>
                                                    <select
                                                        id="filtro_estado"
                                                        name="estado_producto_id"
                                                        class="form-control"
                                                    >
                                                        <option value="">Todos los estados</option>
                                                        <option value="solo_agotado" {{ request('estado_producto_id') === 'solo_agotado' ? 'selected' : '' }}>
                                                            AGOTADO
                                                        </option>
                                                        <option value="bajo_stock" {{ request('estado_producto_id') === 'bajo_stock' ? 'selected' : '' }}>
                                                            BAJO STOCK
                                                        </option>
                                                        @foreach($estadosProducto as $estado)
                                                            <option value="{{ $estado->id }}"
                                                                {{ (string)request('estado_producto_id') === (string)$estado->id ? 'selected' : '' }}>
                                                                {{ $estado->parametro->name ?? '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="filtro_categoria">
                                                        <i class="fas fa-folder"></i> Categoría
                                                    </label>
                                                    <select
                                                        id="filtro_categoria"
                                                        name="categoria_id"
                                                        class="form-control"
                                                    >
                                                        <option value="">Todas las categorías</option>
                                                        @foreach($categorias as $categoria)
                                                            <option value="{{ $categoria->parametro->id }}"
                                                                {{ (string)request('categoria_id') === (string)$categoria->parametro->id ? 'selected' : '' }}>
                                                                {{ $categoria->parametro->name ?? '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="filtro_marca">
                                                        <i class="fas fa-tag"></i> Marca
                                                    </label>
                                                    <select
                                                        id="filtro_marca"
                                                        name="marca_id"
                                                        class="form-control"
                                                    >
                                                        <option value="">Todas las marcas</option>
                                                        @foreach($marcas as $marca)
                                                            <option value="{{ $marca->parametro->id }}"
                                                                {{ (string)request('marca_id') === (string)$marca->parametro->id ? 'selected' : '' }}>
                                                                {{ $marca->parametro->name ?? '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-filter"></i> Aplicar filtros
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Botones de acciones -->
                    <div class="d-flex justify-content-end flex-wrap mt-3 mb-4">
                        <button class="btn btn-secondary btn-lg mr-2" data-toggle="modal" data-target="#modalEscanear">
                            <i class="fas fa-barcode"></i> Escanear Código de Barras
                        </button>
                        <a href="{{ route('inventario.productos.exportar-pdf') }}" class="btn btn-danger btn-lg mr-2">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </a>
                        <a href="{{ route('inventario.productos.exportar-excel') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </a>
                    </div>

                    <x-data-table
                        title="Lista de Productos"
                        searchable="true"
                        searchAction="{{ route('inventario.productos.index') }}"
                        searchPlaceholder="Buscar producto..."
                        searchValue="{{ request('search') }}"
                        :columns="[
                            ['label' => 'Id', 'width' => '3%'],
                            ['label' => 'Producto', 'width' => '20%'],
                            ['label' => 'Código', 'width' => '14%'],
                            ['label' => 'Categoría', 'width' => '10%'],
                            ['label' => 'Marca', 'width' => '10%'],
                            ['label' => 'Cantidad', 'width' => '8%'],
                            ['label' => 'Peso', 'width' => '8%'],
                            ['label' => 'Estado', 'width' => '8%'],
                            ['label' => 'Contrato', 'width' => '6%'],
                            ['label' => 'Proveedor', 'width' => '7%'],
                            ['label' => 'Opciones', 'width' => '6%', 'class' => 'text-center']
                        ]"
                        :pagination="$productos->links()"
                    >
                        @forelse ($productos as $producto)
                            <tr>
                                <td>{{ $producto->id }}</td>
                                <td>
                                    <strong>{{ $producto->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ Str::limit($producto->descripcion, 30) ?? 'Sin descripción' }}
                                    </small>
                                </td>
                                <td>
                                    @if($producto->codigo_barras)
                                        <div><span class="badge badge-secondary">{{ $producto->codigo_barras }}</span></div>
                                        <div class="mt-1">
                                            <a class="btn btn-xs btn-outline-primary" target="_blank" href="{{ route('inventario.productos.etiqueta', $producto->id) }}">
                                                <i class="fas fa-print"></i> Imprimir
                                            </a>
                                        </div>
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $producto->categoria->name ?? 'Sin categoría' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge badge-dark">
                                        {{ $producto->marca->name ?? 'Sin marca' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $stockClass = 'success';
                                        if ($producto->cantidad <= 5) $stockClass = 'danger';
                                        elseif ($producto->cantidad <= 10) $stockClass = 'warning';
                                        elseif ($producto->cantidad <= 20) $stockClass = 'info';
                                    @endphp
                                    <span class="badge badge-{{ $stockClass }}">
                                        {{ $producto->cantidad }}
                                    </span>
                                </td>
                                <td>
                                    @if($producto->peso && $producto->unidadMedida)
                                        <small>{{ $producto->peso }} {{ $producto->unidadMedida->parametro->name ?? '' }}</small>
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $estadoClass = 'success';
                                        $estadoText = 'DISPONIBLE';
                                        $estadoProducto = $producto->estado?->parametro?->name;
                                        if ($estadoProducto === 'AGOTADO') {
                                            $estadoClass = 'danger';
                                            $estadoText = 'AGOTADO';
                                        } elseif ($producto->cantidad <= 0) {
                                            $estadoClass = 'danger';
                                            $estadoText = 'AGOTADO';
                                        } elseif ($producto->cantidad <= 5) {
                                            $estadoClass = 'warning';
                                            $estadoText = 'BAJO STOCK';
                                        }
                                    @endphp
                                    <span class="badge badge-{{ $estadoClass }}">
                                        {{ $estadoText }}
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        {{ $producto->contratoConvenio->name ?? 'N/A' }}
                                    </small>
                                </td>
                                <td>
                                    <small>
                                        {{ $producto->proveedor->name ?? 'N/A' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <x-action-buttons
                                        show="true"
                                        edit="true"
                                        delete="true"
                                        showUrl="{{ route('inventario.productos.show', $producto->id) }}"
                                        editUrl="{{ route('inventario.productos.edit', $producto->id) }}"
                                        deleteUrl="{{ route('inventario.productos.destroy', $producto->id) }}"
                                        showTitle="Ver producto"
                                        editTitle="Editar producto"
                                        deleteTitle="Eliminar producto"
                                    />
                                </td>
                            </tr>
                        @empty
                            <x-table-empty
                                colspan="10"
                                message="No hay productos registrados"
                                icon="fas fa-box"
                            />
                        @endforelse
                    </x-data-table>
                    <div class="float-left pt-2">
                        <small class="text-muted">
                            Mostrando {{ $productos->firstItem() ?? 0 }} a {{ $productos->lastItem() ?? 0 }}
                            de {{ $productos->total() }} productos
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal para escanear código de barras -->
    <div class="modal fade" id="modalEscanear" tabindex="-1" aria-labelledby="modalEscanearLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="modalEscanearLabel">
                        <i class="fas fa-barcode"></i> Escanear Código de Barras
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center">
                    <p>Escanea el código de barras del producto usando el lector.</p>
                    <input type="text" id="inputCodigoBarras" class="form-control form-control-lg text-center"
                        placeholder="Esperando código..." autocomplete="off" autofocus>
                    <div id="resultadoBusqueda" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de confirmación de eliminación --}}
    <x-confirm-delete-modal />

    {{-- Alertas --}}
    {{-- Notificaciones manejadas globalmente por sweetalert2-notifications --}}
@endsection


@push('css')
    {{-- base.css se carga desde common-css.blade.php --}}
@endpush

@push('scripts')
    @vite(['resources/js/inventario/escaner.js'])
    @vite(['resources/js/pages/formularios-generico.js'])
@endpush
