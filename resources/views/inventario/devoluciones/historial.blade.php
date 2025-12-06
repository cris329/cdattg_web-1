@extends('inventario.layouts.base')

@section('title', 'Historial de Devoluciones')

@include('inventario._components.common-css')

@section('content_header')
    <x-page-header
        icon="fas fa-history"
        title="Historial de Devoluciones"
        subtitle="Registro completo de todas las devoluciones"
        :breadcrumb="[
            ['label' => 'Inicio', 'url' => '#'],
            ['label' => 'Inventario', 'active' => true],
            ['label' => 'Devoluciones', 'url' => route('inventario.devoluciones.index')],
            ['label' => 'Historial', 'active' => true]
        ]"
    />
@endsection

@section('content')
    <section class="content mt-4">
        <div class="container-fluid">
            @include('components.session-alerts')

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Historial Completo de Devoluciones</h5>
                        </div>
                        <div class="card-body">
                            @if($devoluciones && $devoluciones->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <caption id="devoluciones-historial-description" class="sr-only">
                                            Listado de devoluciones con información de producto, cantidad devuelta, fecha de devolución, observaciones y acciones disponibles.
                                        </caption>
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Orden</th>
                                                <th>Cantidad Devuelta</th>
                                                <th>Fecha Devolución</th>
                                                <th>Observaciones</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($devoluciones as $devolucion)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $devolucion->detalleOrden->producto->producto ?? 'N/A' }}</strong>
                                                        @if($devolucion->detalleOrden && $devolucion->detalleOrden->producto)
                                                            <br>
                                                            <small class="text-muted">{{ $devolucion->detalleOrden->producto->descripcion ?? '' }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        #{{ $devolucion->detalleOrden->orden->id ?? 'N/A' }}
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">
                                                            {{ $devolucion->cantidad_devuelta }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{ $devolucion->created_at ? $devolucion->created_at->format('d/m/Y H:i') : 'Sin fecha' }}
                                                    </td>
                                                    <td>
                                                        @if($devolucion->observaciones)
                                                            <small class="text-muted">{{ Str::limit($devolucion->observaciones, 50) }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($devolucion->cierra_sin_stock)
                                                            <span class="badge badge-warning">
                                                                <i class="fas fa-exclamation-triangle"></i> Cierre sin stock
                                                            </span>
                                                        @else
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check"></i> Stock restaurado
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a
                                                            href="{{ route('inventario.devoluciones.show', $devolucion->id) }}"
                                                            class="btn btn-sm btn-info"
                                                            title="Ver detalles"
                                                        >
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if($devolucion->detalleOrden && $devolucion->detalleOrden->orden)
                                                            <a
                                                                href="{{ route('inventario.ordenes.show', $devolucion->detalleOrden->orden->id) }}"
                                                                class="btn btn-sm btn-secondary"
                                                                title="Ver orden"
                                                            >
                                                                <i class="fas fa-file-invoice"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if($devoluciones instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $devoluciones->links() }}
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                    <h5>No hay devoluciones registradas</h5>
                                    <p class="text-muted">Aún no se han registrado devoluciones en el sistema.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@include('inventario._components.common-footer')
