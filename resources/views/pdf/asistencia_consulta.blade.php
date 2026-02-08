<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asistencia</title>
    <style>
        :root {
            --sena-green: #0f6b3d;
            --sena-green-soft: #e6f4ec;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border: #d1d5db;
        }

        body { 
            font-family: Arial, sans-serif; 
            margin: 12px; 
            font-size: 10.5px; 
            color: var(--text-main); 
        }

        .header { 
            text-align: center; 
            border-bottom: 2px solid var(--sena-green); 
            padding-bottom: 6px; 
            margin-bottom: 8px; 
        }

        .header h1 { 
            margin: 0; 
            font-size: 15px; 
            color: var(--sena-green); 
            letter-spacing: .5px; 
        }

        .header .sub { 
            margin-top: 4px; 
            font-size: 10px; 
            color: var(--text-muted); 
        }

        .section { 
            border: 1px solid var(--border); 
            padding: 6px; 
            margin-bottom: 6px; 
        }

        .section-title { 
            font-weight: bold; 
            margin-bottom: 6px; 
            font-size: 12px; 
            color: var(--sena-green);
        }

        .grid { 
            width: 100%; 
            border-collapse: collapse;
        }

        .grid td {
            padding: 2px;
            vertical-align: top;
        }

        .row { 
            margin-bottom: 3px; 
        }

        .label { 
            display: inline-block; 
            width: 135px; 
            font-weight: bold; 
            color: var(--text-main); 
        }

        .value { 
            color: var(--text-main); 
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 4px; 
        }

        th, td { 
            border: 1px solid var(--border); 
            padding: 4px; 
            text-align: left; 
            font-size: 10px; 
        }

        th { 
            background: var(--sena-green-soft); 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 9.5px; 
        }

        .tag { 
            display: inline-block; 
            padding: 2px 6px; 
            border: 1px solid var(--border); 
            font-size: 9px; 
        }

        .ok { 
            background: #d1fae5; 
            border-color: #a7f3d0; 
            color: #065f46; 
        }

        .bad { 
            background: #fee2e2; 
            border-color: #fecaca; 
            color: #991b1b; 
        }

        .warn { 
            background: #fef3c7; 
            border-color: #fde68a; 
            color: #92400e; 
        }

        .footer { 
            margin-top: 8px; 
            text-align: center; 
            font-size: 9px; 
            color: var(--text-muted); 
        }
    </style>
</head>
<body>
    @php
        $ficha = $asistencia->instructorFicha;
        $programa = $ficha?->programaFormacion;
        $instructorPersona = $ficha?->instructor?->persona;
        $userCreate = $asistencia->userCreate;
        $userCreatePersona = $userCreate?->persona;
        $evidencia = $asistencia->evidencia;
        $total = $aprendicesTabla->count();
        $asistieron = $aprendicesTabla->where('asistio', true)->count();
        $noAsistieron = $total - $asistieron;
    @endphp

    <div class="header">
        <h1>REPORTE DE ASISTENCIA</h1>
        <div class="sub">
            Servicio Nacional de Aprendizaje – SENA<br>
            Centro de Desarrollo Agroindustrial, Turístico y Tecnológico del Guaviare
        </div>
    </div>

    <div class="section">
        <div class="section-title">Resumen</div>
        <table class="grid">
            <tr>
                <td><strong>Fecha:</strong> {{ $asistencia->fecha?->format('d/m/Y') }}</td>
                <td><strong>Inicio:</strong> {{ $asistencia->hora_inicio?->format('h:i A') ?? 'N/A' }}</td>
                <td><strong>Fin:</strong> {{ $asistencia->hora_fin?->format('h:i A') ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong></strong>Estado:</strong>
                    <span class="tag {{ $asistencia->is_finished ? 'ok' : 'warn' }}">
                        {{ $asistencia->is_finished ? 'Finalizada' : 'Activa' }}
                    </span>
                </td>
                <td><strong>Total Aprendices:</strong> {{ $total }}</td>
                <td><strong>Asistieron:</strong> {{ $asistieron }}</td>
            </tr>
            <tr>
                <td><strong>No asistieron:</strong> {{ $noAsistieron }}</td>
                <td colspan="2"></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Ficha / Programa</div>
        <div class="row"><span class="label">Ficha:</span> <span class="value">{{ $ficha?->ficha ?? 'N/A' }}</span></div>
        <div class="row"><span class="label">Programa de Formación:</span> <span class="value">{{ $programa?->nombre ?? 'N/A' }}</span></div>
        <div class="row"><span class="label">Instructor líder:</span>
            <span class="value">{{ $instructorPersona?->primer_nombre ?? '' }} {{ $instructorPersona?->primer_apellido ?? '' }} - {{ $instructorPersona?->numero_documento ?? 'N/A' }}</span>
        </div>
        <div class="row"><span class="label">Instructor de Formación:</span>
            <span class="value">{{ $userCreatePersona?->primer_nombre ?? '' }} {{ $userCreatePersona?->primer_apellido ?? '' }} - {{ $userCreatePersona?->numero_documento ?? 'N/A' }}</span>
        </div>
        <div class="row"><span class="label">Sede / Ambiente:</span>
            <span class="value">{{ $ficha?->sede?->sede ?? 'N/A' }} / {{ $ficha?->ambiente?->title ?? 'N/A' }}</span>
        </div>
    </div>

    @if($asistencia->observaciones)
    <div class="section">
        <div class="section-title">Observaciones de la Asistencia</div>
        <div>{{ $asistencia->observaciones }}</div>
    </div>
    @endif

    <div class="section">
        <strong>Evidencia:</strong>
        {{ $evidencia?->nombre ?? 'N/A' }}
    </div>

    <div class="section">
        <div class="section-title">Detalle de Aprendices</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 110px;">Documento</th>
                    <th>Aprendiz</th>
                    <th style="width: 80px;">Asistió</th>
                    <th style="width: 90px;">Ingreso</th>
                    <th style="width: 90px;">Salida</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($aprendicesTabla as $idx => $row)
                    @php
                        $aprendiz = $row['aprendiz'];
                        $registro = $row['registro'];
                        $asistio = $row['asistio'];
                        $p = $aprendiz?->persona;
                    @endphp
                    <tr>
                        <td>{{ $p?->numero_documento ?? 'N/A' }}</td>
                        <td>{{ $p?->primer_nombre ?? '' }} {{ $p?->primer_apellido ?? '' }}</td>
                        <td style="text-align:center">
                            <span class="tag {{ isset($asistio) && $asistio ? 'ok' : 'bad' }}">
                                {{ isset($asistio) && $asistio ? 'SI' : 'NO' }}
                            </span>
                        </td>
                        <td>{{ $registro?->hora_ingreso?->format('h:i A') ?? 'N/A' }}</td>
                        <td>{{ $registro?->hora_salida?->format('h:i A') ?? 'N/A' }}</td>
                        <td>{{ $registro?->observaciones ?? 'Sin observaciones' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        Documento generado automáticamente por el sistema de asistencia SENA. El SENA no se hace responsable por el uso indebido de este documento. ·
        {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
