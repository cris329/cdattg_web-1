@extends('inventario.layouts.base')

@section('title', 'Notificaciones')

@section('css')
<!-- Estilos personalizados para notificaciones -->
@vite([
        'resources/css/inventario/notificaciones.css'
    ])
@endsection

@section('content_header')
    <x-page-header
        icon="fas fa-bell"
        title="Notificaciones"
        subtitle="Administra tus notificaciones del sistema"
        :breadcrumb="[
            ['label' => 'Inicio', 'url' => '#'],
            ['label' => 'Notificaciones', 'active' => true]
        ]"
    />
@endsection

@section('content')
    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h3 class="card-title text-dark font-weight-bold">
                                <i class="fas fa-bell mr-2 text-primary"></i> Mis Notificaciones
                                @if($notificaciones->total() > 0)
                                    <span class="badge badge-primary ml-2">{{ $notificaciones->total() }}</span>
                                @endif
                            </h3>
                            @if($notificaciones->count() > 0)
                                <div class="card-tools">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary mr-1"
                                        id="marcar-todas-leidas"
                                        title="Marcar todas como leídas"
                                    >
                                        <i class="fas fa-check-double"></i> Marcar leídas
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger"
                                        id="vaciar-notificaciones"
                                        title="Eliminar todas las notificaciones"
                                    >
                                        <i class="fas fa-trash-alt"></i> Vaciar todo
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            @if($notificaciones->count() > 0)
                                <div class="list-group list-group-flush">
                                    @foreach($notificaciones as $notificacion)
                                        @php
                                            $tipo = $notificacion->tipo;
                                            $icon = 'fa-bell';
                                            $color = 'info';
                                            
                                            if(str_contains($tipo, 'StockBajo')) {
                                                $icon = 'fa-exclamation-triangle';
                                                $color = 'warning';
                                            } elseif(str_contains($tipo, 'Aprobada')) {
                                                $icon = 'fa-check-circle';
                                                $color = 'success';
                                            } elseif(str_contains($tipo, 'Rechazada')) {
                                                $icon = 'fa-times-circle';
                                                $color = 'danger';
                                            } elseif(str_contains($tipo, 'NuevaOrden')) {
                                                $icon = 'fa-file-alt';
                                                $color = 'primary';
                                            } elseif(str_contains($tipo, 'DevolucionRegistrada')) {
                                                $icon = 'fa-undo';
                                                $color = 'success';
                                            }
                                            
                                            // Decodificar datos si es string
                                            $datos = is_string($notificacion->datos)
                                                ? json_decode($notificacion->datos, true)
                                                : $notificacion->datos;
                                            $datosTipo = $datos['tipo'] ?? null;
                                            $accionUrl = null;

                                            if ($datosTipo === 'stock_bajo' && isset($datos['producto_id'])) {
                                                $accionUrl = route('inventario.productos.show', ['producto' => $datos['producto_id']]);
                                            } elseif (
                                                in_array($datosTipo, ['orden_aprobada', 'orden_rechazada'], true)
                                                && isset($datos['orden_id'])
                                            ) {
                                                $accionUrl = route('inventario.ordenes.show', ['orden' => $datos['orden_id'], 'ref' => url()->current()]);
                                            } elseif ($datosTipo === 'nueva_orden') {
                                                $accionUrl = isset($datos['orden_id'])
                                                    ? route('inventario.aprobaciones.pendientes') . '?orden=' . $datos['orden_id']
                                                    : route('inventario.aprobaciones.pendientes');
                                            } elseif (isset($datos['orden_id'])) {
                                                $accionUrl = route('inventario.ordenes.show', ['orden' => $datos['orden_id'], 'ref' => url()->current()]);
                                            }
                                        @endphp
                                        
                                        <div class="list-group-item {{ is_null($notificacion->leida_en) ? 'list-group-item-light border-left border-primary border-3' : 'bg-white' }}">
                                            <div class="d-flex w-100 justify-content-between align-items-start">
                                                <div class="d-flex">
                                                    <div class="mr-3 mt-1">
                                                        <div class="bg-{{ $color }} rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <i class="fas {{ $icon }} text-white fa-lg"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 font-weight-bold text-dark">
                                                            @if(str_contains($tipo, 'StockBajo'))
                                                                Stock Bajo
                                                            @elseif(str_contains($tipo, 'Aprobada'))
                                                                Aprobada
                                                            @elseif(str_contains($tipo, 'Rechazada'))
                                                                Rechazada
                                                            @elseif(str_contains($tipo, 'NuevaOrden'))
                                                                Nueva Orden
                                                            @endif
                                                            <small class="text-muted font-weight-normal">• {{ $notificacion->created_at->diffForHumans() }}</small>
                                                        </h6>

                                                        <p class="mb-1 text-dark">
                                                            @if(str_contains($tipo, 'StockBajo'))
                                                                <strong>
                                                                    {{ $datos['producto_nombre'] ?? ($datos['producto']['name'] ?? 'N/A') }}
                                                                </strong>
                                                                - Stock:
                                                                <span class="badge badge-{{ ($datos['stock_actual'] ?? 0) == 0 ? 'danger' : 'warning' }}">
                                                                    {{ $datos['stock_actual'] ?? 0 }}
                                                                </span>
                                                            @elseif(str_contains($tipo, 'Aprobada'))
                                                                Tu solicitud de
                                                                <strong>
                                                                    {{ $datos['cantidad'] ?? 0 }}
                                                                    {{ ($datos['cantidad'] ?? 0) > 1 ? 'unidades' : 'unidad' }}
                                                                </strong>
                                                                de
                                                                <strong>{{ $datos['producto']['name'] ?? 'N/A' }}</strong>
                                                                ha sido aprobada.
                                                                <br>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-user-check"></i>
                                                                    Aprobado por:
                                                                    <strong>{{ $datos['aprobador']['name'] ?? 'N/A' }}</strong>
                                                                    |
                                                                    <i class="fas fa-shopping-cart"></i>
                                                                    Orden #{{ $datos['orden_id'] ?? 'N/A' }}
                                                                </small>
                                                            @elseif(str_contains($tipo, 'Rechazada'))
                                                                Tu solicitud de
                                                                <strong>
                                                                    {{ $datos['cantidad'] ?? 0 }}
                                                                    {{ ($datos['cantidad'] ?? 0) > 1 ? 'unidades' : 'unidad' }}
                                                                </strong>
                                                                de
                                                                <strong>{{ $datos['producto']['name'] ?? 'N/A' }}</strong>
                                                                ha sido rechazada.
                                                                <br>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-user-times"></i>
                                                                    Rechazado por:
                                                                    <strong>{{ $datos['aprobador']['name'] ?? 'N/A' }}</strong>
                                                                    |
                                                                    <i class="fas fa-shopping-cart"></i>
                                                                    Orden #{{ $datos['orden_id'] ?? 'N/A' }}
                                                                </small>
                                                                @if(isset($datos['motivo_rechazo']) && !empty(trim($datos['motivo_rechazo'])))
                                                                    <div class="alert alert-warning mt-2 mb-0 py-2">
                                                                        <strong><i class="fas fa-info-circle"></i> Motivo del rechazo:</strong>
                                                                        <p class="mb-0 mt-1">{{ $datos['motivo_rechazo'] }}</p>
                                                                    </div>
                                                                @endif
                                                            @elseif(str_contains($tipo, 'NuevaOrden'))
                                                                <strong>Orden #{{ $datos['orden_id'] ?? 'N/A' }}</strong>
                                                                - {{ $datos['tipo_orden'] ?? 'N/A' }}
                                                                <br>{{ $datos['solicitante']['name'] ?? 'N/A' }}
                                                                <span class="badge badge-info">{{ $datos['solicitante']['rol'] ?? 'N/A' }}</span>
                                                            @elseif(str_contains($tipo, 'DevolucionRegistrada'))
                                                                <strong>Devolución registrada</strong>
                                                                <br>
                                                                <span class="text-muted">
                                                                    {{ $datos['producto_nombre'] ?? 'Producto sin nombre' }}
                                                                    • {{ $datos['cantidad_devuelta'] ?? 0 }} {{ ($datos['cantidad_devuelta'] ?? 0) === 1 ? 'unidad' : 'unidades' }}
                                                                </span>
                                                                <br>
                                                                <small>
                                                                    Usuario: {{ $datos['usuario']['name'] ?? 'N/A' }}
                                                                </small>
                                                                <br>
                                                                <small class="text-muted">
                                                                    Orden #{{ $datos['orden_id'] ?? 'N/A' }}
                                                                </small>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="ml-2 d-flex flex-column">
                                                    @if($accionUrl)
                                                        <button
                                                            class="btn btn-sm btn-info mb-1 text-white open-notification"
                                                            data-id="{{ $notificacion->id }}"
                                                            data-url="{{ $accionUrl }}"
                                                            data-unread="{{ is_null($notificacion->leida_en) ? 'true' : 'false' }}"
                                                            title="Ver detalle"
                                                        >
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </button>
                                                    @endif
                                                    @if(is_null($notificacion->leida_en))
                                                        <button
                                                            class="btn btn-sm btn-success mb-1 mark-read"
                                                            data-id="{{ $notificacion->id }}"
                                                            title="Marcar como leída"
                                                        >
                                                            <i class="fas fa-check text-white"></i>
                                                        </button>
                                                    @else
                                                        <span class="badge badge-success mb-1 p-2" title="Leída">
                                                            <i class="fas fa-check text-white"></i>
                                                        </span>
                                                    @endif
                                                    <button
                                                        class="btn btn-sm btn-danger delete-notification"
                                                        data-id="{{ $notificacion->id }}"
                                                        title="Eliminar"
                                                    >
                                                        <i class="fas fa-trash text-white"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="card-footer clearfix">
                                    <div class="float-right">
                                        {{ $notificaciones->links('pagination::bootstrap-4') }}
                                    </div>
                                    <div class="float-left pt-2">
                                        <small class="text-muted">
                                            Mostrando {{ $notificaciones->firstItem() ?? 0 }} a {{ $notificaciones->lastItem() ?? 0 }}
                                            de {{ $notificaciones->total() }} notificaciones
                                        </small>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No tienes notificaciones</h5>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
  {{-- Notificaciones manejadas globalmente por sweetalert2-notifications --}}
@endsection

@include('inventario._components.common-footer')

@section('js')

@vite(['resources/js/inventario/notificaciones.js'])
@endsection




