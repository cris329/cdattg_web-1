<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiqueta - {{ $producto->name }}</title>
    <style>
        @page { size: auto; margin: 10mm; }
        body { font-family: Arial, sans-serif; }
        .label { width: 80mm; }
        .title { font-size: 12px; margin-bottom: 6px; }
        .barcode { width: 100%; }
        .code { font-size: 11px; text-align: center; margin-top: 4px; }
    </style>
</head>
<body onload="window.print()">
    <div class="label">
        <div class="title">{{ $producto->name }}</div>
        @if(!empty($barcodeImage))
            <img src="{{ $barcodeImage }}" alt="Código de barras" class="barcode">
        @else
            <div class="code">{{ $producto->codigo_barras ?? 'SIN CODIGO' }}</div>
        @endif
    </div>
</body>
</html>
