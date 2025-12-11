@extends('inventario.email.layout')

@section('content')
    <div class="greeting">
        Hola, {{ $notifiable->name }}
    </div>
    
    <div class="content">
        <p>Lamentamos informarte que tu solicitud de <strong>{{ strtolower($tipoOrden) }}</strong> ha sido rechazada.</p>
    </div>
    
    <div class="alert-box danger">
        <span class="alert-icon">❌</span>
        <div>
            <strong>Solicitud Rechazada</strong><br>
            Tu orden no pudo ser procesada en este momento.
        </div>
    </div>
    
    <div class="info-box">
        <div class="info-box-title"> Detalles de la Solicitud</div>
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
            <span class="info-label">Cantidad Solicitada:</span>
            <span class="info-value">{{ $detalleOrden->cantidad }} unidades</span>
        </div>
        <div class="info-row">
            <span class="info-label">Rechazado por:</span>
            <span class="info-value">{{ $aprobador->name }}</span>
        </div>
    </div>
    
    @if($motivoRechazo)
    <div class="alert-box warning">
        <div>
            <strong>Motivo del Rechazo:</strong><br>
            {{ $motivoRechazo }}
        </div>
    </div>
    @endif
    
    <div class="button-container">
        <a href="{{ url('/inventario/ordenes/' . $orden->id) }}" class="button">
            Ver Detalles
        </a>
    </div>
    
    <div class="content">
        <p>Si tienes alguna pregunta sobre el motivo del rechazo, por favor contacta al administrador del inventario.</p>
    </div>
    
    <div class="signature">
        Saludos cordiales<br>
    </div>
@endsection
