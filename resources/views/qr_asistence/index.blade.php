@extends('adminlte::page')
@section('css')
    @vite(['resources/css/Asistencia/index_qr.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Asistencia QR</h1>
                    <p class="admin-header-subtitle">Registro de asistencia</p>
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
                        <a href="{{ route('asistence.web') }}">
                            <i class="fas fa-file-alt me-1"></i>Fichas
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-qrcode me-1"></i>Asistencia QR
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="vista-asistencia-qr">
        <div class="main-card">
            <section class="content-mt4 qr-page">
                <div class="container-fluid">
                    <x-session-alerts />
                    <div id="alert-container" class="mb-3"></div>
                    <div class="cards-grid qr-grid">
                <div class="card-modern">
                    <div class="card-header-gradient">
                        <div class="header-text">
                            <h3 class="card-title">
                                {{ $fichaCaracterizacion->programaFormacion->nombre ?? 'Programa no disponible' }}
                                <span class="ficha-codigo">Ficha {{ $fichaCaracterizacion->ficha }}</span>
                            </h3>
                        </div>
                        <div class="modalidad-badge">
                            {{ $fichaCaracterizacion->modalidadFormacion->name ?? 'Presencial' }}
                        </div>
                    </div>
                    <div class="card-body-modern">
                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-info-circle section-icon"></i>
                                Información del programa
                            </div>
                            <div class="info-item">
                                <i class="fa-solid fa-hashtag info-icon"></i>
                                <div>
                                    <div class="info-label">N° Ficha</div>
                                    <div class="info-value">{{ $fichaCaracterizacion->ficha }}</div>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-user-tie info-icon"></i>
                                <div>
                                    <div class="info-label">Instructor líder</div>
                                    <div class="info-value">
                                        @if ($fichaCaracterizacion->instructor && $fichaCaracterizacion->instructor->persona)
                                            {{ $fichaCaracterizacion->instructor->persona->getNombreCompletoAttribute() }}
                                        @else
                                            No asignado
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Comentado: Lógica para calcular número de actividad
                            @php
                                $numeroActividad = 1;
                            @endphp
                            @foreach ($actividades as $actividad)
                                @if ($actividad->id == $evidencia->id)
                                    @break
                                @endif
                                @php
                                    $numeroActividad++;
                                @endphp
                            @endforeach
                            --}}
                            {{-- Comentado: Sección de información de competencias y guías de aprendizaje
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="info-box bg-white shadow-sm rounded">
                                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center mr-3"
                                            style="width: 48px; height: 48px;">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                        <div class="info-box-content">
                                            <span class="info-box-text text-secondary">Evidencia de aprendizaje</span>
                                            <span class="info-box-number fw-bold">
                                                EV-{{ $numeroActividad }}:
                                                {{ $evidencia->nombre ?? 'Evidencia no disponible' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-white shadow-sm rounded">
                                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mr-3"
                                            style="width: 48px; height: 48px;">
                                            <i class="fas fa-check-circle text-white"></i>
                                        </div>
                                        <div class="info-box-content">
                                            <span class="info-box-text text-secondary">Guia de aprendizaje</span>
                                            <span class="info-box-number fw-bold">{{ $guiaAprendizajeActual->codigo }}:
                                                {{ $guiaAprendizajeActual->nombre }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box bg-white shadow-sm rounded">
                                        <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center mr-3"
                                            style="width: 48px; height: 48px;">
                                            <i class="fas fa-book text-white"></i>
                                        </div>
                                        <div class="info-box-content">
                                            <span class="info-box-text text-secondary">Resultado de aprendizaje</span>
                                            <span class="info-box-number fw-bold">
                                                {{ $rapActual->nombre }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            --}}
                        </div>

                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-file-alt section-icon"></i>
                                Evidencia de Aprendizaje
                            </div>
                            @if ($evidencia)
                                <div class="info-item">
                                    <i class="fa-solid fa-file-alt info-icon"></i>
                                    <div>
                                        <div class="info-label">Evidencia</div>
                                        <div class="info-value">{{ $evidencia->nombre }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fa-solid fa-calendar info-icon"></i>
                                    <div>
                                        <div class="info-label">Fecha</div>
                                        <div class="info-value">{{ $evidencia->created_at->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fa-solid fa-clock info-icon"></i>
                                    <div>
                                        <div class="info-label">Hora de creación</div>
                                        <div class="info-value">{{ $evidencia->created_at->format('h:i A') }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="info-item">
                                    <i class="fa-solid fa-exclamation-triangle info-icon text-warning"></i>
                                    <div>
                                        <div class="info-label">Estado</div>
                                        <div class="info-value text-warning">Sin evidencia asignada</div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-clock section-icon"></i>
                                Estado de la sesión
                            </div>
                            <div class="info-item">
                                <i class="fas fa-circle info-icon" style="color: var(--success) !important;"></i>
                                <div>
                                    <div class="info-label">Asistencia</div>
                                    <div class="info-value">
                                        @isset($asistencia)
                                            @if($asistencia->is_finished)
                                                Finalizada
                                            @else
                                                Activa
                                            @endif
                                        @else
                                            Sin sesión
                                        @endisset
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-actions">
                            <div class="aprendices-badge">
                                <i class="fas fa-users"></i>
                                <span class="badge-number">{{ $aprendizPersonaConAsistencia->count() ?? 0 }}</span>
                                <span>Aprendices</span>
                            </div>
                            @isset($asistencia)
                                @if(!$asistencia->is_finished)
                                    <button type="button" class="btn-primary-modern" onclick="console.log('Click en botón finalizar'); openFinalizarModal();">
                                        <i class="fas fa-check-circle"></i>
                                        Finalizar asistencia
                                    </button>
                                @else
                                    <button type="button" class="btn-primary-modern" disabled>
                                        <i class="fas fa-check"></i>
                                        Asistencia finalizada
                                    </button>
                                @endif
                            @endisset
                        </div>
                    </div>
                </div>

                <div class="card-modern" id="qr-scanner-card">
                    <div class="card-header-gradient">
                        <div class="header-text">
                            <h3 class="card-title">
                                Escanear QR
                                <span class="ficha-codigo">Registro en tiempo real</span>
                            </h3>
                        </div>
                        <div class="modalidad-badge">
                            <i class="fas fa-circle me-1" style="font-size: 0.75rem;"></i>
                            Activo
                        </div>
                    </div>

                    <div class="card-body-modern">
                        @if(isset($asistencia) && !$asistencia->is_finished)
                            <div class="qr-feedback-messages mb-4"></div>
                            <div class="d-flex justify-content-center mb-4">
                                <form id="asistencia-form" action="{{ route('asistence.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="caracterizacion_id" id="ficha_caracterizacion_id"
                                        value="{{ $fichaCaracterizacion->id }}">
                                    <input type="hidden" name="evidencia_id" id="evidencia_id"
                                        value="{{ $asistencia ? $asistencia->evidencia->id : '' }}">
                                </form>
                                <div class="qr-scanner-container" style="width: 100%; max-width: 350px;">
                                    <div id="qr-lector" style="width: 100%; max-width: 350px; position: relative;">
                                        <div class="qr-frame"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="qr-scanner-footer mt-3">
                                <div class="text-center text-secondary mb-3">
                                    <p class="mb-0">Posicione el código QR en el recuadro</p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning text-center mb-0">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Asistencia finalizada</strong><br>
                                <small>No se pueden registrar más ingresos o salidas.</small>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card-modern span-2" id="no-classes-card" style="display: none;">
                    <div class="card-header-gradient">
                        <div class="header-text">
                            <h3 class="card-title">
                                No hay clases programadas
                                <span class="ficha-codigo">El escáner se habilita con clases</span>
                            </h3>
                        </div>
                        <div class="modalidad-badge">Aviso</div>
                    </div>
                    <div class="card-body-modern text-center py-5">
                        <div class="text-warning mb-3">
                            <i class="fas fa-calendar-times fa-3x"></i>
                        </div>
                        <h6 class="text-muted">No hay clases programadas para hoy</h6>
                        <p class="text-muted mb-0">El escáner QR estará disponible cuando haya clases programadas.</p>
                    </div>
                </div>

                <div class="card-modern span-2">
                    <div class="card-header-gradient">
                        <div class="header-text">
                            <h3 class="card-title">
                                Listado de aprendices
                                <span class="ficha-codigo">Entradas y salidas por sesión</span>
                            </h3>
                        </div>
                        <div class="modalidad-badge">Tabla</div>
                    </div>

                    <div class="card-body-modern">
                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-keyboard section-icon"></i>
                                Registro manual
                            </div>
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" 
                                               id="manual-document-input" 
                                               class="form-control" 
                                               placeholder="Seleccione aprendices con checkbox o ingrese documento manualmente..."
                                               maxlength="20">
                                        <button class="ml-2 btn btn-primary" 
                                                id="manual-register-btn"
                                                type="button">
                                            <i class="fas fa-user-check mr-1"></i> Registrar Asistencia 
                                        </button>
                                    </div>
                                    <small class="text-muted mt-1 d-block">
                                        Seleccione aprendices con checkbox o use esta opción para registro manual por documento
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-borderless table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="px-4 py-3">
                                            <input type="checkbox" id="selectAllAprendices" class="form-check-input">
                                        </th>
                                        <th class="px-4 py-3">#</th>
                                        <th class="px-4 py-3">Documento</th>
                                        <th class="px-4 py-3">Nombre del aprendiz</th>
                                        <th class="px-4 py-3 text-center">Hora Ingreso</th>
                                        <th class="px-4 py-3 text-center">Hora Salida</th>
                                        <th class="px-4 py-3">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($aprendizPersonaConAsistencia as $index => $aprendiz)
                                        <tr data-documento="{{ $aprendiz->numero_documento }}">
                                            <td class="px-4">
                                                <input type="checkbox" 
                                                       class="form-check-input aprendiz-checkbox" 
                                                       data-aprendiz-id="{{ $aprendiz->aprendiz_id }}"
                                                       data-documento="{{ $aprendiz->numero_documento }}"
                                                       value="{{ $aprendiz->aprendiz_id }}">
                                            </td>
                                            <td class="px-4">{{ $index + 1 }}</td>
                                            <td class="px-4 font-weight-medium">{{ $aprendiz->numero_documento }}</td>
                                            <td class="px-4 font-weight-medium">
                                                {{ $aprendiz->getNombreCompletoAttribute() }} {{-- Usar el accesor --}}
                                            </td>
                                            <td class="px-4 text-center hora-ingreso-cell">
                                                @if ($aprendiz->asistenciaHoy && $aprendiz->asistenciaHoy->formatted_hora_ingreso)
                                                    <span class="text-success">{{ $aprendiz->asistenciaHoy->formatted_hora_ingreso }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 text-center hora-salida-cell">
                                                @if ($aprendiz->asistenciaHoy && $aprendiz->asistenciaHoy->formatted_hora_salida)
                                                    <span class="text-danger">{{ $aprendiz->asistenciaHoy->formatted_hora_salida }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4">
                                                <input type="text" 
                                                       class="form-control form-control-sm observaciones-aprendiz-final" 
                                                       data-aprendiz-id="{{ $aprendiz->aprendiz_id }}"
                                                       placeholder="Observaciones..."
                                                       value="{{ $aprendiz->asistenciaHoy->observaciones ?? '' }}"
                                                       maxlength="255">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <img src="{{ asset('img/no-data.svg') }}" alt="No data"
                                                    style="width: 120px" class="mb-3">
                                                <p class="text-muted">No hay aprendices registrados</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <!-- Modal de finalización de asistencia -->
    <div id="finalizarModal" class="modal" style="display: none;">
        <div class="modal-backdrop">
            <div class="modal-card">
                <h5 class="text-danger mb-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Finalizar asistencia
                </h5>

                <p class="mb-4">
                    ¿Está seguro que desea finalizar la asistencia?
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        No se podrán registrar más ingresos o salidas.
                    </small>
                </p>

                <div class="mb-4">
                    <label for="observaciones" class="form-label">
                        <i class="fas fa-comment-alt mr-1"></i>
                        Observaciones de la sesión
                    </label>
                    <textarea 
                        id="observaciones" 
                        name="observaciones" 
                        class="form-control" 
                        rows="3" 
                        placeholder="Ingrese cualquier observación relevante sobre esta sesión de asistencia..."
                    >@isset($asistencia){{ $asistencia->observaciones ?? '' }}@endif</textarea>
                    <small class="text-muted">
                        Estas observaciones se guardarán junto con el reporte de asistencia.
                    </small>
                </div>

                <div class="modal-actions d-flex justify-content-between gap-2">
                    <button type="button" class="btn btn-light flex-fill" onclick="closeFinalizarModal()">
                        <i class="fas fa-times mr-1"></i>
                        Cancelar
                    </button>

                    <button type="button" class="btn btn-danger flex-fill" onclick="confirmFinalizarAsistencia()">
                        <i class="fas fa-check-circle mr-1"></i>
                        Sí, finalizar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="{{ asset('js/websocket-handler.js') }}"></script>
    
    <!-- Funciones globales definidas antes que cualquier otro script -->
    <script>
        // Función para abrir modal de finalización
        function openFinalizarModal() {
            console.log('Abriendo modal de finalización...');
            console.log('asistenciaId:', window.asistenciaId);
            
            const modal = document.getElementById('finalizarModal');
            if (modal) {
                modal.style.display = 'flex';
                console.log('Modal encontrada y mostrada');
            } else {
                console.error('No se encontró la modal con id "finalizarModal"');
            }
        }
        
        // Función para cerrar modal de finalización
        function closeFinalizarModal() {
            document.getElementById('finalizarModal').style.display = 'none';
        }
        
        // Función para confirmar finalización de asistencia
        function confirmFinalizarAsistencia() {
            console.log('confirmFinalizarAsistencia() ejecutada');
            
            if (!window.asistenciaId) {
                alert('No hay una sesión de asistencia activa.');
                return;
            }
            
            // Obtener el botón que fue clickeado
            const btn = event.target || event.srcElement;
            const originalText = btn.innerHTML;
            
            // Obtener las observaciones del modal
            const observaciones = document.getElementById('observaciones').value;
            
            // Obtener observaciones de todos los aprendices
            const observacionesAprendices = {};
            document.querySelectorAll('.observaciones-aprendiz-final').forEach(function(input) {
                const aprendizId = input.dataset.aprendizId;
                const valor = input.value.trim();
                if (valor) {
                    observacionesAprendices[aprendizId] = valor;
                }
            });
            
            console.log('Botón encontrado:', btn);
            console.log('Iniciando finalización de asistencia ID:', window.asistenciaId);
            console.log('Observaciones generales:', observaciones);
            console.log('Observaciones aprendices:', observacionesAprendices);
            
            // Mostrar loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Finalizando...';
            
            // Hacer llamada AJAX para finalizar asistencia
            fetch('{{ route('asistence.finalizarAsistencia') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    ficha_id: window.fichaId,
                    caracterizacion_id: window.caracterizacionId,
                    asistencia_id: window.asistenciaId,
                    observaciones: observaciones,
                    observaciones_aprendices: observacionesAprendices
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta del servidor:', data);
                if (data.status === 'success') {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }
                    window.location.reload();
                } else {
                    alert('Error al finalizar la asistencia: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('Error al finalizar asistencia:', error);
                alert('Error de comunicación al finalizar la asistencia.');
            })
            .finally(() => {
                // Restaurar botón
                btn.disabled = false;
                btn.innerHTML = originalText;
                closeFinalizarModal();
            });
        }
    </script>
    
    <script>
        window.csrfToken = '{{ csrf_token() }}';
        window.apiVerifyDocumentRoute = '{{ route('api.verifyDocument') }}';
        window.horarioHoy = @json($horarioHoy);
        
        // Datos de la vista
        window.fichaId = {{ $fichaCaracterizacion->id }};
        window.caracterizacionId = '{{ $caracterizacion->id }}';
        @isset($asistencia) 
            window.asistenciaId = '{{ $asistencia->id }}'; 
        @else
            window.asistenciaId = null;
        @endif
        
        // Función para mostrar alertas usando sesiones de Laravel
        function showAlert(message, type = 'info') {
            // Usar las mismas claves que usa el componente session-alerts
            const sessionKeys = {
                'success': 'success',
                'error': 'error', 
                'warning': 'warning',
                'info': 'info'
            };
            
            // Guardar mensaje en sesión y recargar la página para que el componente lo muestre
            fetch('{{ route('asistencia.setSessionAlert') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify({
                    key: sessionKeys[type],
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Recargar para que el componente session-alerts muestre el mensaje
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error al guardar alerta en sesión:', error);
                // Fallback a alert() si hay error
                alert(message);
            });
        }
        
        // Función para mostrar alertas sin recargar (para registro de asistencia)
        function showAlertNoReload(message, type = 'info') {
            const alertContainer = document.getElementById('alert-container');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            `;
            
            alertContainer.appendChild(alertDiv);
            
            // Auto-eliminar después de 5 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }
        
        // Manejar selección de checkboxes
        document.addEventListener('DOMContentLoaded', function() {
            // Checkbox para seleccionar todos
            const selectAllCheckbox = document.getElementById('selectAllAprendices');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('.aprendiz-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });
            }
            
            // Botón para registrar asistencia de seleccionados
            const manualRegisterBtn = document.getElementById('manual-register-btn');
            if (manualRegisterBtn) {
                manualRegisterBtn.addEventListener('click', function() {
                    registrarAsistenciaSeleccionados();
                });
            }
            
            // También permitir registro por documento manual
            const manualDocumentInput = document.getElementById('manual-document-input');
            if (manualDocumentInput) {
                manualDocumentInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault(); // Prevenir comportamiento por defecto
                        registrarAsistenciaSeleccionados();
                    }
                });
            }
        });
        
        function registrarAsistenciaSeleccionados() {
            const selectedCheckboxes = document.querySelectorAll('.aprendiz-checkbox:checked');
            const manualDocument = document.getElementById('manual-document-input').value.trim();
            
            if (selectedCheckboxes.length === 0 && !manualDocument) {
                showAlertNoReload('Por favor, seleccione al menos un aprendiz o ingrese un documento.', 'warning');
                return;
            }
            
            // Evitar múltiples ejecuciones simultáneas
            const btn = document.getElementById('manual-register-btn');
            if (btn.disabled) {
                return; // Ya está procesando
            }
            
            const originalText = btn.innerHTML;
            
            // Mostrar loading y deshabilitar
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Registrando...';
            
            // Preparar datos
            const aprendicesSeleccionados = [];
            selectedCheckboxes.forEach(checkbox => {
                aprendicesSeleccionados.push({
                    aprendiz_id: checkbox.dataset.aprendizId,
                    documento: checkbox.dataset.documento
                });
            });
            
            const requestData = {
                ficha_id: window.fichaId,
                caracterizacion_id: window.caracterizacionId,
                asistencia_id: window.asistenciaId,
                aprendices_seleccionados: aprendicesSeleccionados
            };
            
            // Agregar documento manual si se ingresó
            if (manualDocument) {
                requestData.documento_manual = manualDocument;
            }
            
            console.log('Enviando datos:', requestData);
            
            // Enviar solicitud
            fetch('{{ route('asistence.registrarSeleccionados') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta:', data);
                if (data.status === 'success') {
                    // Limpiar selección y campo manual
                    document.querySelectorAll('.aprendiz-checkbox:checked').forEach(cb => cb.checked = false);
                    document.getElementById('selectAllAprendices').checked = false;
                    document.getElementById('manual-document-input').value = '';
                    
                    // Mostrar mensaje de éxito sin recargar
                    showAlertNoReload(data.message, 'success');
                    
                    // Actualizar solo las filas de los aprendices registrados
                    if (data.registros_exitosos > 0) {
                        actualizarFilasAprendices(data.aprendices_actualizados);
                    }
                } else {
                    showAlertNoReload('Error: ' + (data.message || 'Error al registrar asistencia'), 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlertNoReload('Error de comunicación al registrar asistencia.', 'danger');
            })
            .finally(() => {
                // Rehabilitar botón
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }
        
        // Función para actualizar filas de aprendices sin recargar la página
        function actualizarFilasAprendices(aprendicesActualizados) {
            if (!aprendicesActualizados || aprendicesActualizados.length === 0) {
                return;
            }
            
            aprendicesActualizados.forEach(aprendiz => {
                // Buscar la fila del aprendiz
                const fila = document.querySelector(`tr[data-documento="${aprendiz.documento}"]`);
                if (fila) {
                    // Actualizar celdas de hora
                    const horaIngresoCell = fila.querySelector('.hora-ingreso-cell');
                    const horaSalidaCell = fila.querySelector('.hora-salida-cell');
                    
                    if (aprendiz.hora_ingreso && horaIngresoCell) {
                        horaIngresoCell.innerHTML = `<span class="text-success">${aprendiz.hora_ingreso}</span>`;
                    }
                    
                    if (aprendiz.hora_salida && horaSalidaCell) {
                        horaSalidaCell.innerHTML = `<span class="text-danger">${aprendiz.hora_salida}</span>`;
                    }
                    
                    // Desmarcar checkbox
                    const checkbox = fila.querySelector('.aprendiz-checkbox');
                    if (checkbox) {
                        checkbox.checked = false;
                    }
                }
            });
        }
        
        // Manejar finalización de asistencia
        document.addEventListener('DOMContentLoaded', function() {
            const finalizarBtn = document.getElementById('finalizarAsistenciaBtn');
            
            if (finalizarBtn) {
                finalizarBtn.addEventListener('click', function() {
                    if (confirm('¿Está seguro de finalizar la asistencia? Esta acción generará un reporte PDF y bloqueará el registro hasta el día siguiente.')) {
                        finalizarAsistencia();
                    }
                });
            }
            
            function finalizarAsistencia() {
                const btn = document.getElementById('finalizarAsistenciaBtn');
                const originalText = btn.innerHTML;
                
                // Deshabilitar botón y mostrar loading
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Finalizando...';
                
                fetch('{{ route('asistence.finalizarAsistencia') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify({
                        ficha_id: window.fichaId,
                        caracterizacion_id: window.caracterizacionId,
                        asistencia_id: window.asistenciaId // Cambiado de evidencia_id a asistencia_id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        
                        // Descargar PDF
                        if (data.pdf_url) {
                            const link = document.createElement('a');
                            link.href = data.pdf_url;
                            link.download = `asistencia_${new Date().toISOString().split('T')[0]}.pdf`;
                            link.click();
                        }
                        
                        showAlert('Asistencia finalizada correctamente. Se generó un reporte PDF.', 'success');
                        
                        // Redireccionar después de un momento
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 1500);
                    } else {
                        showAlert('Error: ' + (data.message || 'Error al finalizar asistencia'), 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error al finalizar asistencia:', error);
                    showAlert('Error de comunicación al finalizar la asistencia.', 'danger');
                })
                .finally(() => {
                    // Restaurar botón
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            }
        });
    </script>
    
    <style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }
    
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-card {
        background: white;
        border-radius: 8px;
        padding: 24px;
        max-width: 400px;
        width: 90%;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        animation: slideUp 0.3s ease-out;
    }
    
    @keyframes slideUp {
        from { 
            opacity: 0;
            transform: translateY(20px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }
    </style>
    
    @vite(['resources/js/Asistencia/index-qr.js'])

@endsection
