@extends('adminlte::page')

@section('plugins.Datatables', true)
{{-- SweetAlert2 activado globalmente en config/adminlte.php --}}

@section('title', 'Programas complementarios')

@section('css')
    @vite(['resources/css/parametros.css'])
    <style>
        .programas-table-responsive {
            overflow-x: auto;
        }

        @media (min-width: 1200px) {
            .programas-table-responsive {
                overflow-x: visible;
            }
        }
    </style>
@endsection

@php
    $totalProgramas = $programas->count();
    $programasConOferta = $programas->where('estado', 1)->count();
    $programasSinOferta = $programas->where('estado', 0)->count();
    $programasCuposLlenos = $programas->where('estado', 2)->count();
@endphp

@section('content_header')
    <x-page-header icon="fa-graduation-cap" title="Programas complementarios"
        subtitle="Administra la oferta complementaria disponible" :breadcrumb="[
            ['label' => 'Inicio', 'url' => route('verificarLogin'), 'icon' => 'fa-home'],
            ['label' => 'Complementarios', 'icon' => 'fa-graduation-cap', 'active' => true],
        ]" />
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-xxl-10 mx-auto">
                    @php
                        $cardHeaderClasses = implode(' ', [
                            'card-header',
                            'd-flex',
                            'flex-column',
                            'flex-md-row',
                            'align-items-md-center',
                            'justify-content-md-between',
                            'gap-2',
                        ]);
                    @endphp
                    <div class="card card-outline card-success shadow-sm mb-3">
                        <div class="{{ $cardHeaderClasses }}">
                            <div>
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-layer-group mr-2"></i>Listado de programas
                                </h5>
                                <span class="text-muted small">
                                    Visualiza y gestiona tu oferta complementaria.
                                </span>
                            </div>
                            <div class="d-flex flex-wrap mt-2 mt-md-0">
                                <a href="{{ route('complementarios-ofertados.catalogo.import.create') }}"
                                    class="btn btn-outline-success mr-2 mb-2">
                                    <i class="fas fa-file-excel mr-1"></i> Importar catálogo SENA
                                </a>
                                <a href="{{ route('complementarios-ofertados.create') }}"
                                    class="btn btn-success mr-2 mb-2">
                                    <i class="fas fa-plus-circle mr-1"></i> Crear programa
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <x-data-table title="Programas" :paginated="false" :searchable="true" tableId="programas-table"
                                tableClass="table table-sm table-striped table-center mb-0"
                                tableWrapperClass="table-responsive programas-table-responsive rounded-bottom"
                                :columns="[
                                    ['label' => 'Código', 'width' => '12%'],
                                    ['label' => 'Programa', 'width' => '28%'],
                                    ['label' => 'Modalidad', 'width' => '14%'],
                                    ['label' => 'Jornada', 'width' => '14%'],
                                    ['label' => 'Estado', 'width' => '12%'],
                                    ['label' => 'Cupos', 'width' => '8%'],
                                    ['label' => 'Opciones', 'width' => '12%', 'class' => 'text-center'],
                                ]">
                                @forelse ($programas as $programa)
                                    @php
                                        $busqueda = Str::of(
                                            $programa->nombre . ' ' . $programa->codigo . ' ' . ($programa->justificacion ?? ''),
                                        )->lower();
                                        $modalidadSlug = Str::slug($programa->modalidad->parametro->name ?? '');
                                        $jornadaSlug = Str::slug($programa->jornada->jornada ?? '');
                                    @endphp
                                    <tr data-search="{{ $busqueda }}" data-modalidad="{{ $modalidadSlug }}"
                                        data-jornada="{{ $jornadaSlug }}" data-estado="{{ $programa->estado }}">
                                        <td class="align-middle text-primary font-weight-bold">
                                            {{ $programa->codigo }}
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-semibold text-truncate" style="max-width: 320px;">
                                                {{ $programa->nombre }}
                                            </div>
                                            <small class="text-muted d-block">
                                                {{ Str::limit($programa->justificacion ?? 'Sin justificación', 90) }}
                                            </small>
                                        </td>
                                        <td class="align-middle">
                                            {{ $programa->modalidad->parametro->name ?? 'N/A' }}
                                        </td>
                                        <td class="align-middle">
                                            {{ $programa->jornada->jornada ?? 'N/A' }}
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge badge-pill {{ $programa->badge_class }}">
                                                {{ $programa->estado_label }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-primary font-weight-semibold">
                                            {{ $programa->cupos }}
                                        </td>
                                        <td class="align-middle text-center">
                                            <x-action-buttons
                                                :show="true"
                                                :edit="true"
                                                :delete="true"
                                                :showUrl="route('complementarios-ofertados.show', $programa->id)"
                                                :editUrl="route('complementarios-ofertados.edit', $programa->id)"
                                                :deleteUrl="route('complementarios-ofertados.destroy', $programa->id)"
                                                :showTitle="'Ver detalles del programa'"
                                                :editTitle="'Editar programa'"
                                                :deleteTitle="'Eliminar programa'"
                                                :modelName="'programa'"
                                                :modelId="$programa->id"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <tr data-empty-placeholder="true">
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Aún no hay programas registrados en el sistema.
                                        </td>
                                    </tr>
                                @endforelse
                            </x-data-table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 col-xxl-10 mx-auto">
                    <div class="card card-outline card-warning shadow-sm">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-filter mr-2"></i>Filtros inteligentes
                            </h5>
                            <span class="badge bg-warning text-uppercase text-dark">Segmenta tu oferta</span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <label class="form-label text-muted text-uppercase small mb-1" for="filtro-estado">
                                        Estado
                                    </label>
                                    <select id="filtro-estado" class="form-control form-control-sm">
                                        <option value="" selected>Todos</option>
                                        <option value="1">Con oferta</option>
                                        <option value="0">Sin oferta</option>
                                        <option value="2">Cupos llenos</option>
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <label class="form-label text-muted text-uppercase small mb-1" for="filtro-modalidad">
                                        Modalidad
                                    </label>
                                    <select id="filtro-modalidad" class="form-control form-control-sm">
                                        <option value="" selected>Todas</option>
                                        @foreach ($modalidades as $modalidad)
                                            <option value="{{ Str::slug($modalidad->parametro->name) }}">
                                                {{ $modalidad->parametro->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <label class="form-label text-muted text-uppercase small mb-1" for="filtro-jornada">
                                        Jornada
                                    </label>
                                    <select id="filtro-jornada" class="form-control form-control-sm">
                                        <option value="" selected>Todas</option>
                                        @foreach ($jornadas as $jornada)
                                            <option value="{{ Str::slug($jornada->jornada) }}">
                                                {{ $jornada->jornada }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="reset-filtros">
                                    <i class="fas fa-undo mr-1"></i> Restablecer filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12 col-xxl-10 mx-auto">
                    <div class="row align-items-stretch">
                        <div class="col-12 col-lg-8 mb-3 mb-lg-0">
                            <div class="card card-outline card-success shadow-sm h-100">
                                <div
                                    class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-md-between">
                                    <div>
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-chart-bar mr-2"></i>Resumen general
                                        </h5>
                                        <span class="text-muted small">Monitorea el estado de tu oferta en tiempo
                                            real.</span>
                                    </div>
                                    <span class="badge bg-success mt-2 mt-md-0 text-uppercase">Actualizado</span>
                                </div>
                                <div class="card-body pb-2">
                                    <div class="row">
                                        <div class="col-sm-6 col-lg-4 mb-3">
                                            <div class="info-box bg-light shadow-none h-100" data-toggle="tooltip"
                                                title="Cantidad total de programas registrados.">
                                                <span class="info-box-icon bg-primary text-white">
                                                    <i class="fas fa-layer-group"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span
                                                        class="info-box-text text-muted text-uppercase small">Programas</span>
                                                    <span class="info-box-number h4 mb-0">
                                                        {{ number_format($totalProgramas) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4 mb-3">
                                            <div class="info-box bg-light shadow-none h-100" data-toggle="tooltip"
                                                title="Programas con oferta activa actualmente.">
                                                <span class="info-box-icon bg-success text-white">
                                                    <i class="fas fa-check-circle"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted text-uppercase small">Con
                                                        oferta</span>
                                                    <span class="info-box-number h4 mb-0 text-success">
                                                        {{ number_format($programasConOferta) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4 mb-3">
                                            <div class="info-box bg-light shadow-none h-100" data-toggle="tooltip"
                                                title="Programas que agotaron sus cupos.">
                                                <span class="info-box-icon bg-warning text-white">
                                                    <i class="fas fa-user-times"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted text-uppercase small">Cupos
                                                        llenos</span>
                                                    <span class="info-box-number h4 mb-0 text-warning">
                                                        {{ number_format($programasCuposLlenos) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 col-lg-4 mb-3">
                                            <div class="info-box bg-light shadow-none h-100" data-toggle="tooltip"
                                                title="Programas que están en etapa de preparación.">
                                                <span class="info-box-icon bg-secondary text-white">
                                                    <i class="fas fa-hourglass-half"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-muted text-uppercase small">En
                                                        preparación</span>
                                                    <span class="info-box-number h4 mb-0 text-secondary">
                                                        {{ number_format($programasSinOferta) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="card card-outline card-info shadow-sm h-100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-lightbulb mr-2"></i>Recomendaciones
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">
                                        <i class="fas fa-info-circle mr-1"></i> Buenas prácticas
                                    </h6>
                                    <ul class="mb-3 small text-muted pl-3">
                                        <li>Verifica semanalmente los programas con cupos llenos para reprogramar oferta.
                                        </li>
                                        <li>Actualiza la programación cuando los ambientes cambien de disponibilidad.</li>
                                        <li>Sincroniza jornadas y modalidades para evitar traslapes de horarios.</li>
                                    </ul>
                                    <div class="alert alert-info mb-0">
                                        <h6 class="alert-heading mb-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Tip adicional
                                        </h6>
                                        <p class="mb-0 small">
                                            Aprovecha los filtros para validar en segundos la distribución de oferta por
                                            modalidad
                                            y jornada antes de abrir nuevas inscripciones.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modal: ver programa --}}
    <div class="modal fade" id="modal-ver-programa" tabindex="-1" aria-labelledby="modalVerProgramaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title" id="modalVerProgramaLabel">
                        <i class="fas fa-eye mr-2"></i>Ficha del programa
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nombre</dt>
                        <dd class="col-sm-8" id="detalle-nombre">-</dd>
                        <dt class="col-sm-4">Código</dt>
                        <dd class="col-sm-8" id="detalle-codigo">-</dd>
                        <dt class="col-sm-4">Descripción</dt>
                        <dd class="col-sm-8" id="detalle-justificacion">-</dd>
                        <dt class="col-sm-4">Requisitos de Ingreso</dt>
                        <dd class="col-sm-8" id="detalle-requisitos-ingreso">-</dd>
                        <dt class="col-sm-4">Duración</dt>
                        <dd class="col-sm-8" id="detalle-duracion">-</dd>
                        <dt class="col-sm-4">Cupos</dt>
                        <dd class="col-sm-8" id="detalle-cupos">-</dd>
                        <dt class="col-sm-4">Modalidad</dt>
                        <dd class="col-sm-8" id="detalle-modalidad">-</dd>
                        <dt class="col-sm-4">Jornada</dt>
                        <dd class="col-sm-8" id="detalle-jornada">-</dd>
                        <dt class="col-sm-4">Ambiente</dt>
                        <dd class="col-sm-8" id="detalle-ambiente">-</dd>
                        <dt class="col-sm-4">Comentario ambiente</dt>
                        <dd class="col-sm-8" id="detalle-ambiente-comentario">-</dd>
                        <dt class="col-sm-4">Estado</dt>
                        <dd class="col-sm-8" id="detalle-estado">-</dd>
                        <dt class="col-sm-4">Días de formación</dt>
                        <dd class="col-sm-8" id="detalle-dias">-</dd>
                    </dl>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: editar programa --}}
    <div class="modal fade" id="modal-editar-programa" tabindex="-1" aria-labelledby="modalEditarProgramaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-gradient-warning text-white">
                    <h5 class="modal-title" id="modalEditarProgramaLabel">
                        <i class="fas fa-edit mr-2"></i>Editar programa
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form-editar-programa">
                    <div class="modal-body">
                        <input type="hidden" id="edit-programa-id">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold" for="edit-nombre">Nombre</label>
                                <input type="text" class="form-control" id="edit-nombre" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold" for="edit-codigo">Código</label>
                                <input type="text" class="form-control" id="edit-codigo" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-semibold" for="edit-justificacion">Descripción</label>
                            <textarea class="form-control" id="edit-justificacion" rows="3" required maxlength="600"></textarea>
                            <small class="form-text text-muted">Máximo 600 caracteres</small>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-semibold" for="edit-requisitos-ingreso">Requisitos de Ingreso</label>
                            <textarea class="form-control" id="edit-requisitos-ingreso" rows="3" required maxlength="400"></textarea>
                            <small class="form-text text-muted">Máximo 400 caracteres</small>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold" for="edit-duracion">Duración (horas)</label>
                                <input type="number" min="1" class="form-control" id="edit-duracion" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="font-weight-semibold" for="edit-cupos">Cupos</label>
                                <input type="number" min="1" class="form-control" id="edit-cupos" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="font-weight-semibold" for="edit-modalidad">Modalidad</label>
                                <select class="form-control" id="edit-modalidad" required>
                                    @foreach ($modalidades as $mod)
                                        <option value="{{ $mod->id }}">{{ $mod->parametro->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="font-weight-semibold" for="edit-jornada">Jornada</label>
                                <select class="form-control" id="edit-jornada" required>
                                    @foreach ($jornadas as $jor)
                                        <option value="{{ $jor->id }}">{{ $jor->jornada }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="font-weight-semibold" for="edit-estado">Estado</label>
                                <select class="form-control" id="edit-estado" required>
                                    <option value="0">Sin oferta</option>
                                    <option value="1">Con oferta</option>
                                    <option value="2">Cupos llenos</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-semibold" for="edit-ambiente">Ambiente</label>
                            <select class="form-control" id="edit-ambiente" required>
                                @foreach ($ambientes as $ambiente)
                                    <option value="{{ $ambiente->id }}">
                                        {{ $ambiente->title }} · {{ $ambiente->piso->piso ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="fas fa-save mr-1"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <!-- Cargar Axios desde CDN -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- Datos del servidor para JavaScript -->
    <script type="application/json" id="modalidades-data">
        @json($modalidades)
    </script>
    <script type="application/json" id="jornadas-data">
        @json($jornadas)
    </script>
    <script type="application/json" id="ambientes-data">
        @json($ambientes)
    </script>
    <!-- Rutas del servidor para JavaScript -->
    <script type="application/json" id="routes-data">
        {
            "editApi": "{{ route('complementarios-ofertados.edit-api', ':id') }}",
            "update": "{{ route('complementarios-ofertados.update', ':id') }}",
            "destroy": "{{ route('complementarios-ofertados.destroy', ':id') }}"
        }
    </script>
    <script>
        // Rutas del servidor
        const routes = JSON.parse(document.getElementById('routes-data').textContent);
        $(function() {
            const table = $('#programas-table').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                    zeroRecords: 'No hay resultados para los filtros aplicados.'
                },
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                dom: '<"row align-items-center mb-2 px-3 pt-3"<"col-md-6"l><"col-md-6 text-md-right"f>>' +
                    'rt' +
                    '<"row align-items-center mt-2 px-3 pb-3"<"col-md-5"i><"col-md-7"p>>'
            });

            $('[data-toggle="tooltip"]').tooltip();

            const resetFiltrosBtn = $('#reset-filtros');
            const filtroEstado = $('#filtro-estado');
            const filtroModalidad = $('#filtro-modalidad');
            const filtroJornada = $('#filtro-jornada');

            const aplicarFiltros = () => {
                const estado = filtroEstado.val();
                const modalidad = filtroModalidad.val();
                const jornada = filtroJornada.val();

                table.rows().every(function() {
                    const $row = $(this.node());
                    const coincideEstado = !estado || Number($row.data('estado')) === Number(estado);
                    const coincideModalidad = !modalidad || $row.data('modalidad') === modalidad;
                    const coincideJornada = !jornada || $row.data('jornada') === jornada;
                    $row.toggle(coincideEstado && coincideModalidad && coincideJornada);
                });
            };

            [filtroEstado, filtroModalidad, filtroJornada].forEach($input => {
                $input.on('change', aplicarFiltros);
            });

            resetFiltrosBtn.on('click', () => {
                filtroEstado.val('');
                filtroModalidad.val('');
                filtroJornada.val('');
                aplicarFiltros();
            });

            const fetchPrograma = (id) => {
                const url = routes.editApi.replace(':id', id);
                return axios.get(url).then(response => response.data);
            };

            const showDetalle = (data) => {
                $('#detalle-nombre').text(data.nombre);
                $('#detalle-codigo').text(data.codigo);
                $('#detalle-justificacion').text(data.justificacion || 'N/A');
                $('#detalle-requisitos-ingreso').text(data.requisitos_ingreso || 'N/A');
                $('#detalle-duracion').text(`${data.duracion} horas`);
                $('#detalle-cupos').text(data.cupos);

                const modalidades = JSON.parse(document.getElementById('modalidades-data').textContent);
                const jornadas = JSON.parse(document.getElementById('jornadas-data').textContent);
                const ambientes = JSON.parse(document.getElementById('ambientes-data').textContent);
                const estados = {
                    0: 'Sin oferta',
                    1: 'Con oferta',
                    2: 'Cupos llenos'
                };

                $('#detalle-modalidad').text(
                    modalidades.find(m => m.id === data.modalidad_id)?.parametro?.name ?? 'N/A'
                );
                $('#detalle-jornada').text(
                    jornadas.find(j => j.id === data.jornada_id)?.jornada ?? 'N/A'
                );

                const ambiente = ambientes.find(a => a.id === data.ambiente_id);
                $('#detalle-ambiente').text(
                    ambiente ? `${ambiente.title} · ${ambiente.piso?.piso ?? 'N/A'}` : 'N/A'
                );
                $('#detalle-ambiente-comentario').text(
                    data.ambiente_comentario ? data.ambiente_comentario : 'N/A'
                );
                $('#detalle-estado').text(estados[data.estado] ?? 'N/A');

                if (Array.isArray(data.dias) && data.dias.length) {
                    const dias = data.dias
                        .map(dia => `${dia.nombre ?? 'Día'} (${dia.hora_inicio} - ${dia.hora_fin})`)
                        .join(', ');
                    $('#detalle-dias').text(dias);
                } else {
                    $('#detalle-dias').text('No definidos');
                }

                $('#modal-ver-programa').modal('show');
            };

            const showEditar = (data) => {
                $('#edit-programa-id').val(data.id);
                $('#edit-nombre').val(data.nombre);
                $('#edit-codigo').val(data.codigo);
                $('#edit-justificacion').val(data.justificacion);
                $('#edit-requisitos-ingreso').val(data.requisitos_ingreso);
                $('#edit-duracion').val(data.duracion);
                $('#edit-cupos').val(data.cupos);
                $('#edit-modalidad').val(data.modalidad_id);
                $('#edit-jornada').val(data.jornada_id);
                $('#edit-estado').val(data.estado);
                $('#edit-ambiente').val(data.ambiente_id);
                $('#modal-editar-programa').modal('show');
            };

            // Los botones de view y edit ahora son enlaces directos, no necesitan event listeners
            // El delete se maneja automáticamente por el componente action-buttons con la clase formulario-eliminar

            // Mantener el modal de edición para compatibilidad si se accede desde otra parte
            $(document).on('click', '[data-action="edit-modal"]', function() {
                const id = $(this).data('id');
                fetchPrograma(id)
                    .then(showEditar)
                    .catch(() => {
                        Swal.fire('Error', 'No se pudo cargar la información del programa.', 'error');
                    });
            });

            $(document).on('click', '[data-action="delete"]', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: '¿Eliminar programa?',
                    text: 'Esta acción no se puede revertir.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        const url = routes.destroy.replace(':id', id);
                        return axios.delete(url)
                            .then(response => response.data)
                            .catch(error => {
                                if (error.response) {
                                    // El servidor respondió con un código de error
                                    throw new Error(error.response.data.message || 'Error del servidor');
                                } else if (error.request) {
                                    // La solicitud fue hecha pero no se recibió respuesta
                                    throw new Error('No se recibió respuesta del servidor');
                                } else {
                                    // Algo pasó al configurar la solicitud
                                    throw new Error('Error al configurar la solicitud');
                                }
                            });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then(result => {
                    if (result.isConfirmed) {
                        if (result.value.success) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: result.value.message,
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            // Mostrar error sin recargar la página
                            Swal.fire({
                                title: 'No se puede eliminar',
                                text: result.value.message,
                                icon: 'error',
                                confirmButtonText: 'Entendido',
                                showCancelButton: false
                            });
                        }
                    }
                }).catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: error.message || 'Ocurrió un problema al eliminar el programa.',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                });
            });

            $('#form-editar-programa').on('submit', function(event) {
                event.preventDefault();
                const id = $('#edit-programa-id').val();
                const url = routes.update.replace(':id', id);

                const payload = {
                    nombre: $('#edit-nombre').val(),
                    codigo: $('#edit-codigo').val(),
                    justificacion: $('#edit-justificacion').val(),
                    requisitos_ingreso: $('#edit-requisitos-ingreso').val(),
                    duracion: $('#edit-duracion').val(),
                    cupos: $('#edit-cupos').val(),
                    modalidad_id: $('#edit-modalidad').val(),
                    jornada_id: $('#edit-jornada').val(),
                    estado: $('#edit-estado').val(),
                    ambiente_id: $('#edit-ambiente').val()
                };

                axios.put(url, payload)
                    .then(response => {
                        if (response.data.success) {
                            Swal.fire('Actualizado', response.data.message, 'success')
                                .then(() => window.location.reload());
                        } else {
                            Swal.fire(
                                'Error',
                                response.data.message ?? 'No se pudo actualizar el programa.',
                                'error'
                            );
                        }
                    })
                    .catch(() => {
                        Swal.fire(
                            'Error',
                            'Ocurrió un problema al actualizar el programa.',
                            'error'
                        );
                    });
            });
        });
    </script>
@endsection
