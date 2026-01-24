@extends('adminlte::page')

{{-- Activar plugins de AdminLTE --}}
@section('plugins.Datatables', true)
{{-- SweetAlert2 activado globalmente en config/adminlte.php --}}
@section('plugins.Select2', true)

@section('title', 'Crear Programa Complementario')

@section('css')
    @vite(['resources/css/parametros.css'])
    <style>
        .helper-text {
            font-size: .8rem;
            color: #6c757d;
        }

        .character-count {
            font-size: .75rem;
            color: #6c757d;
        }

        .nav-form-steps .nav-link {
            border-radius: .5rem;
            border: 1px solid transparent;
        }

        .nav-form-steps .nav-link.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .sidebar-guidance {
            position: sticky;
            top: 5.5rem;
        }
    </style>
@endsection

@section('content_header')
    <x-page-header icon="fa-graduation-cap" title="Programa Complementario"
        subtitle="Crear nuevo programa de formación complementaria" :breadcrumb="[
            [
                'label' => 'Gestión Programas',
                'url' => route('complementarios-ofertados.index'),
                'icon' => 'fa-graduation-cap',
            ],
            ['label' => 'Crear programa', 'icon' => 'fa-plus-circle', 'active' => true],
        ]" />
@endsection

@section('content')
    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-9">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                        <a href="{{ route('complementarios-ofertados.index') }}"
                            class="btn btn-outline-secondary btn-sm mb-2 mb-md-0">
                            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                        </a>
                        <div class="text-muted small">
                            <span class="badge badge-primary mr-1">Nuevo</span>
                            Completa los tres pasos para publicar el programa en la oferta complementaria.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('complementarios-ofertados.store') }}" id="programaForm"
                        class="card card-outline card-primary shadow-sm">
                        @csrf
                        <div class="card-header bg-white border-0 pb-2">
                            <h5 class="card-title mb-1">
                                <i class="fas fa-plus-circle mr-2"></i>Creación de programa complementario
                            </h5>
                            <p class="text-muted mb-0">Utiliza la navegación por pestañas para completar la información
                                del programa.</p>
                        </div>

                        <div class="card-body">
                            <ul class="nav nav-pills nav-form-steps flex-column flex-md-row mb-4" id="formTabs">
                                <li class="nav-item flex-md-fill mr-md-2 mb-2 mb-md-0">
                                    <a class="nav-link active d-flex align-items-center justify-content-center"
                                        id="tab-general-tab" data-toggle="tab" href="#tab-general" role="tab"
                                        aria-controls="tab-general" aria-selected="true">
                                        <i class="fas fa-layer-group mr-2"></i> Información general
                                    </a>
                                </li>
                                <li class="nav-item flex-md-fill mr-md-2 mb-2 mb-md-0">
                                    <a class="nav-link d-flex align-items-center justify-content-center" id="tab-config-tab"
                                        data-toggle="tab" href="#tab-config" role="tab"
                                        aria-controls="tab-config"
                                        aria-selected="false">
                                        <i class="fas fa-sliders-h mr-2"></i> Configuración académica
                                    </a>
                                </li>
                                <li class="nav-item flex-md-fill">
                                    <a class="nav-link d-flex align-items-center justify-content-center" id="tab-estado-tab"
                                        data-toggle="tab" href="#tab-estado" role="tab"
                                        aria-controls="tab-estado"
                                        aria-selected="false">
                                        <i class="fas fa-traffic-light mr-2"></i> Estado operativo
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content" id="formTabsContent">
                                <div class="tab-pane fade show active" id="tab-general" role="tabpanel"
                                    aria-labelledby="tab-general-tab">
                                    <div class="card card-outline card-success mb-4">
                                        <div class="card-header py-2">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-file-excel mr-2"></i>Seleccionar desde catálogo SENA
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="catalogo_id" class="form-label font-weight-semibold">
                                                    Programa en catálogo (CURSO ESPECIAL)
                                                </label>
                                                <select name="catalogo_id" id="catalogo_id"
                                                    class="form-control @error('catalogo_id') is-invalid @enderror">
                                                    <option value="">-- Seleccionar desde catálogo --</option>
                                                    @foreach ($catalogoProgramas ?? [] as $catalogo)
                                                        <option value="{{ $catalogo->id }}"
                                                            data-codigo="{{ $catalogo->prf_codigo }}"
                                                            data-nombre="{{ $catalogo->denominacion }}"
                                                            data-duracion="{{ $catalogo->duracion_horas }}"
                                                            data-requisitos="{{ e($catalogo->requisitos_ingreso) }}"
                                                            @selected(old('catalogo_id') == $catalogo->id)>
                                                            {{ $catalogo->prf_codigo }} - {{ $catalogo->denominacion }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <small class="helper-text">
                                                    Al seleccionar un programa del catálogo se rellenarán
                                                    automáticamente el código, el nombre, los requisitos (si están
                                                    disponibles) y la duración en horas.
                                                </small>
                                                @error('catalogo_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-7">
                                            <label for="nombre" class="form-label font-weight-semibold">Nombre del
                                                programa<span class="text-danger"> *</span></label>
                                            <input type="text" name="nombre" id="nombre"
                                                class="form-control @error('nombre') is-invalid @enderror"
                                                value="{{ old('nombre') }}"
                                                placeholder="Ej. Curso de fortalecimiento en matemáticas" required>
                                            <small class="helper-text">Utiliza un nombre descriptivo y fácil de reconocer
                                                por los aspirantes.</small>
                                            @error('nombre')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-5">
                                            <label for="codigo" class="form-label font-weight-semibold">Código
                                                interno<span class="text-danger"> *</span></label>
                                            <input type="text" name="codigo" id="codigo"
                                                class="form-control @error('codigo') is-invalid @enderror"
                                                value="{{ old('codigo') }}" placeholder="Ej. CMP-2025-001" required>
                                            <small class="helper-text">Respeta la convención usada por planeación
                                                académica.</small>
                                            @error('codigo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label for="justificacion" class="form-label font-weight-semibold mb-0">
                                                Descripción<span class="text-danger"> *</span>
                                            </label>
                                            <span id="justificacionCounter" class="character-count">0/600</span>
                                        </div>
                                        <textarea name="justificacion" id="justificacion" rows="4"
                                            class="form-control @error('justificacion') is-invalid @enderror"
                                            placeholder="Descripción del programa complementario" maxlength="600" required>{{ old('justificacion') }}</textarea>
                                        <small class="helper-text">Máximo 600 caracteres. Describe por qué se crea este programa.</small>
                                        @error('justificacion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label for="requisitos_ingreso" class="form-label font-weight-semibold mb-0">
                                                Requisitos de Ingreso<span class="text-danger"> *</span>
                                            </label>
                                            <span id="requisitosCounter" class="character-count">0/400</span>
                                        </div>
                                        <textarea name="requisitos_ingreso" id="requisitos_ingreso" rows="3"
                                            class="form-control @error('requisitos_ingreso') is-invalid @enderror"
                                            placeholder="Especifica los criterios de admisión y perfil requerido" maxlength="400" required>{{ old('requisitos_ingreso') }}</textarea>
                                        <small class="helper-text">Máximo 400 caracteres. Define quiénes pueden participar.</small>
                                        @error('requisitos_ingreso')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="tab-config" role="tabpanel"
                                    aria-labelledby="tab-config-tab">
                                    <p class="text-muted">Define la logística académica del programa.</p>

                                    <!-- Sección de Estructura Académica -->
                                    <div class="card card-outline card-info mb-4">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-graduation-cap mr-2"></i>Estructura Académica
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <!-- Selector de Competencias -->
                                            <div class="form-group">
                                                <label for="competencias" class="form-label font-weight-semibold">
                                                    Competencias del Programa
                                                </label>
                                                <select class="form-control select2-multiple" id="competencias" name="competencias[]" multiple>
                                                    @foreach($competencias ?? [] as $competencia)
                                                        <option value="{{ $competencia->id }}">{{ $competencia->codigo }} - {{ $competencia->nombre }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="helper-text">Selecciona las competencias que conforman este programa.</small>
                                            </div>

                                            <!-- Visualización de RAPs por Competencia (Opcional) -->
                                            <div id="raps-container" class="mt-3" style="display: none;">
                                                <h6>Resultados de Aprendizaje Asociados <small class="text-muted">(Opcional)</small></h6>
                                                <div id="raps-list" class="border rounded p-3 bg-light">
                                                    <!-- Se llena dinámicamente con JavaScript -->
                                                </div>
                                                <small class="helper-text">Los RAPs se muestran automáticamente según las competencias seleccionadas. Su selección es opcional.</small>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-sm-6">
                                            <label for="duracion" class="form-label font-weight-semibold">
                                                Duración (horas)<span class="text-danger"> *</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" name="duracion" id="duracion"
                                                    class="form-control @error('duracion') is-invalid @enderror"
                                                    value="{{ old('duracion') }}" min="1" max="1000"
                                                    step="1" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">h</span>
                                                </div>
                                            </div>
                                            <small class="helper-text">Tiempo total planeado para completar el
                                                contenido.</small>
                                            @error('duracion')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label for="cupos" class="form-label font-weight-semibold">
                                                Cupos ofertados<span class="text-danger"> *</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" name="cupos" id="cupos"
                                                    class="form-control @error('cupos') is-invalid @enderror"
                                                    value="{{ old('cupos') }}" min="1" max="1000"
                                                    step="1" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-user-friends"></i></span>
                                                </div>
                                            </div>
                                            <small class="helper-text">Número máximo de participantes por cohorte.</small>
                                            @error('cupos')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label for="jornada_id" class="form-label font-weight-semibold">
                                                Jornada<span class="text-danger"> *</span>
                                            </label>
                                            @php
                                                $jornadaSelectClass = 'form-control select2';
                                                if ($errors->has('jornada_id')) {
                                                    $jornadaSelectClass .= ' is-invalid';
                                                }
                                            @endphp
                                            <select name="jornada_id" id="jornada_id" class="{{ $jornadaSelectClass }}"
                                                required>
                                                <option value="">Seleccione una jornada</option>
                                                @foreach ($jornadas as $jornada)
                                                    <option value="{{ $jornada->id }}"
                                                        {{ old('jornada_id') == $jornada->id ? 'selected' : '' }}>
                                                        {{ $jornada->jornada }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="helper-text">Franja horaria en la que se ofertará.</small>
                                            @error('jornada_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="ambiente_id" class="form-label font-weight-semibold">
                                                Ambiente<span class="text-danger"> *</span>
                                            </label>
                                            @php
                                                $ambienteSelectClass = 'form-control select2';
                                                if ($errors->has('ambiente_id')) {
                                                    $ambienteSelectClass .= ' is-invalid';
                                                }
                                                $ambienteSeleccionadoId = old('ambiente_id');
                                            @endphp
                                            <select name="ambiente_id" id="ambiente_id"
                                                class="{{ $ambienteSelectClass }}"
                                                data-placeholder="Seleccione una opción" required>
                                                <option value="">Seleccione un ambiente</option>
                                                @isset($ambientes)
                                                    @php
                                                        $ambientesGrouped = $ambientes->groupBy('piso_id');
                                                    @endphp
                                                    @foreach ($ambientesGrouped as $pisoId => $grupo)
                                                        @php
                                                            $primerPiso = optional($grupo->first()->piso)->piso;
                                                            $label = $primerPiso ?? "Piso {$pisoId}";
                                                        @endphp
                                                        <optgroup label="{{ $label }}">
                                                            @foreach ($grupo as $ambiente)
                                                                @php
                                                                    $esAmbienteSeleccionado =
                                                                        $ambienteSeleccionadoId == $ambiente->id;
                                                                @endphp
                                                                <option value="{{ $ambiente->id }}"
                                                                    @if ($esAmbienteSeleccionado) selected @endif>
                                                                    {{ $ambiente->title }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                @else
                                                    <optgroup label="Ambientes">
                                                        <option value="" disabled>No hay ambientes disponibles</option>
                                                    </optgroup>
                                                @endisset
                                            </select>
                                            <small class="helper-text">Ambiente físico o virtual asociado al
                                                programa.</small>
                                            @error('ambiente_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="ambiente_comentario" class="form-label font-weight-semibold">
                                            Comentario sobre el ambiente
                                        </label>
                                        <textarea name="ambiente_comentario" id="ambiente_comentario" rows="2"
                                            class="form-control @error('ambiente_comentario') is-invalid @enderror"
                                            placeholder="Ej. Casa de la mujer, Alcaldía, Centro comunitario, etc."
                                            maxlength="500">{{ old('ambiente_comentario') }}</textarea>
                                        <small class="helper-text">Opcional. Especifica detalles adicionales sobre la ubicación o lugar específico donde se ejecutará el programa (máximo 500 caracteres).</small>
                                        @error('ambiente_comentario')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Sección de Días y Horarios de Formación -->
                                    <div class="card card-outline card-warning mb-4">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-calendar-alt mr-2"></i>Días y Horarios de Formación
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted small mb-3">
                                                Selecciona los días de la semana y asigna un horario individual para cada día.
                                                Si un día no tiene formación, deja la opción "Sin formación" seleccionada.
                                            </p>
                                            
                                            <div id="dias-horarios-container">
                                                <!-- Se llenará dinámicamente con JavaScript -->
                                            </div>
                                            
                                            <!-- Campo oculto para enviar datos al servidor -->
                                            <input type="hidden" name="dias_json" id="dias_json" value="">
                                            
                                            @error('dias')
                                                <div class="alert alert-danger mt-2">
                                                    <small>{{ $message }}</small>
                                                </div>
                                            @enderror
                                            @if($errors->has('dias.*'))
                                                <div class="alert alert-danger mt-2">
                                                    <small>
                                                        <ul class="mb-0">
                                                            @foreach($errors->get('dias.*') as $error)
                                                                <li>{{ $error[0] ?? $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </small>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="tab-estado"
                                    aria-labelledby="tab-estado-tab">
                                    <p class="text-muted">Selecciona el estado del programa en la planeación actual.</p>
                                    <div class="form-group mb-4">
                                        <label for="estado" class="form-label font-weight-semibold">Estado del
                                            programa<span class="text-danger"> *</span></label>
                                        <select name="estado" id="estado"
                                            class="form-control @error('estado') is-invalid @enderror" required>
                                            <option value="0" {{ old('estado') == '0' ? 'selected' : '' }}>Sin Oferta
                                            </option>
                                            <option value="1" {{ old('estado') == '1' ? 'selected' : '' }}>Con Oferta
                                            </option>
                                            <option value="2" {{ old('estado') == '2' ? 'selected' : '' }}>Cupos
                                                Llenos</option>
                                        </select>
                                        <small class="helper-text">Puedes actualizar este estado más adelante desde la
                                            gestión de programas.</small>
                                        @error('estado')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="alert alert-light border text-muted">
                                        <h6 class="mb-2"><i class="fas fa-info-circle mr-2"></i>Consejo</h6>
                                        <p class="mb-1 small">Utiliza <strong>Sin oferta</strong> mientras asignas
                                            horarios,
                                            ambientes o gestores.</p>
                                        <p class="mb-0 small">Marca <strong>Con oferta</strong> solo cuando esté listo para
                                            la
                                            inscripción en la vista pública.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-end">
                            <a href="{{ route('complementarios-ofertados.index') }}" class="btn btn-light mr-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary" id="saveBtn">
                                <i class="fas fa-save mr-1"></i> Guardar programa
                            </button>
                        </div>
                    </form>
                </div>

                <div class="col-12 col-lg-3">
                    <div class="sidebar-guidance">
                        <div class="card card-outline card-info shadow-sm mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-lightbulb mr-2"></i> Recomendaciones</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0 small text-muted">
                                    <li class="mb-2">
                                        <i class="fas fa-check-circle text-success mr-1"></i>
                                        Verifica que el código no se repita en programas activos.
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-calendar-alt text-primary mr-1"></i>
                                        Confirma la disponibilidad del ambiente en la agenda de planeación.
                                    </li>
                                    <li>
                                        <i class="fas fa-bullhorn text-warning mr-1"></i>
                                        Describe beneficios y requisitos para aumentar la conversión.
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="card card-outline card-secondary shadow-sm">
                            <div class="card-header">
                                <h5 class="card-title mb-0"><i class="fas fa-clipboard-list mr-2"></i> Estados</h5>
                            </div>
                            <div class="card-body small text-muted">
                                <p class="mb-2">
                                    <span class="badge badge-secondary mr-1">Sin oferta</span>
                                    Preparación interna, no visible al público.
                                </p>
                                <p class="mb-2">
                                    <span class="badge badge-success mr-1">Con oferta</span>
                                    Disponible para inscripciones en la vista pública.
                                </p>
                                <p class="mb-0">
                                    <span class="badge badge-warning mr-1">Cupos llenos</span>
                                    Capacidad completa, no admite más inscripciones.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    @vite(['resources/js/complementarios/dias-horarios.js'])
    <script type="application/json" id="dias-semana-data">
        @json(
            collect($diasSemana ?? [])->map(fn ($d) => [
                'id' => (int) $d->id,
                'nombre' => (string) ($d->parametro->name ?? ''),
            ])->values()
        )
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lógica de selección desde catálogo SENA
            const catalogoSelect = document.getElementById('catalogo_id');
            const codigoInput = document.getElementById('codigo');
            const nombreInput = document.getElementById('nombre');
            const duracionInput = document.getElementById('duracion');
            const requisitosInput = document.getElementById('requisitos_ingreso');

            if (catalogoSelect) {
                const bloquearCamposDesdeCatalogo = (usarCatalogo) => {
                    const campos = [codigoInput, nombreInput, duracionInput, requisitosInput];

                    campos.forEach((campo) => {
                        if (!campo) {
                            return;
                        }

                        if (usarCatalogo) {
                            campo.setAttribute('readonly', 'readonly');
                            campo.removeAttribute('required');
                        } else {
                            campo.removeAttribute('readonly');
                        }
                    });

                    if (codigoInput && !usarCatalogo) {
                        codigoInput.setAttribute('required', 'required');
                    }
                    if (nombreInput && !usarCatalogo) {
                        nombreInput.setAttribute('required', 'required');
                    }
                    if (duracionInput && !usarCatalogo) {
                        duracionInput.setAttribute('required', 'required');
                    }
                    if (requisitosInput && !usarCatalogo) {
                        requisitosInput.setAttribute('required', 'required');
                    }
                };

                const aplicarSeleccionCatalogo = () => {
                    const option = catalogoSelect.options[catalogoSelect.selectedIndex];

                    const tieneCatalogo = !!option && !!option.value;

                    if (!tieneCatalogo) {
                        bloquearCamposDesdeCatalogo(false);
                        return;
                    }

                    const codigo = option.dataset.codigo || '';
                    const nombre = option.dataset.nombre || '';
                    const duracion = option.dataset.duracion || '';
                    const requisitos = option.dataset.requisitos || '';

                    if (codigoInput && codigo) {
                        codigoInput.value = codigo;
                    }

                    if (nombreInput && nombre) {
                        nombreInput.value = nombre;
                    }

                    if (duracionInput && duracion) {
                        duracionInput.value = duracion;
                    }

                    if (requisitosInput && requisitos) {
                        requisitosInput.value = requisitos;
                    }

                    bloquearCamposDesdeCatalogo(true);
                };

                catalogoSelect.addEventListener('change', aplicarSeleccionCatalogo);
                aplicarSeleccionCatalogo();
            }
            if (typeof $ !== 'undefined' && $.fn.select2) {
                // Configurar Select2 para campos existentes
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    language: 'es',
                    allowClear: false
                }).on('change', function() {
                    if ($(this).val()) {
                        $(this).removeClass('is-invalid');
                    }
                });

                // Configurar Select2 múltiple para competencias
                $('.select2-multiple').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    language: 'es',
                    placeholder: 'Selecciona una o más opciones',
                    allowClear: true
                });
            }

            const form = document.getElementById('programaForm');
            const saveBtn = document.getElementById('saveBtn');
            const justificacion = document.getElementById('justificacion');
            const justificacionCounter = document.getElementById('justificacionCounter');
            const requisitosIngreso = document.getElementById('requisitos_ingreso');
            const requisitosCounter = document.getElementById('requisitosCounter');

            // Contadores de caracteres para los nuevos campos
            const updateJustificacionCounter = () => {
                justificacionCounter.textContent = `${justificacion.value.length}/600`;
            };

            const updateRequisitosCounter = () => {
                requisitosCounter.textContent = `${requisitosIngreso.value.length}/400`;
            };

            updateJustificacionCounter();
            updateRequisitosCounter();

            justificacion.addEventListener('input', updateJustificacionCounter);
            requisitosIngreso.addEventListener('input', updateRequisitosCounter);

            // Gestión de competencias y RAPs
            const competenciasSelect = document.getElementById('competencias');
            const rapsContainer = document.getElementById('raps-container');
            const rapsList = document.getElementById('raps-list');

            if (competenciasSelect) {
                competenciasSelect.addEventListener('change', function() {
                    const competenciasIds = $(this).val();
                    if (competenciasIds && competenciasIds.length > 0) {
                        cargarRAPsPorCompetencias(competenciasIds);
                        rapsContainer.style.display = 'block';
                    } else {
                        rapsContainer.style.display = 'none';
                        rapsList.innerHTML = '';
                    }
                });
            }

            // Función para cargar RAPs por competencias
            function cargarRAPsPorCompetencias(competenciasIds) {
                fetch('/api/complementarios/raps?competencias=' + competenciasIds.join(','))
                    .then(response => response.json())
                    .then(raps => {
                        mostrarRAPsJerarquizados(raps);
                    })
                    .catch(error => {
                        console.error('Error al cargar RAPs:', error);
                        rapsList.innerHTML = '<div class="text-danger">Error al cargar los resultados de aprendizaje</div>';
                    });
            }

            // Función para mostrar RAPs jerarquizados
            function mostrarRAPsJerarquizados(raps) {
                if (!raps || raps.length === 0) {
                    rapsList.innerHTML = '<div class="text-muted">No se encontraron resultados de aprendizaje para las competencias seleccionadas.</div>';
                    return;
                }

                let html = '';
                raps.forEach(rap => {
                    html += `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="raps[]" value="${rap.id}" id="rap-${rap.id}">
                            <label class="form-check-label" for="rap-${rap.id}">
                                <strong>${rap.codigo}</strong> - ${rap.nombre}
                                <small class="text-muted d-block">${rap.competencia_nombre}</small>
                            </label>
                        </div>
                    `;
                });
                rapsList.innerHTML = html;
            }

            // Navegación entre pestañas
            $('#formTabs a[data-toggle="tab"]').on('shown.bs.tab', function(event) {
                event.target.classList.add('active');
            });

            const activarTabDeCampo = (campo) => {
                const pane = campo.closest('.tab-pane');
                if (pane && !pane.classList.contains('active')) {
                    const selector = `#formTabs a[href="#${pane.id}"]`;
                    $(selector).tab('show');
                }
            };

            // Inicializar gestor de días y horarios
            let diasHorariosManager = null;
            if (typeof DiasHorariosManager !== 'undefined') {
                const diasSemana = JSON.parse(document.getElementById('dias-semana-data')?.textContent || '[]');
                diasHorariosManager = new DiasHorariosManager('dias-horarios-container', [], diasSemana);
            }

            // Validación ligera del formulario: delegar en validación HTML5/Laravel
            form.addEventListener('submit', function (e) {
                // Preparar datos de días y horarios antes de enviar
                if (diasHorariosManager) {
                    diasHorariosManager.prepararEnvioFormulario(form);
                }
                
                // Si el navegador detecta algún campo inválido, no llegará aquí.
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...';
                }
            });
        });
    </script>
@endsection
