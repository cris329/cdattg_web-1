<div class="modal-erp-container">
    <form wire:submit="save">
        <!-- Contenido principal -->
        <div class="modal-body-erp">
            <!-- Bloque 1 - Asignación de Aprendiz -->
            <div class="section-block">
                <h6 class="section-title">Asignar Aprendiz</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="persona_id" class="form-label-erp required">Persona</label>
                            <select wire:model="persona_id" 
                            id="persona_id" 
                            class="form-control-erp {{ $errors->has('persona_id') ? 'is-invalid' : '' }}"
                            @if($isEdit) disabled @endif>
                                <option value="">Seleccione una persona</option>
                                @foreach($personas as $persona)
                                    <option value="{{ $persona->id }}" {{ $persona_id == $persona->id ? 'selected' : '' }}>
                                        {{ $persona->nombre_completo }} - {{ $persona->numero_documento }}
                                    </option>
                                @endforeach
                            </select>
                            @error('persona_id')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ficha_caracterizacion_id" class="form-label-erp required">Ficha de Caracterización</label>
                            <select wire:model="ficha_caracterizacion_id" 
                            id="ficha_caracterizacion_id" 
                            class="form-control-erp {{ $errors->has('ficha_caracterizacion_id') ? 'is-invalid' : '' }}"
                            required>
                                <option value="">Seleccione una ficha</option>
                                @foreach($fichas as $ficha)
                                    <option value="{{ $ficha->id }}" {{ $ficha_caracterizacion_id == $ficha->id ? 'selected' : '' }}>
                                        {{ $ficha->ficha }} - {{ $ficha->programaFormacion->nombre ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ficha_caracterizacion_id')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Ficha de caracterización a la que pertenecerá el aprendiz
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado" class="form-label-erp required">Estado</label>
                            <select wire:model="estado" 
                                    id="estado" 
                                    class="form-control-erp {{ $errors->has('estado') ? 'is-invalid' : '' }}"
                                    required>
                                <option value="1" {{ $estado == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ $estado == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback-erp">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="modal-footer-erp">
            <button type="button" class="btn-modal btn-secondary" wire:click="$dispatch('closeModal')">
                <i class="fas fa-times"></i>
                Cancelar
            </button>
            <button type="submit" class="btn-modal btn-primary" wire:loading.attr="disabled">
                <i wire:loading.remove class="fas fa-save"></i>
                <span wire:loading.remove>{{ $isEdit ? 'Actualizar' : 'Guardar' }} Aprendiz</span>
                <i wire:loading class="fas fa-spinner fa-spin"></i>
                <span wire:loading>{{ $isEdit ? 'Actualizando...' : 'Guardando...' }}</span>
            </button>
        </div>
    </form>
</div>
