@extends('inventario.email.layout')

@section('content')
    <div class="greeting">
        Hola, {{ $notifiable->name }}
    </div>

    <div class="content">
        <p>
            Se ha registrado una devolución de <strong>{{ $producto->name ?? 'un producto' }}</strong>
            por parte de {{ $solicitante->name ?? 'un usuario' }}.
        </p>
    </div>

    <div class="info-box">
        <div class="info-box-title">Detalles de la devolución</div>
        <div class="info-row">
            <span class="info-label">Orden:</span>
            <span class="info-value">#{{ $detalleOrden->orden->id ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Producto:</span>
            <span class="info-value">{{ $producto->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cantidad devuelta:</span>
            <span class="info-value">{{ $cantidadDevuelta }} {{ $cantidadDevuelta === 1 ? 'unidad' : 'unidades' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Usuario:</span>
            <span class="info-value">{{ $solicitante->name ?? 'Usuario no identificado' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha de registro:</span>
            <span class="info-value">{{ $fechaDevolucion }}</span>
        </div>
    </div>

    @if(!empty($devolucion->observaciones))
        <div class="info-box">
            <div class="info-box-title">Observaciones</div>
            <p class="mb-0">{{ $devolucion->observaciones }}</p>
        </div>
    @endif

    @if($devolucion->cierra_sin_stock)
        <div class="alert-box warning">
            <div>
                <strong>Consumo reportado</strong><br>
                La devolución se registró como cierre sin stock.
            </div>
        </div>
    @endif

    <div class="button-container">
        <a href="{{ url('/inventario/devoluciones') }}" class="button">
            Ver devoluciones
        </a>
    </div>

    <div class="content">
        <p>Recuerda revisar el stock actualizado y confirmar que el producto esté disponible.</p>
    </div>

    <div class="signature">
        Gracias,<br>
        Equipo de Inventario
    </div>
@endsection

