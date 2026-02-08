<div class="modal-erp-container">
    {{-- Notificación Livewire --}}
    <div 
        x-data="{ show: false, message: '', type: '' }"
        x-on:notify.window="
            show = true; 
            message = $event.detail.message; 
            type = $event.detail.type; 
            setTimeout(() => show = false, 4000);
        "
        x-show="show"
        x-transition
        class="alert"
        :class="{
            'alert-success': type === 'success',
            'alert-danger': type === 'error',
            'alert-warning': type === 'warning'
        }"
        style="margin-bottom: 1rem;"
    >
        <span x-text="message"></span>
    </div>
    <form wire:submit="save">
        <!-- Contenido principal -->
        <div class="modal-body-erp">
            <!-- Información Básica -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="ficha_codigo" class="form-label fw-bold">Número de Ficha <span class="text-danger">*</span></label>
                        <input type="number" 
                               wire:model="ficha_codigo" 
                               id="ficha_codigo" 
                               class="form-control @error('ficha_codigo') is-invalid @enderror"
                               placeholder="Ej: 123456" 
                               maxlength="50" 
                               required>
                        @error('ficha_codigo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="programa_formacion_id" class="form-label fw-bold">Programa de Formación <span class="text-danger">*</span></label>
                        <select wire:model="programa_formacion_id" 
                                id="programa_formacion_id" 
                                class="form-control select2 @error('programa_formacion_id') is-invalid @enderror"
                                required>
                            <option value="">Seleccione un programa...</option>
                            @foreach($programas as $programa)
                                <option value="{{ $programa->id }}" {{ $programa_formacion_id == $programa->id ? 'selected' : '' }}>
                                    {{ $programa->nombre }} ({{ $programa->codigo }})
                                </option>
                            @endforeach
                        </select>
                        @error('programa_formacion_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Fechas -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="fecha_inicio" class="form-label fw-bold">Fecha de Inicio <span class="text-danger">*</span></label>
                        <input type="date" 
                               wire:model="fecha_inicio" 
                               id="fecha_inicio" 
                               class="form-control @error('fecha_inicio') is-invalid @enderror"
                               min="{{ \Carbon\Carbon::now()->subYears(2)->format('Y-m-d') }}"
                               required>
                        @error('fecha_inicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="fecha_fin" class="form-label fw-bold">Fecha de Fin <span class="text-danger">*</span></label>
                        <input type="date" 
                               wire:model="fecha_fin" 
                               id="fecha_fin" 
                               class="form-control @error('fecha_fin') is-invalid @enderror"
                               required>
                        @error('fecha_fin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Asignaciones -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="sede_id" class="form-label fw-bold">Sede <span class="text-danger">*</span></label>
                        <select wire:model="sede_id" 
                                id="sede_id" 
                                class="form-control select2 @error('sede_id') is-invalid @enderror"
                                required>
                            <option value="">Seleccione una sede...</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id }}" {{ $sede_id == $sede->id ? 'selected' : '' }}>
                                    {{ $sede->sede }}
                                </option>
                            @endforeach
                        </select>
                        @error('sede_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="instructor_id" class="form-label fw-bold">Instructor Líder <span class="text-danger">*</span></label>
                        <select wire:model="instructor_id" 
                                id="instructor_id" 
                                class="form-control select2 @error('instructor_id') is-invalid @enderror"
                                required>
                            <option value="">Seleccione un instructor...</option>
                            @foreach($instructores as $instructor)
                                <option value="{{ $instructor->id }}" {{ $instructor_id == $instructor->id ? 'selected' : '' }}>
                                    @if($instructor->persona)
                                        {{ $instructor->persona->primer_nombre }} {{ $instructor->persona->primer_apellido }}
                                        @if($instructor->persona->segundo_nombre) {{ $instructor->persona->segundo_nombre }} @endif
                                        @if($instructor->persona->segundo_apellido) {{ $instructor->persona->segundo_apellido }} @endif
                                    @else
                                        Instructor sin datos
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('instructor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Modalidad y Jornada -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="modalidad_formacion_id" class="form-label fw-bold">Modalidad de Formación <span class="text-danger">*</span></label>
                        <select wire:model="modalidad_formacion_id" 
                                id="modalidad_formacion_id" 
                                class="form-control select2 @error('modalidad_formacion_id') is-invalid @enderror"
                                required>
                            <option value="">Seleccione una modalidad...</option>
                            @foreach($modalidades as $modalidad)
                                <option value="{{ $modalidad->id }}" {{ $modalidad_formacion_id == $modalidad->id ? 'selected' : '' }}>
                                    {{ $modalidad->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('modalidad_formacion_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="jornada_id" class="form-label fw-bold">Jornada de Formación <span class="text-danger">*</span></label>
                        <select wire:model="jornada_id" 
                                id="jornada_id" 
                                class="form-control select2 @error('jornada_id') is-invalid @enderror"
                                required>
                            <option value="">Seleccione una jornada...</option>
                            @foreach($jornadas as $jornada)
                                <option value="{{ $jornada->id }}" {{ $jornada_id == $jornada->id ? 'selected' : '' }}>
                                    {{ $jornada->parametro->name ?? $jornada->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                        @error('jornada_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Ambiente y Total Horas -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="ambiente_id" class="form-label fw-bold">Ambiente <span class="text-danger">*</span></label>
                        <select wire:model="ambiente_id" 
                                id="ambiente_id" 
                                class="form-control select2 @error('ambiente_id') is-invalid @enderror"
                                required>
                            <option value="">Seleccione un ambiente...</option>
                            @foreach($ambientes as $ambiente)
                                <option value="{{ $ambiente->id }}" {{ $ambiente_id == $ambiente->id ? 'selected' : '' }}>
                                    {{ $ambiente->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('ambiente_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="total_horas" class="form-label fw-bold">Total de Horas <span class="text-danger">*</span></label>
                        <input type="number" 
                               wire:model="total_horas" 
                               id="total_horas" 
                               class="form-control @error('total_horas') is-invalid @enderror"
                               min="1" 
                               max="9999" 
                               placeholder="Ej: 120" 
                               required>
                        @error('total_horas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div> --}}
            </div>

            <!-- Días de Formación -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold">Días de Formación <span class="text-danger">*</span></label>
                        <div class="row" id="dias-formacion-container">
                            @php
                                $diasSemana = [
                                    ['id' => 12, 'nombre' => 'LUNES'],
                                    ['id' => 13, 'nombre' => 'MARTES'],
                                    ['id' => 14, 'nombre' => 'MIÉRCOLES'],
                                    ['id' => 15, 'nombre' => 'JUEVES'],
                                    ['id' => 16, 'nombre' => 'VIERNES'],
                                    ['id' => 17, 'nombre' => 'SÁBADO'],
                                    ['id' => 18, 'nombre' => 'DOMINGO'],
                                ];
                            @endphp
                            @foreach($diasSemana as $dia)
                                <div class="col-md-3 col-sm-4 col-6 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                                class="custom-control-input dia-formacion-checkbox"
                                                wire:model="dias_formacion"
                                                id="dia_{{ $dia['id'] }}" 
                                                value="{{ $dia['id'] }}">
                                        <label class="custom-control-label" for="dia_{{ $dia['id'] }}">
                                            {{ $dia['nombre'] }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('dias_formacion')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Estado -->
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group mb-3">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" 
                                    class="custom-control-input" 
                                    wire:model="status" 
                                    id="status" 
                                    value="1"
                                    {{ $status ? 'checked' : '' }}>
                            <label class="custom-control-label fw-bold" for="status">
                                Ficha Activa <span class="text-danger">*</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                <i class="fas fa-times"></i>
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                <i wire:loading.remove class="fas fa-save"></i>
                <span wire:loading.remove>{{ $isEdit ? 'Actualizar' : 'Crear' }} Ficha</span>
                <i wire:loading class="fas fa-spinner fa-spin"></i>
                <span wire:loading>{{ $isEdit ? 'Actualizando...' : 'Guardando...' }}</span>
            </button>
        </div>
    </form>
</div>
