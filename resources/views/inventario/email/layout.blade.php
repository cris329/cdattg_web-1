<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject ?? 'Notificación' }} - {{ config('app.name') }}</title>
    <style>
        @php
            $cssFiles = glob(public_path('build/assets/inventario_email_css-*.css'));
            if (!empty($cssFiles)) {
                echo file_get_contents($cssFiles[0]);
            }
        @endphp
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="title">Inventario C</div>
            </div>
            
            <!-- Body -->
            <div class="email-body">
                @yield('content')
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <div class="copyright">
                    © {{ date('Y') }} CDATTG - Regional Guaviare.<br>
                    Centro de Desarrollo Agroindustrial y Tecnológico del Guaviare.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
