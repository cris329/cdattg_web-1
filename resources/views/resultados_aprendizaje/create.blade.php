<div class="row">
    <div class="col-12">
        <form method="POST" action="{{ route('resultados-aprendizaje.store') }}" class="needs-validation" novalidate>
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="codigo" class="form-label fw-bold">Código <span class="text-danger">*</span></label>
                        <input type="number" name="codigo" id="codigo" value="{{ old('codigo') }}"
                            class="form-control @error('codigo') is-invalid @enderror" placeholder="Ej: RAP-001" required>
                        @error('codigo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Código único de identificación</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="nombre" class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                            class="form-control @error('nombre') is-invalid @enderror"
                            placeholder="Nombre del resultado de aprendizaje" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Nombre descriptivo del RAP</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="competencia_id" class="form-label fw-bold">Competencia Asociada</label>
                        <select name="competencia_id" id="competencia_id"
                            class="form-control @error('competencia_id') is-invalid @enderror">
                            <option value="">Seleccione una competencia (opcional)</option>
                            @foreach($competencias as $competencia)
                                <option value="{{ $competencia->id }}"
                                    {{ old('competencia_id') == $competencia->id ? 'selected' : '' }}>
                                    {{ $competencia->codigo ?? '' }} - {{ $competencia->nombre }}
                                </option>
                            @endforeach
                        </select>
                        @error('competencia_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="status" class="form-label fw-bold">Estado</label>
                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        Los resultados de aprendizaje se podrán modificar posteriormente, incluyendo la asociación con competencias.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Resultado
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
