<div>
    <div class="toolbar">
        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" wire:model.live.debounce.300ms="search" class="search-input"
                placeholder="Buscar por ficha, programa, aprendiz o documento...">
        </div>

        <div class="results-selector">
            <select wire:model.live="perPage" class="results-select">
                <option value="10">10 resultados</option>
                <option value="15">15 resultados</option>
                <option value="25">25 resultados</option>
                <option value="50">50 resultados</option>
            </select>
        </div>
    </div>

    <div wire:loading wire:target="search" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Buscando...
    </div>

    <div wire:loading wire:target="perPage" class="loading-indicator" style="display: none;">
        <i class="fas fa-spinner fa-spin"></i>
        Actualizando resultados...
    </div>

    <div class="table-scroll-wrapper">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Ficha</th>
                    <th>Programa</th>
                    <th>Instructor</th>
                    <th>Estado</th>
                    <th># Aprendices</th>
                    <th class="th-actions sticky-actions">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($asistencias as $asistencia)
                    @php
                        $ficha = $asistencia->instructorFicha;
                        $programa = $ficha?->programaFormacion;
                        $instructorPersona = $ficha?->instructor?->persona;
                    @endphp
                    <tr>
                        <td>
                            <span class="badge-modern badge-primary">
                                {{ $asistencia->fecha?->format('d/m/Y') ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-modern badge-info">{{ $ficha?->ficha ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if ($programa)
                                <span class="badge-modern badge-success">{{ $programa->nombre }}</span>
                            @else
                                <span class="badge-modern badge-secondary">N/A</span>
                            @endif
                        </td>
                        <td>
                            {{ $instructorPersona?->primer_nombre ?? '' }} {{ $instructorPersona?->primer_apellido ?? '' }}
                        </td>
                        <td>
                            <span class="badge-modern {{ $asistencia->is_finished ? 'badge-success' : 'badge-warning' }}">
                                {{ $asistencia->is_finished ? 'Finalizada' : 'Activa' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-modern badge-primary">{{ $asistencia->asistencia_aprendices_count ?? 0 }}</span>
                        </td>
                        <td class="td-actions sticky-actions">
                            <a href="{{ route('asistencia.consulta.show', $asistencia->id) }}" class="btn-action btn-view" title="Ver asistencia">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('asistencia.consulta.pdf', $asistencia->id) }}" class="btn-action btn-view" title="Descargar PDF">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <h3>No hay asistencias registradas</h3>
                                <p>
                                    @if ($search)
                                        No se encontraron resultados con la búsqueda aplicada.
                                    @else
                                        Aún no hay asistencias para mostrar.
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <div class="pagination-modern">
            <div class="pagination-info">
                Mostrando {{ $asistencias->firstItem() ?? 0 }} a {{ $asistencias->lastItem() ?? 0 }}
                de {{ $asistencias->total() }} resultados
            </div>
            <div class="pagination-links">
                {{ $asistencias->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
