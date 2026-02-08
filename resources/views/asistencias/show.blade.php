@extends('adminlte::page')

@section('title', 'Detalle de Asistencia')

@section('css')
    @vite(['resources/css/fichas.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Detalle de Asistencia</h1>
                    <p class="admin-header-subtitle">Información completa de la asistencia y aprendices registrados</p>
                </div>
            </div>
            <nav aria-label="breadcrumb" class="admin-breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('verificarLogin') }}">
                            <i class="fas fa-home me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('asistencia.consulta') }}">
                            <i class="fas fa-clipboard-check me-1"></i>Consultar Asistencias
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-eye me-1"></i>Detalle
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    @php
        $ficha = $asistencia->instructorFicha;
        $programa = $ficha?->programaFormacion;
        $instructor = $ficha?->instructor;
        $instructorPersona = $instructor?->persona;
        $evidencia = $asistencia->evidencia;
    @endphp

    <div class="vista-fichas">
        <div class="main-card">
            <div style="padding: 1.5rem;">
                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 16px;">
                    <a href="{{ route('asistencia.consulta.pdf', $asistencia->id) }}" class="btn-primary-modern">
                        <i class="fas fa-file-pdf" style="margin-right: 6px;"></i>
                        Descargar PDF
                    </a>
                </div>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;">

                <div class="modal-section" style="margin-bottom: 16px;">
                    <h6 class="section-title">Resumen</h6>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Fecha</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $asistencia->fecha?->format('d/m/Y') ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Estado</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                <span class="badge-modern {{ $asistencia->is_finished ? 'badge-success' : 'badge-warning' }}">
                                    {{ $asistencia->is_finished ? 'Finalizada' : 'Activa' }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Duración</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $asistencia->duracion }}</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Inicio</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $asistencia->hora_inicio?->format('h:i A') ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Fin</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $asistencia->hora_fin?->format('h:i A') ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Total Aprendices</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                <span class="badge-modern badge-primary">{{ $asistencia->asistencia_aprendices_count ?? 0 }}</span>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Observaciones</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                {{ $asistencia->observaciones ?? 'Sin observaciones' }}
                            </div>
                        </div>
                    </div>
                </div>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;">

                <div class="modal-section" style="margin-bottom: 16px;">
                    <h6 class="section-title">Ficha / Programa</h6>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Ficha</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                <span class="badge-modern badge-info">{{ $ficha?->ficha ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Programa</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $programa?->nombre ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Instructor Líder</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                @if ($instructorPersona)
                                    {{ $instructorPersona->primer_nombre }} {{ $instructorPersona->primer_apellido }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Sede / Ambiente</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">
                                {{ $ficha?->sede?->sede ?? 'N/A' }} / {{ $ficha?->ambiente?->title ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;">

                <div class="modal-section" style="margin-bottom: 16px;">
                    <h6 class="section-title" style="font-size: 16px;">Evidencia</h6>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                        <div>
                            <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; margin-bottom: 4px;">Nombre</div>
                            <div style="font-size: 14px; color: #1f2937; font-weight: 500;">{{ $evidencia?->nombre ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid #e5e7eb;">

                <div class="table-scroll-wrapper" style="margin-top: 16px;">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Aprendiz</th>
                                <th>Documento</th>
                                <th>Asistencia</th>
                                <th>Hora Ingreso</th>
                                <th>Hora Salida</th>
                                <th>Tiempo</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($aprendicesTabla as $idx => $row)
                                @php
                                    $aprendiz = $row['aprendiz'];
                                    $registro = $row['registro'];
                                    $asistio = $row['asistio'];
                                    $p = $aprendiz?->persona;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge-modern badge-primary">{{ $idx + 1 }}</span>
                                    </td>
                                    <td>
                                        {{ $p?->primer_nombre ?? '' }} {{ $p?->primer_apellido ?? '' }}
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $p?->numero_documento ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge-modern {{ $asistio ? 'badge-success' : 'badge-danger' }}">
                                            {{ $asistio ? 'Asistió' : 'No asistió' }}
                                        </span>
                                    </td>
                                    <td>{{ $registro?->hora_ingreso?->format('h:i A') ?? 'N/A' }}</td>
                                    <td>{{ $registro?->hora_salida?->format('h:i A') ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge-modern {{ ($registro && $registro->hora_salida) ? 'badge-success' : 'badge-warning' }}">
                                            {{ $registro?->tiempo_dentro ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>{{ $registro?->observaciones ?? 'Sin observaciones' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <div class="empty-state">
                                            <i class="fas fa-folder-open"></i>
                                            <h3>No hay aprendices registrados</h3>
                                            <p>No se encontraron registros en esta asistencia.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection
