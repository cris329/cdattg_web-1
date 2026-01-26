<form wire:submit="save" class="instructor-form">
    <!-- Sección 1: Selección de Persona (solo en creación) -->
    @if (!$isEdit)
        <div class="modal-section">
            <h6 class="section-title">
                <i class="fas fa-user"></i>
                Selección de Persona
            </h6>
            
            @if ($personasDisponibles->isEmpty())
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    No hay personas disponibles sin rol de instructor. Debe registrar nuevas personas o liberar el rol de un instructor existente.
                </div>
            @else
                <div class="form-group">
                    <label for="persona_id" class="form-label required">
                        Persona
                    </label>
                    <div wire:ignore>
                        <select wire:model="persona_id" 
                                id="persona_id" 
                                class="form-control-erp @error('persona_id') is-invalid @enderror" 
                                required 
                                data-placeholder="-- Selecciona una persona --">
                            <option value="">-- Selecciona una persona --</option>
                            @foreach ($personasDisponibles as $persona)
                                <option value="{{ $persona->id }}">
                                    {{ $persona->nombre_completo }} — {{ $persona->numero_documento }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('persona_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endif
        </div>
    @endif

    <!-- Sección 2: Información Laboral -->
    <div class="modal-section">
        <h6 class="section-title">
            <i class="fas fa-briefcase"></i>
            Información Laboral
        </h6>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="regional_id" class="form-label required">Regional</label>
                <select wire:model="regional_id" 
                        id="regional_id" 
                        class="form-control-erp @error('regional_id') is-invalid @enderror" 
                        required>
                    <option value="">-- Selecciona una regional --</option>
                    @foreach ($regionales as $regional)
                        <option value="{{ $regional->id }}">{{ $regional->nombre }}</option>
                    @endforeach
                </select>
                @error('regional_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="centro_formacion_id" class="form-label">Centro de Formación</label>
                <select wire:model="centro_formacion_id" 
                        id="centro_formacion_id" 
                        class="form-control-erp @error('centro_formacion_id') is-invalid @enderror">
                    <option value="">-- Selecciona un centro --</option>
                    @foreach ($centrosFormacion as $centro)
                        <option value="{{ $centro->id }}">{{ $centro->nombre }}</option>
                    @endforeach
                </select>
                @error('centro_formacion_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="tipo_vinculacion_id" class="form-label">Tipo de Vinculación</label>
                <select wire:model="tipo_vinculacion_id" 
                        id="tipo_vinculacion_id" 
                        class="form-control-erp @error('tipo_vinculacion_id') is-invalid @enderror">
                    <option value="">-- Selecciona un tipo --</option>
                    @foreach ($tiposVinculacion as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->parametro->name }}</option>
                    @endforeach
                </select>
                @error('tipo_vinculacion_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="fecha_ingreso_sena" class="form-label">Fecha de Ingreso al SENA</label>
                <input type="date" 
                       wire:model="fecha_ingreso_sena" 
                       id="fecha_ingreso_sena" 
                       class="form-control-erp @error('fecha_ingreso_sena') is-invalid @enderror">
                @error('fecha_ingreso_sena')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <!-- Jornadas de trabajo -->
        <div class="form-group">
            <label class="form-label">Jornadas de Trabajo</label>
            <div wire:ignore>
                <select wire:model.live="jornadas" 
                        class="form-control-erp" 
                        multiple="multiple" 
                        data-placeholder="-- Selecciona jornadas --">
                    @foreach ($jornadasTrabajo as $jornada)
                        <option value="{{ $jornada->id }}">{{ $jornada->parametro->name }}</option>
                    @endforeach
                </select>
            </div>
            <small class="text-muted">Puedes seleccionar múltiples jornadas manteniendo presionado Ctrl/Cmd</small>
        </div>
        
        <!-- Experiencia -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="anos_experiencia" class="form-label">Años de Experiencia</label>
                <input type="number" 
                       wire:model="anos_experiencia" 
                       id="anos_experiencia" 
                       min="0" 
                       max="50"
                       class="form-control-erp @error('anos_experiencia') is-invalid @enderror">
                @error('anos_experiencia')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="experiencia_instructor_meses" class="form-label">Meses como Instructor</label>
                <input type="number" 
                       wire:model="experiencia_instructor_meses" 
                       id="experiencia_instructor_meses" 
                       min="0" 
                       max="600"
                       class="form-control-erp @error('experiencia_instructor_meses') is-invalid @enderror">
                @error('experiencia_instructor_meses')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="form-group">
            <label for="experiencia_laboral" class="form-label">Experiencia Laboral</label>
            <textarea wire:model="experiencia_laboral" 
                      id="experiencia_laboral" 
                      rows="3" 
                      class="form-control-erp @error('experiencia_laboral') is-invalid @enderror"
                      placeholder="Describe brevemente tu experiencia laboral relevante..."></textarea>
            @error('experiencia_laboral')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- Sección 3: Formación Académica -->
    <div class="modal-section">
        <h6 class="section-title">
            <i class="fas fa-graduation-cap"></i>
            Formación Académica
        </h6>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="nivel_academico_id" class="form-label">Nivel Académico</label>
                <select wire:model="nivel_academico_id" 
                        id="nivel_academico_id" 
                        class="form-control-erp @error('nivel_academico_id') is-invalid @enderror">
                    <option value="">-- Selecciona un nivel --</option>
                    @foreach ($nivelesAcademicos as $nivel)
                        <option value="{{ $nivel->id }}">{{ $nivel->parametro->name }}</option>
                    @endforeach
                </select>
                @error('nivel_academico_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="formacion_pedagogia" class="form-label">Formación Pedagógica</label>
                <textarea wire:model="formacion_pedagogia" 
                          id="formacion_pedagogia" 
                          rows="3" 
                          class="form-control-erp @error('formacion_pedagogia') is-invalid @enderror"
                          placeholder="Describe tu formación pedagógica..."></textarea>
                @error('formacion_pedagogia')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <!-- Campos dinámicos para formación -->
        <div class="dynamic-fields">
            <!-- Títulos Obtenidos -->
            <div class="form-group">
                <label class="form-label">Títulos Obtenidos</label>
                @foreach ($titulos_obtenidos as $index => $titulo)
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <input type="text" 
                               wire:model="titulos_obtenidos.{{ $index }}" 
                               class="form-control-erp @error('titulos_obtenidos.'.$index) is-invalid @enderror"
                               placeholder="Nombre del título">
                        <button type="button" 
                                wire:click="removeCampo('titulos_obtenidos', {{ $index }})"
                                class="btn-erp btn-danger"
                                style="width: auto; padding: 8px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @error('titulos_obtenidos.'.$index)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @endforeach
                <button type="button" 
                        wire:click="addCampo('titulos_obtenidos')"
                        class="btn-erp btn-secondary"
                        style="margin-top: 0.5rem;">
                    <i class="fas fa-plus"></i>
                    Agregar Título
                </button>
            </div>
            
            <!-- Instituciones Educativas -->
            <div class="form-group">
                <label class="form-label">Instituciones Educativas</label>
                @foreach ($instituciones_educativas as $index => $institucion)
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <input type="text" 
                               wire:model="instituciones_educativas.{{ $index }}" 
                               class="form-control-erp @error('instituciones_educativas.'.$index) is-invalid @enderror"
                               placeholder="Nombre de la institución">
                        <button type="button" 
                                wire:click="removeCampo('instituciones_educativas', {{ $index }})"
                                class="btn-erp btn-danger"
                                style="width: auto; padding: 8px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @error('instituciones_educativas.'.$index)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @endforeach
                <button type="button" 
                        wire:click="addCampo('instituciones_educativas')"
                        class="btn-erp btn-secondary"
                        style="margin-top: 0.5rem;">
                    <i class="fas fa-plus"></i>
                    Agregar Institución
                </button>
            </div>
            
            <!-- Certificaciones Técnicas -->
            <div class="form-group">
                <label class="form-label">Certificaciones Técnicas</label>
                @foreach ($certificaciones_tecnicas as $index => $certificacion)
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <input type="text" 
                               wire:model="certificaciones_tecnicas.{{ $index }}" 
                               class="form-control-erp @error('certificaciones_tecnicas.'.$index) is-invalid @enderror"
                               placeholder="Nombre de la certificación">
                        <button type="button" 
                                wire:click="removeCampo('certificaciones_tecnicas', {{ $index }})"
                                class="btn-erp btn-danger"
                                style="width: auto; padding: 8px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @error('certificaciones_tecnicas.'.$index)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @endforeach
                <button type="button" 
                        wire:click="addCampo('certificaciones_tecnicas')"
                        class="btn-erp btn-secondary"
                        style="margin-top: 0.5rem;">
                    <i class="fas fa-plus"></i>
                    Agregar Certificación
                </button>
            </div>
            
            <!-- Cursos Complementarios -->
            <div class="form-group">
                <label class="form-label">Cursos Complementarios</label>
                @foreach ($cursos_complementarios as $index => $curso)
                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                        <input type="text" 
                               wire:model="cursos_complementarios.{{ $index }}" 
                               class="form-control-erp @error('cursos_complementarios.'.$index) is-invalid @enderror"
                               placeholder="Nombre del curso">
                        <button type="button" 
                                wire:click="removeCampo('cursos_complementarios', {{ $index }})"
                                class="btn-erp btn-danger"
                                style="width: auto; padding: 8px 12px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    @error('cursos_complementarios.'.$index)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                @endforeach
                <button type="button" 
                        wire:click="addCampo('cursos_complementarios')"
                        class="btn-erp btn-secondary"
                        style="margin-top: 0.5rem;">
                    <i class="fas fa-plus"></i>
                    Agregar Curso
                </button>
            </div>
        </div>
    </div>

    <!-- Sección 4: Competencias y Habilidades -->
    <div class="modal-section">
        <h6 class="section-title">
            <i class="fas fa-cogs"></i>
            Competencias y Habilidades
        </h6>
        
        <!-- Áreas de Experticia -->
        <div class="form-group">
            <label class="form-label">Áreas de Experticia</label>
            @foreach ($areas_experticia as $index => $area)
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <input type="text" 
                           wire:model="areas_experticia.{{ $index }}" 
                           class="form-control-erp @error('areas_experticia.'.$index) is-invalid @enderror"
                           placeholder="Describe un área de experticia">
                    <button type="button" 
                            wire:click="removeCampo('areas_experticia', {{ $index }})"
                            class="btn-erp btn-danger"
                            style="width: auto; padding: 8px 12px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                @error('areas_experticia.'.$index)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endforeach
            <button type="button" 
                    wire:click="addCampo('areas_experticia')"
                    class="btn-erp btn-secondary"
                    style="margin-top: 0.5rem;">
                <i class="fas fa-plus"></i>
                Agregar Área
            </button>
        </div>
        
        <!-- Competencias TIC -->
        <div class="form-group">
            <label class="form-label">Competencias TIC</label>
            @foreach ($competencias_tic as $index => $competencia)
                <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <input type="text" 
                           wire:model="competencias_tic.{{ $index }}" 
                           class="form-control-erp @error('competencias_tic.'.$index) is-invalid @enderror"
                           placeholder="Describe una competencia TIC">
                    <button type="button" 
                            wire:click="removeCampo('competencias_tic', {{ $index }})"
                            class="btn-erp btn-danger"
                            style="width: auto; padding: 8px 12px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                @error('competencias_tic.'.$index)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endforeach
            <button type="button" 
                    wire:click="addCampo('competencias_tic')"
                    class="btn-erp btn-secondary"
                    style="margin-top: 0.5rem;">
                <i class="fas fa-plus"></i>
                Agregar Competencia
            </button>
        </div>
        
        <!-- Idiomas -->
        <div class="form-group">
            <label class="form-label">Idiomas</label>
            @foreach ($idiomas as $index => $idioma)
                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 0.5rem; margin-bottom: 0.5rem;">
                    <input type="text" 
                           wire:model="idiomas.{{ $index }}.idioma" 
                           class="form-control-erp @error('idiomas.'.$index.'.idioma') is-invalid @enderror"
                           placeholder="Idioma">
                    <input type="text" 
                           wire:model="idiomas.{{ $index }}.nivel" 
                           class="form-control-erp @error('idiomas.'.$index.'.nivel') is-invalid @enderror"
                           placeholder="Nivel">
                    <button type="button" 
                            wire:click="removeCampo('idiomas', {{ $index }})"
                            class="btn-erp btn-danger"
                            style="width: auto; padding: 8px 12px;">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                @error('idiomas.'.$index.'.idioma')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @error('idiomas.'.$index.'.nivel')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endforeach
            <button type="button" 
                    wire:click="addCampo('idiomas')"
                    class="btn-erp btn-secondary"
                    style="margin-top: 0.5rem;">
                <i class="fas fa-plus"></i>
                Agregar Idioma
            </button>
        </div>
        
        <!-- Modalidades (Habilidades Pedagógicas) -->
        <div class="form-group">
            <label class="form-label">Modalidades (Habilidades Pedagógicas)</label>
            <div wire:ignore>
                <select wire:model.live="modalidades" 
                        class="form-control-erp" 
                        multiple="multiple" 
                        data-placeholder="-- Selecciona modalidades --">
                    @foreach ($modalidadesDisponibles as $modalidad)
                        <option value="{{ $modalidad->id }}">{{ $modalidad->parametro->name }}</option>
                    @endforeach
                </select>
            </div>
            <small class="text-muted">Puedes seleccionar múltiples modalidades manteniendo presionado Ctrl/Cmd</small>
        </div>
        
        <!-- Especialidades -->
        <div class="form-group">
            <label class="form-label">Especialidades</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="font-size: 13px; color: #6b7280; margin-bottom: 0.5rem; display: block;">Especialidad Principal</label>
                    <select wire:model="especialidades.principal" 
                            class="form-control-erp">
                        <option value="">-- Selecciona especialidad principal --</option>
                        @foreach ($redesConocimiento as $red)
                            <option value="{{ $red->id }}">{{ $red->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size: 13px; color: #6b7280; margin-bottom: 0.5rem; display: block;">Especialidades Secundarias</label>
                    <div wire:ignore>
                        <select wire:model.live="especialidades.secundarias" 
                                class="form-control-erp" 
                                multiple="multiple" 
                                data-placeholder="-- Selecciona especialidades --">
                            @foreach ($redesConocimiento as $red)
                                <option value="{{ $red->id }}">{{ $red->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <small class="text-muted">Puedes seleccionar múltiples especialidades</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección 5: Información Administrativa -->
    <div class="modal-section">
        <h6 class="section-title">
            <i class="fas fa-file-contract"></i>
            Información Administrativa
        </h6>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="numero_contrato" class="form-label">Número de Contrato</label>
                <input type="text" 
                       wire:model="numero_contrato" 
                       id="numero_contrato" 
                       class="form-control-erp @error('numero_contrato') is-invalid @enderror"
                       placeholder="Ej: 123-2024">
                @error('numero_contrato')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="fecha_inicio_contrato" class="form-label">Fecha Inicio Contrato</label>
                <input type="date" 
                       wire:model="fecha_inicio_contrato" 
                       id="fecha_inicio_contrato" 
                       class="form-control-erp @error('fecha_inicio_contrato') is-invalid @enderror">
                @error('fecha_inicio_contrato')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="fecha_fin_contrato" class="form-label">Fecha Fin Contrato</label>
                <input type="date" 
                       wire:model="fecha_fin_contrato" 
                       id="fecha_fin_contrato" 
                       class="form-control-erp @error('fecha_fin_contrato') is-invalid @enderror">
                @error('fecha_fin_contrato')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="supervisor_contrato" class="form-label">Supervisor del Contrato</label>
                <input type="text" 
                       wire:model="supervisor_contrato" 
                       id="supervisor_contrato" 
                       class="form-control-erp @error('supervisor_contrato') is-invalid @enderror"
                       placeholder="Nombre del supervisor">
                @error('supervisor_contrato')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Footer del formulario -->
    <div class="modal-footer">
        <button type="button" 
                wire:click="$dispatch('closeModal')" 
                class="btn-erp btn-secondary">
            <i class="fas fa-times"></i>
            Cancelar
        </button>
        
        <button type="submit" 
                class="btn-erp btn-primary"
                wire:loading.attr="disabled">
            <i wire:loading.remove wire:target="save" class="fas fa-save"></i>
            <span wire:loading.remove wire:target="save">
                {{ $isEdit ? 'Actualizar Instructor' : 'Crear Instructor' }}
            </span>
            <i wire:loading wire:target="save" class="fas fa-spinner fa-spin"></i>
            <span wire:loading wire:target="save">Procesando...</span>
        </button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Select2 para selects múltiples
    $('.form-control-erp[multiple]').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Inicializar Select2 para selects simples
    $('.form-control-erp:not([multiple])').not('[wire\\:ignore]').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
});
</script>
@endpush
