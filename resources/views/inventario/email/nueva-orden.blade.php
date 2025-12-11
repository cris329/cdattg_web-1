@extends('inventario.email.layout')

@section('content')
    <div class="greeting">
        Hola, {{ $notifiable->name }}
    </div>
    
    <div class="content">
        <p>Se ha recibido una nueva solicitud de <strong>{{ $tipoOrden }}</strong> que requiere tu aprobación.</p>
    </div>
    
    <div class="info-box">
        <div class="info-box-title">Detalles de la Solicitud</div>
        <div class="info-row">
            <span class="info-label">Orden:</span>
            <span class="info-value">#{{ $orden->id }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipo:</span>
            <span class="info-value">{{ $tipoOrden }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Solicitante:</span>
            <span class="info-value">{{ $solicitante->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $solicitante->email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Productos:</span>
            <span class="info-value">{{ $cantidadProductos }} {{ $cantidadProductos === 1 ? 'producto' : 'productos' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Motivo:</span>
            <span class="info-value">{{ \Illuminate\Support\Str::limit($motivo, 100) }}</span>
        </div>
        @if($orden->fecha_devolucion)
        <div class="info-row">
            <span class="info-label">Fecha de Devolución:</span>
            <span class="info-value">{{ $orden->fecha_devolucion->format('d/m/Y') }}</span>
        </div>
        @endif
    </div>
    
    <div class="button-container">
        <a href="{{ url('/inventario/aprobaciones/pendientes') }}" class="button">
            Revisar Solicitud
        </a>
    </div>
    
    <div class="alert-box info">    
        <div>Por favor, revisa y aprueba o rechaza esta solicitud a la brevedad para continuar con el proceso.</div>
    </div>
    
    <div class="signature">
        Saludos cordiales,<br>
    </div>
@endsection
