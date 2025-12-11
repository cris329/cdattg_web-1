<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos del Inventario</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
        }
        h1 {
            text-align: center;
            margin-bottom: 6px;
            font-size: 16px;
        }
        .subtitle {
            text-align: center;
            margin-bottom: 10px;
            font-size: 9px;
            color: #555555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #dddddd;
            padding: 4px 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 9px;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <h1>Reporte de Productos del Inventario</h1>
    <div class="subtitle">
        Generado el {{ now()->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Marca</th>
                <th>Ubicación</th>
                <th class="text-right">Cantidad</th>
                <th>Peso / Unidad</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $index => $producto)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $producto->name }}</td>
                    <td>{{ $producto->categoria->name ?? 'N/A' }}</td>
                    <td>{{ $producto->marca->name ?? 'N/A' }}</td>
                    <td>{{ $producto->ambiente->title ?? 'N/A' }}</td>
                    <td class="text-right">{{ $producto->cantidad }}</td>
                    <td>
                        @if($producto->peso && $producto->unidadMedida?->parametro?->name)
                            {{ $producto->peso }} {{ $producto->unidadMedida->parametro->name }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $producto->estado?->parametro?->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>


