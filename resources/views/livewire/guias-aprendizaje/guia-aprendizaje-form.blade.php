<div class="modal-erp-container">
    <form wire:submit="save">
        <!-- Contenido principal -->
        <div class="modal-body-erp">
            <!-- Bloque 1 - Identidad -->
            <div class="section-block">
                <h6 class="section-title">Identidad de la Guía de Aprendizaje</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="codigo" class="form-label-erp">Código de la Guía</label>
                            <input type="text" 
                                   id="codigo"
                                   wire:model="codigo" 
                                   class="form-control-erp @error('codigo') is-invalid @enderror" 
                                   placeholder="Ej: GA-001" 
                                   maxlength="20"
                                   {{ $isEdit ? 'disabled' : '' }}
                                   required>
                            @error('codigo')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre" class="form-label-erp">Nombre de la Guía</label>
                            <input type="text" 
                                   id="nombre"
                                   wire:model="nombre" 
                                   class="form-control-erp @error('nombre') is-invalid @enderror" 
                                   placeholder="Ej: Guía de Programación Básica" 
                                   maxlength="255"
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bloque 2 - Programa y Duración -->
            <div class="section-block">
                <h6 class="section-title">Asociación y Duración</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="programa_formacion_id" class="form-label-erp">Programa de Formación</label>
                            <select id="programa_formacion_id"
                                    wire:model="programa_formacion_id" 
                                    class="form-control-erp @error('programa_formacion_id') is-invalid @enderror">
                                <option value="">Seleccionar programa...</option>
                                @foreach($programas as $programa)
                                    <option value="{{ $programa->id }}">{{ $programa->codigo }} - {{ Str::limit($programa->nombre, 40) }}</option>
                                @endforeach
                            </select>
                            @error('programa_formacion_id')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="duracion_horas" class="form-label-erp">Duración (horas)</label>
                            <div class="input-group-erp">
                                <input type="number" 
                                       id="duracion_horas"
                                       wire:model="duracion_horas" 
                                       class="form-control-erp @error('duracion_horas') is-invalid @enderror" 
                                       placeholder="Ej: 120" 
                                       min="1"
                                       max="999"
                                       required>
                                <span class="input-group-text-erp">horas</span>
                            </div>
                            @error('duracion_horas')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="duracion_meses" class="form-label-erp">Duración (meses)</label>
                            <div class="input-group-erp">
                                <input type="number" 
                                       id="duracion_meses"
                                       wire:model="duracion_meses" 
                                       class="form-control-erp @error('duracion_meses') is-invalid @enderror" 
                                       placeholder="Ej: 3" 
                                       min="1"
                                       max="12"
                                       required>
                                <span class="input-group-text-erp">meses</span>
                            </div>
                            @error('duracion_meses')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bloque 3 - Descripción -->
            <div class="section-block">
                <h6 class="section-title">Descripción y Contenido</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="descripcion" class="form-label-erp">Descripción</label>
                            <textarea id="descripcion"
                                      wire:model="descripcion" 
                                      class="form-control-erp @error('descripcion') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Describe brevemente el propósito y contenido de la guía..."
                                      maxlength="1000"
                                      required></textarea>
                            @error('descripcion')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                            <small class="form-text">Caracteres: {{ strlen($descripcion) }}/1000</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bloque 4 - Metodología -->
            <div class="section-block">
                <h6 class="section-title">Metodología y Evaluación</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="objetivo_general" class="form-label-erp">Objetivo General</label>
                            <textarea id="objetivo_general"
                                      wire:model="objetivo_general" 
                                      class="form-control-erp @error('objetivo_general') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Describe el objetivo general de la guía..."
                                      maxlength="500"></textarea>
                            @error('objetivo_general')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                            <small class="form-text">Caracteres: {{ strlen($objetivo_general) }}/500</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="metodologia" class="form-label-erp">Metodología</label>
                            <textarea id="metodologia"
                                      wire:model="metodologia" 
                                      class="form-control-erp @error('metodologia') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Describe la metodología de enseñanza-aprendizaje..."
                                      maxlength="1000"></textarea>
                            @error('metodologia')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                            <small class="form-text">Caracteres: {{ strlen($metodologia) }}/1000</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="evaluacion" class="form-label-erp">Sistema de Evaluación</label>
                            <textarea id="evaluacion"
                                      wire:model="evaluacion" 
                                      class="form-control-erp @error('evaluacion') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Describe el sistema de evaluación..."
                                      maxlength="1000"></textarea>
                            @error('evaluacion')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                            <small class="form-text">Caracteres: {{ strlen($evaluacion) }}/1000</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bloque 5 - Resultados de Aprendizaje -->
            <div class="section-block">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h6 class="section-title" style="margin-bottom: 0;">Gestión de Resultados de Aprendizaje</h6>
                    <div style="display: flex; gap: 0.5rem;">
                        <span style="font-size: 0.875rem; color: #6b7280; padding: 0.25rem 0.75rem; background: #f8fafc; border-radius: 4px;">
                            {{ count($resultadosSeleccionados) }} seleccionados
                        </span>
                        <span style="font-size: 0.875rem; color: #6b7280; padding: 0.25rem 0.75rem; background: #f0fdf4; border-radius: 4px;">
                            {{ $resultadosDisponibles->count() }} disponibles
                        </span>
                    </div>
                </div>
                
                <!-- Filtro de búsqueda -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <div class="input-group-erp">
                        <input type="text" 
                               wire:model.live="searchResultado" 
                               class="form-control-erp"
                               placeholder="Buscar resultados por código o descripción...">
                        <span class="input-group-text-erp">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
                
                <!-- Tabla de Resultados Disponibles -->
                <div class="form-group">
                    <label class="form-label-erp">Resultados Disponibles para Asignar</label>
                    <div class="table-scroll-wrapper" style="max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px;">
                        <table class="modern-table" style="margin: 0;">
                            <thead style="position: sticky; top: 0; background: #f9fafb; z-index: 10;">
                                <tr>
                                    <th style="width: 50px; min-width: 50px; max-width: 50px; text-align: center; vertical-align: middle; padding-left: 20px; padding-right: 8px;">
                                        <input type="checkbox" 
                                               wire:model.live="selectAll" 
                                               class="form-check-input">
                                    </th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th style="width: 80px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($resultadosDisponibles as $resultado)
                                    <tr>
                                        <td style="width: 50px; min-width: 50px; max-width: 50px; text-align: center; vertical-align: middle; padding-left: 20px; padding-right: 8px; position: relative;">
                                            <input type="checkbox" 
                                                   wire:model.live="resultadosSeleccionados" 
                                                   value="{{ $resultado->id }}"
                                                   class="form-check-input">
                                        </td>
                                        <td style="font-weight: 500; font-size: 0.875rem;">{{ $resultado->codigo }}</td>
                                        <td style="font-size: 0.875rem;">{{ Str::limit($resultado->nombre, 60) }}</td>
                                        <td>
                                            <span class="badge-modern {{ $resultado->status ? 'badge-active' : 'badge-inactive' }}">
                                                {{ $resultado->status ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td style="text-align: center;">
                                            <button type="button" 
                                                    wire:click="agregarResultado({{ $resultado->id }})" 
                                                    class="btn-action btn-success" 
                                                    title="Asignar resultado">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 2rem;">
                                            <div style="color: #6b7280;">
                                                <i class="fas fa-search fa-2x mb-2"></i>
                                                <p>No hay resultados disponibles</p>
                                                <small style="font-size: 0.75rem;">Intenta ajustar el término de búsqueda</small>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Tabla de Resultados Seleccionados -->
                <div class="form-group">
                    <label class="form-label-erp">Resultados Asignados a la Guía</label>
                    <div class="table-scroll-wrapper" style="max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px;">
                        <table class="modern-table" style="margin: 0;">
                            <thead style="position: sticky; top: 0; background: #f9fafb; z-index: 10;">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Estado</th>
                                    <th style="width: 80px;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($resultadosSeleccionados as $resultadoId)
                                    @php
                                        $resultado = $resultadosAprendizaje->where('id', $resultadoId)->first();
                                    @endphp
                                    @if($resultado)
                                        <tr>
                                            <td style="font-weight: 500; font-size: 0.875rem;">{{ $resultado->codigo }}</td>
                                            <td style="font-size: 0.875rem;">{{ Str::limit($resultado->nombre, 60) }}</td>
                                            <td>
                                                <span class="badge-modern {{ $resultado->status ? 'badge-active' : 'badge-inactive' }}">
                                                    {{ $resultado->status ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td style="text-align: center;">
                                                <button type="button" 
                                                        wire:click="quitarResultado({{ $resultadoId }})" 
                                                        class="btn-action btn-danger" 
                                                        title="Quitar resultado">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 2rem;">
                                            <div style="color: #6b7280;">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No hay resultados asignados</p>
                                                <small style="font-size: 0.75rem;">Selecciona resultados de la tabla de disponibles</small>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Acciones Masivas -->
                @if(count($resultadosSeleccionados) > 0)
                    <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem;">
                        <button type="button" 
                                wire:click="limpiarSeleccion" 
                                class="btn-erp btn-secondary">
                            <i class="fas fa-times"></i>
                            Limpiar Selección
                        </button>
                        <button type="button" 
                                wire:click="seleccionarTodos" 
                                class="btn-erp btn-primary">
                            <i class="fas fa-check-double"></i>
                            Seleccionar Todos
                        </button>
                    </div>
                @endif
            </div>
            
            <!-- Bloque 6 - Estado -->
            <div class="section-block">
                <h6 class="section-title">Estado</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status" wire:model="status">
                                <label class="form-check-label" for="status">
                                    <strong>Guía de Aprendizaje Activa</strong>
                                    <span class="form-text">Las guías activas pueden ser utilizadas en los programas de formación</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bloque 7 - Información Importante -->
            <div class="section-block section-info">
                <div class="info-content">
                    <i class="info-icon fas fa-info-circle"></i>
                    <div class="info-text">
                        <strong>Nota:</strong> El código se almacenará automáticamente en mayúsculas. 
                        La asociación a resultados de aprendizaje es fundamental para el seguimiento del programa.
                        La duración total se distribuirá automáticamente entre los resultados asociados.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer ERP -->
        <div class="modal-footer-erp">
            <div class="footer-actions">
                <button type="button" wire:click="cancel" class="btn-erp btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="button" 
                        wire:click="save" 
                        class="btn-erp btn-primary">
                    <i class="fas fa-save"></i>
                    {{ $isEdit ? 'Actualizar' : 'Guardar' }} Guía
                </button>
            </div>
        </div>
    </form>
</div>
