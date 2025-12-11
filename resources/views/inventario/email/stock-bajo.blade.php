@extends('inventario.email.layout')

@section('content')
    <div class="greeting">
        Hola, {{ $notifiable->name }}
    </div>
    
    <div class="content">
        <p>Te informamos que el stock del siguiente producto está por debajo del nivel mínimo establecido.</p>
    </div>
    
    <div class="alert-box warning">
        <div>
            <strong>Stock Bajo Detectado</strong><br>
            Es necesario realizar un reabastecimiento lo antes posible.
        </div>
    </div>
    
    <div class="info-box">
        <div class="info-box-title"> Información del Producto</div>
        <div class="info-row">
            <span class="info-label">Producto:</span>
            <span class="info-value"><strong>{{ $producto->name }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Código:</span>
            <span class="info-value">{{ $producto->codigo ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Categoría:</span>
            <span class="info-value">{{ $producto->categoria->parametro->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Stock actual:</span>
            <span class="info-value" style="color: #e53e3e;"><strong>{{ $stockActual }} unidades</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Stock mínimo:</span>
            <span class="info-value">{{ $stockMinimo }} unidades</span>
        </div>
        <div class="info-row">
            <span class="info-label">Diferencia:</span>
            <span class="info-value" style="color: #e53e3e;"><strong>{{ $stockMinimo - $stockActual }} unidades por debajo</strong></span>
        </div>
    </div>
    
    <div class="button-container">
        <a href="{{ url('/inventario/productos/' . $producto->id) }}" class="button">
            Ver Producto
        </a>
    </div>
    
    <div class="signature">
        Saludos cordiales<br>
    </div>
@endsection
