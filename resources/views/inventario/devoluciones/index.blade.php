@extends('inventario.layouts.base')

@section('title', 'Préstamos Pendientes de Devolución')

@include('inventario._components.common-css')

@section('content_header')
    <x-page-header
        icon="fas fa-undo"
        title="Préstamos Pendientes de Devolución"
        subtitle="Lista de préstamos que requieren devolución"
        :breadcrumb="[
            ['label' => 'Inicio', 'url' => '#'],
            ['label' => 'Inventario', 'active' => true],
            ['label' => 'Devoluciones', 'active' => true]
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
                            <h5 class="card-title mb-0">Préstamos Pendientes</h5>
                        </div>
                        <div class="card-body">
                            @if($prestamos->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <caption id="devoluciones-description" class="sr-only">
                                            Listado de devoluciones con información de producto, cantidad prestada,
                                            cantidad devuelta, cantidad pendiente, fecha de préstamo y fecha de devolución.
                                        </caption>
                                        <thead>
                                            <tr>
                                                <th>Producto</th>
                                                <th>Cantidad Prestada</th>
                                                <th>Cantidad Devuelta</th>
                                                <th>Cantidad Pendiente</th>
                                                <th>Fecha Préstamo</th>
                                                <th>Fecha Devolución</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($prestamos as $detalle)
                                                @php
                                                    $producto = $detalle->producto;
                                                    $productoNombre = $producto?->name ?? 'N/A';
                                                    $productoDescripcion = $producto?->descripcion ?? '';
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $productoNombre }}</strong>
                                                        @if($productoDescripcion)
                                                        <br>
                                                        <small class="text-muted">{{ $productoDescripcion }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $detalle->cantidad }}</td>
                                                    <td>{{ $detalle->getCantidadDevuelta() }}</td>
                                                    <td>
                                                        <span class="badge badge-warning">
                                                            {{ $detalle->getCantidadPendiente() }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        {{ $detalle->orden->created_at ? $detalle->orden->created_at->format('d/m/Y') : 'Sin fecha' }}
                                                    </td>
                                                    <td>
                                                        {{ $detalle->orden->fecha_devolucion ? $detalle->orden->fecha_devolucion->format('d/m/Y') : 'Sin fecha' }}
                                                    </td>
                                                    
                                                    <td>
                                                        <a
                                                            href="{{ route('inventario.devoluciones.create', $detalle->id) }}"
                                                            class="btn btn-sm btn-primary"
                                                        >
                                                            <i class="fas fa-undo"></i> Devolver
                                                        </a>
                                                        <a
                                                            href="{{ route('inventario.ordenes.show', ['orden' => $detalle->orden->id, 'ref' => url()->current()]) }}"
                                                            class="btn btn-sm btn-info"
                                                        >
                                                            <i class="fas fa-eye"></i> Ver Orden
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if($prestamos instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                                    <div class="d-flex justify-content-center mt-3">
                                        {{ $prestamos->links() }}
                                    </div>
                                @endif
                            @else
                                @include('inventario._components.empty-state', [
                                    'icon' => 'fas fa-check-circle',
                                    'iconColor' => 'text-success',
                                    'title' => 'No hay préstamos pendientes',
                                    'description' => 'Todos los préstamos han sido devueltos completamente.'
                                ])
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@include('inventario._components.common-footer')

