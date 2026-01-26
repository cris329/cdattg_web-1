<div class="modal-erp-container">
    <form wire:submit="save">
        <!-- Contenido principal -->
        <div class="modal-body-erp">
            <!-- Bloque 1 - Identidad -->
            <div class="section-block">
                <h6 class="section-title">Identidad del Programa</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="codigo" class="form-label-erp">Código del Programa</label>
                            <input type="text" 
                                   id="codigo"
                                   wire:model="codigo" 
                                   class="form-control-erp @error('codigo') is-invalid @enderror" 
                                   placeholder="Ej: 228001 (solo números)" 
                                   maxlength="6" 
                                   pattern="[0-9]*"
                                   onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   required>
                            @error('codigo')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre" class="form-label-erp">Nombre del Programa</label>
                            <input type="text" 
                                   id="nombre"
                                   wire:model="nombre" 
                                   class="form-control-erp @error('nombre') is-invalid @enderror" 
                                   placeholder="Ej: Técnico en Sistemas" 
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bloque 2 - Clasificación -->
            <div class="section-block">
                <h6 class="section-title">Clasificación</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="red_conocimiento_id" class="form-label-erp">Red de Conocimiento</label>
                            <select id="red_conocimiento_id"
                                    wire:model="red_conocimiento_id" 
                                    class="form-control-erp @error('red_conocimiento_id') is-invalid @enderror" 
                                    required>
                                <option value="">Seleccione una red de conocimiento</option>
                                @foreach ($this->redesConocimiento as $red)
                                    <option value="{{ $red->id }}">
                                        {{ $red->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('red_conocimiento_id')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nivel_formacion_id" class="form-label-erp">Nivel de Formación</label>
                            <select id="nivel_formacion_id"
                                    wire:model="nivel_formacion_id" 
                                    class="form-control-erp @error('nivel_formacion_id') is-invalid @enderror" 
                                    required>
                                <option value="">Seleccione un nivel de formación</option>
                                @foreach ($this->nivelesFormacion as $nivel)
                                    <option value="{{ $nivel->id }}">
                                        {{ $nivel->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nivel_formacion_id')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bloque 3 - Duración -->
            <div class="section-block">
                <h6 class="section-title">Distribución de Horas</h6>
                <p class="section-subtitle">La suma de horas lectiva y productiva debe coincidir con el total.</p>
                
                <!-- Validación visual en tiempo real -->
                @if ($horas_totales && $horas_etapa_lectiva && $horas_etapa_productiva)
                    @if (($horas_etapa_lectiva + $horas_etapa_productiva) != $horas_totales)
                        <div class="alert alert-danger alert-sm mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Error:</strong> La suma de horas lectiva ({{ $horas_etapa_lectiva }}) + productiva ({{ $horas_etapa_productiva }}) = {{ $horas_etapa_lectiva + $horas_etapa_productiva }}h no coincide con el total ({{ $horas_totales }}h).
                        </div>
                    @else
                        <div class="alert alert-success alert-sm mb-3">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Correcto:</strong> La distribución de horas es válida.
                        </div>
                    @endif
                @endif
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="horas_totales" class="form-label-erp">Horas Totales</label>
                            <div class="input-group-erp">
                                <input type="number" 
                                       id="horas_totales"
                                       wire:model="horas_totales"
                                       class="form-control-erp @error('horas_totales') is-invalid @enderror"
                                       placeholder="Total" 
                                       min="1" 
                                       max="20000"
                                       maxlength="5"
                                       required>
                                <span class="input-group-text-erp">h</span>
                            </div>
                            @error('horas_totales')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="horas_etapa_lectiva" class="form-label-erp">Horas Lectiva</label>
                            <div class="input-group-erp">
                                <input type="number" 
                                       id="horas_etapa_lectiva"
                                       wire:model="horas_etapa_lectiva"
                                       class="form-control-erp @error('horas_etapa_lectiva') is-invalid @enderror"
                                       placeholder="Horas lectiva" 
                                       min="1" 
                                       required>
                                <span class="input-group-text-erp">h</span>
                            </div>
                            @error('horas_etapa_lectiva')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="horas_etapa_productiva" class="form-label-erp">Horas Productiva</label>
                            <div class="input-group-erp">
                                <input type="number" 
                                       id="horas_etapa_productiva"
                                       wire:model="horas_etapa_productiva"
                                       class="form-control-erp @error('horas_etapa_productiva') is-invalid @enderror"
                                       placeholder="Horas productiva" 
                                       min="1" 
                                       required>
                                <span class="input-group-text-erp">h</span>
                            </div>
                            @error('horas_etapa_productiva')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bloque 4 - Información Importante -->
            <div class="section-block section-info">
                <div class="info-content">
                    <i class="info-icon fas fa-info-circle"></i>
                    <div class="info-text">
                        <strong>Nota:</strong> Las competencias se podrán asociar o quitar desde la edición del programa una vez creado.
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
                    {{ $isEdit ? 'Actualizar' : 'Guardar' }} Programa
                </button>
            </div>
        </div>
    </form>
</div>
