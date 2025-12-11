@extends('inventario.email.layout')

@section('content')
    <div class="greeting">
        ¡Hola, {{ $notifiable->name }}!
    </div>
    
    <div class="content">
        <p>¡Buenas noticias! Tu solicitud de <strong>{{ strtolower($tipoOrden) }}</strong> ha sido aprobada.</p>
    </div>
    
    <div class="alert-box success">
        <div>
            <strong>Solicitud Aprobada</strong><br>
            Tu orden ha sido procesada exitosamente y está lista para ser entregada.
        </div>
    </div>
    
    <div class="info-box">
        <div class="info-box-title">Información del Producto</div>
        <div class="info-row">
            <span class="info-label">Orden:</span>
            <span class="info-value">#{{ $orden->id }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipo:</span>
            <span class="info-value">{{ $tipoOrden }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Producto:</span>
            <span class="info-value">{{ $producto->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cantidad Aprobada:</span>
            <span class="info-value">{{ $detalleOrden->cantidad }} unidades</span>
        </div>
        <div class="info-row">
            <span class="info-label">Aprobado por:</span>
            <span class="info-value">{{ $aprobador->name }}</span>
        </div>
        @if($orden->fecha_devolucion)
        <div class="info-row">
            <span class="info-label">Fecha de Devolución:</span>
            <span class="info-value">{{ $orden->fecha_devolucion->format('d/m/Y') }}</span>
        </div>
        @endif
    </div>
    
    @if($orden->fecha_devolucion)
    <div class="alert-box warning">
        <div>
            <strong>Recordatorio Importante</strong><br>
            Recuerda devolver el producto en la fecha indicada para evitar inconvenientes.
        </div>
    </div>
    @endif
    
    <div class="button-container">
        <a href="{{ url('/inventario/ordenes/' . $orden->id) }}" class="button">
            Ver Detalles
        </a>
    </div>
    
    <div class="content">
        <p>Puedes pasar a recoger el producto en el área de inventario.</p>
    </div>
    
    <div class="signature">
        Saludos cordiales<br>
    </div>
@endsection
