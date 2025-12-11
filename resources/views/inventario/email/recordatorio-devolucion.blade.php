@extends('inventario.email.layout')

@section('content')
    <div class="greeting">
        Hola, {{ $notifiable->name }}
    </div>
    
    <div class="content">
        @php
            $urgencia = match($diasRestantes) {
                3 => 'Te recordamos que',
                2 => 'Atención:',
                1 => 'URGENTE:',
                default => 'Te recordamos que'
            };
        @endphp
        <p><strong>{{ $urgencia }}</strong> tienes un préstamo pendiente de devolución.</p>
    </div>
    
    @if($diasRestantes === 1)
    <div class="alert-box danger">
        <div>
            <strong>¡Último Día!</strong><br>
            Este es el último día para devolver los productos. Por favor, realiza la devolución hoy mismo.
        </div>
    </div>
    @elseif($diasRestantes === 2)
    <div class="alert-box warning">
        <div>
            <strong>Quedan Solo 2 Días</strong><br>
            Por favor, planifica la devolución de los productos lo antes posible.
        </div>
    </div>
    @else
    <div class="alert-box info">
        <div>
            <strong>Recordatorio de Devolución</strong><br>
            Te recordamos que se acerca la fecha límite de devolución de tu préstamo.
        </div>
    </div>
    @endif
    
    <div class="info-box">
        <div class="info-box-title">Información del Préstamo</div>
        <div class="info-row">
            <span class="info-label">Orden:</span>
            <span class="info-value">#{{ $orden->id }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Productos prestados:</span>
            <span class="info-value">{{ $cantidadProductos }} {{ $cantidadProductos === 1 ? 'producto' : 'productos' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha límite:</span>
            <span class="info-value"><strong>{{ $fechaDevolucion }}</strong></span>
        </div>
        <div class="info-row">
            <span class="info-label">Días restantes:</span>
            <span class="info-value" style="color: {{ $diasRestantes <= 1 ? '#e53e3e' : ($diasRestantes === 2 ? '#ed8936' : '#39A900') }};">
                <strong>{{ $diasRestantes }} {{ $diasRestantes === 1 ? 'día' : 'días' }}</strong>
            </span>
        </div>
    </div>
    
    @if($cantidadProductos <= 5)
    <div class="info-box">
        <div class="info-box-title"> Productos a Devolver</div>
        <div class="products-list">
            @foreach($orden->detalles as $detalle)
                @php
                    $cantidadPendiente = $detalle->getCantidadPendiente();
                @endphp
                @if($cantidadPendiente > 0)
                <div class="product-item">
                    <div class="product-icon"></div>
                    <div class="product-details">
                        <div class="product-name">{{ $detalle->producto->name }}</div>
                        <div class="product-quantity">{{ $cantidadPendiente }} {{ $cantidadPendiente > 1 ? 'unidades' : 'unidad' }} pendiente{{ $cantidadPendiente > 1 ? 's' : '' }}</div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif
    
    <div class="button-container">
        <a href="{{ url('/inventario/mis-prestamos') }}" class="button">
            Ver Mis Préstamos
        </a>
    </div>
    
    <div class="content">
        <p>Por favor, asegúrate de devolver los productos antes de la fecha límite para evitar sanciones.</p>
    </div>
    
    <div class="signature">
        Saludos cordiales<br>
    </div>
@endsection
