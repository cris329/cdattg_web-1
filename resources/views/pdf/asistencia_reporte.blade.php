<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Asistencia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .info-section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .info-section h3 {
            margin-top: 0;
            color: #007bff;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            width: 200px;
        }
        .info-value {
            flex: 1;
        }
        .asistencia-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .asistencia-table th,
        .asistencia-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .asistencia-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .asistio-si {
            background-color: #d4edda;
            color: #155724;
        }
        .asistio-no {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE ASISTENCIA</h1>
        <h2>SENA - Servicio Nacional de Aprendizaje</h2>
    </div>

    <!-- Información de la Ficha -->
    <div class="info-section">
        <h3>📋 Información de la Ficha</h3>
        <div class="info-row">
            <span class="info-label">Número de Ficha:</span>
            <span class="info-value">{{ $fichaCaracterizacion->ficha ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Programa:</span>
            <span class="info-value">{{ $fichaCaracterizacion->programaFormacion->nombre ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Modalidad:</span>
            <span class="info-value">{{ $fichaCaracterizacion->modalidadFormacion->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Jornada:</span>
            <span class="info-value">{{ $fichaCaracterizacion->jornadaFormacion->name ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Ambiente:</span>
            <span class="info-value">{{ $fichaCaracterizacion->ambiente->piso->bloque->sede->sede ?? 'N/A' }} - 
                {{ $fichaCaracterizacion->ambiente->piso->bloque->bloque ?? 'N/A' }} - 
                {{ $fichaCaracterizacion->ambiente->ambiente ?? 'N/A' }}</span>
        </div>
    </div>

    <!-- Información del Instructor -->
    <div class="info-section">
        <h3👨 Información del Instructor</h3>
        @if($caracterizacion && $caracterizacion->instructor && $caracterizacion->instructor->persona)
            <div class="info-row">
                <span class="info-label">Nombre:</span>
                <span class="info-value">{{ $caracterizacion->instructor->persona->getNombreCompletoAttribute() }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Documento:</span>
                <span class="info-value">{{ $caracterizacion->instructor->persona->numero_documento }}</span>
            </div>
        @else
            <div class="info-row">
                <span class="info-label">Instructor:</span>
                <span class="info-value">No asignado</span>
            </div>
        @endif
    </div>

    <!-- Información de la Evidencia -->
    <div class="info-section">
        <h3📄 Información de la Evidencia</h3>
        <div class="info-row">
            <span class="info-label">Nombre:</span>
            <span class="info-value">{{ $evidencia->nombre ?? 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Fecha:</span>
            <span class="info-value">{{ $fecha }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Hora:</span>
            <span class="info-value">{{ $hora }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Estado:</span>
            <span class="info-value">Finalizada</span>
        </div>
    </div>

    <!-- Resumen de Asistencia -->
    <div class="info-section">
        <h3📊 Resumen de Asistencia</h3>
        <div class="info-row">
            <span class="info-label">Total de Aprendices:</span>
            <span class="info-value">{{ $todosLosAprendices->count() ?? 0 }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Asistieron:</span>
            <span class="info-value" style="color: #28a745; font-weight: bold;">{{ $asistieron->count() ?? 0 }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">No Asistieron:</span>
            <span class="info-value" style="color: #dc3545; font-weight: bold;">{{ $noAsistieron->count() ?? 0 }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Porcentaje Asistencia:</span>
            <span class="info-value">
                @if($todosLosAprendices->count() > 0)
                    {{ round(($asistieron->count() / $todosLosAprendices->count()) * 100, 2) }}%
                @else
                    0%
                @endif
            </span>
        </div>
    </div>

    <!-- Tabla de Aprendices que Asistieron -->
    <h3>✅ Aprendices que Asistieron ({{ $asistieron->count() }})</h3>
    <table class="asistencia-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Documento</th>
                <th>Nombre Completo</th>
                <th>Hora Ingreso</th>
            </tr>
        </thead>
        <tbody>
            @php $contador = 1; @endphp
            @foreach($asistieron as $aprendiz)
                <tr class="asistio-si">
                    <td>{{ $contador }}</td>
                    <td>{{ $aprendiz->persona->numero_documento }}</td>
                    <td>{{ $aprendiz->persona->getNombreCompletoAttribute() }}</td>
                    <td>
                        @if(isset($aprendiz->asistencia))
                            {{ Carbon\Carbon::parse($aprendiz->asistencia->hora_ingreso)->format('h:i A') }}
                        @else
                            @if($aprendiz->id && isset($asistenciaId))
                                {{-- Buscar la asistencia directamente --}}
                                @php
                                    $asistencia = \App\Models\AsistenciaAprendiz::where('aprendiz_ficha_id', $aprendiz->id)
                                        ->where('asistencia_id', $asistenciaId)
                                        ->whereDate('created_at', now()->format('Y-m-d'))
                                        ->first();
                                @endphp
                                @if($asistencia)
                                    {{ Carbon\Carbon::parse($asistencia->hora_ingreso)->format('h:i A') }}
                                @else
                                    -
                                @endif
                            @else
                                -
                            @endif
                        @endif
                    </td>
                </tr>
                @php $contador++; @endphp
            @endforeach
        </tbody>
    </table>

    <!-- Tabla de Aprendices que No Asistieron -->
    <h3>❌ Aprendices que No Asistieron ({{ $noAsistieron->count() }})</h3>
    <table class="asistencia-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Documento</th>
                <th>Nombre Completo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @php $contador = 1; @endphp
            @foreach($noAsistieron as $aprendiz)
                <tr class="asistio-no">
                    <td>{{ $contador }}</td>
                    <td>{{ $aprendiz->persona->numero_documento }}</td>
                    <td>{{ $aprendiz->persona->getNombreCompletoAttribute() }}</td>
                    <td>Ausente</td>
                </tr>
                @php $contador++; @endphp
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado el {{ $fecha }} a las {{ $hora }} | Sistema de Gestión de Asistencia SENA</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html>
