@props([
    'paises' => [],
    'departamentos' => [],
    'municipios' => [],
    'paisSeleccionado' => null,
    'municipioSeleccionado' => null,
    'departamentoSeleccionado' => null,
    'required' => false
])

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="pais_id">
                País
                @if($required)
                    <span class="text-danger">*</span>
                @endif
            </label>
            <select
                class="form-control @error('pais_id') is-invalid @enderror"
                id="pais_id"
                name="pais_id"
                {{ $required ? 'required' : '' }}
            >
                <option value="">Seleccione un país</option>
                @foreach($paises as $pais)
                    <option
                        value="{{ $pais->id }}"
                        {{ old('pais_id', $paisSeleccionado) == $pais->id ? 'selected' : '' }}
                    >
                        {{ $pais->pais }}
                    </option>
                @endforeach
            </select>
            @error('pais_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="departamento_id">
                Departamento
                @if($required)
                    <span class="text-danger">*</span>
                @endif
            </label>
            <select
                class="form-control @error('departamento_id') is-invalid @enderror"
                id="departamento_id"
                name="departamento_id"
                {{ $required ? 'required' : '' }}
            >
                <option value="">Seleccione un departamento</option>
                @if($paisSeleccionado)
                    @foreach($departamentos->where('pais_id', $paisSeleccionado) as $departamento)
                        <option
                            value="{{ $departamento->id }}"
                            {{ old('departamento_id', $departamentoSeleccionado) == $departamento->id ? 'selected' : '' }}
                        >
                            {{ $departamento->departamento }}
                        </option>
                    @endforeach
                @endif
            </select>
            @error('departamento_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="municipio_id">
                Municipio
                @if($required)
                    <span class="text-danger">*</span>
                @endif
            </label>
            <select
                class="form-control @error('municipio_id') is-invalid @enderror"
                id="municipio_id"
                name="municipio_id"
                {{ $required ? 'required' : '' }}
            >
                <option value="">Seleccione un municipio</option>
                @if($departamentoSeleccionado)
                    @foreach($municipios->where('departamento_id', $departamentoSeleccionado) as $municipio)
                        <option
                            value="{{ $municipio->id }}"
                            {{ old('municipio_id', $municipioSeleccionado) == $municipio->id ? 'selected' : '' }}
                        >
                            {{ $municipio->municipio }}
                        </option>
                    @endforeach
                @endif
            </select>
            @error('municipio_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paisSelect = document.getElementById('pais_id');
    const departamentoSelect = document.getElementById('departamento_id');
    const municipioSelect = document.getElementById('municipio_id');
    
    if (!paisSelect || !departamentoSelect || !municipioSelect) {
        return;
    }
    
    const departamentoSeleccionado = {{ old('departamento_id', $departamentoSeleccionado ?? 'null') }};
    const municipioSeleccionado = {{ old('municipio_id', $municipioSeleccionado ?? 'null') }};
    const departamentosRouteUrl = '{{ route("inventario.proveedores.departamentos", ["paisId" => "__ID__"]) }}';
    const municipiosRouteUrl = '{{ route("inventario.proveedores.municipios", ["departamentoId" => "__ID__"]) }}';
    
    // Función para cargar departamentos
    function cargarDepartamentos(paisId, selectedId = null) {
        // Limpiar selects dependientes
        departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
        municipioSelect.innerHTML = '<option value="">Seleccione un municipio</option>';
        departamentoSelect.disabled = true;
        municipioSelect.disabled = true;
        
        if (!paisId) {
            departamentoSelect.disabled = false;
            municipioSelect.disabled = false;
            return;
        }
        
        // Mostrar loading
        const loadingOption = document.createElement('option');
        loadingOption.value = '';
        loadingOption.textContent = 'Cargando departamentos...';
        loadingOption.disabled = true;
        loadingOption.selected = true;
        departamentoSelect.appendChild(loadingOption);
        
        // Hacer la petición AJAX
        const url = departamentosRouteUrl.replace('__ID__', paisId);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar departamentos');
            }
            return response.json();
        })
        .then(data => {
            // Limpiar el select
            departamentoSelect.innerHTML = '<option value="">Seleccione un departamento</option>';
            
            if (data && Array.isArray(data) && data.length > 0) {
                data.forEach(departamento => {
                    const option = document.createElement('option');
                    option.value = departamento.id;
                    option.textContent = departamento.departamento;
                    
                    // Si hay un departamento seleccionado previamente, seleccionarlo
                    if (selectedId && departamento.id == selectedId) {
                        option.selected = true;
                        // Cargar municipios del departamento seleccionado
                        cargarMunicipios(departamento.id, municipioSeleccionado);
                    }
                    
                    departamentoSelect.appendChild(option);
                });
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No hay departamentos disponibles';
                option.disabled = true;
                departamentoSelect.appendChild(option);
            }
            
            departamentoSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            departamentoSelect.innerHTML = '<option value="">Error al cargar departamentos</option>';
            departamentoSelect.disabled = false;
        });
    }
    
    // Función para cargar municipios
    function cargarMunicipios(departamentoId, selectedId = null) {
        // Limpiar el select de municipios
        municipioSelect.innerHTML = '<option value="">Seleccione un municipio</option>';
        municipioSelect.disabled = true;
        
        if (!departamentoId) {
            municipioSelect.disabled = false;
            return;
        }
        
        // Mostrar loading
        const loadingOption = document.createElement('option');
        loadingOption.value = '';
        loadingOption.textContent = 'Cargando municipios...';
        loadingOption.disabled = true;
        loadingOption.selected = true;
        municipioSelect.appendChild(loadingOption);
        
        // Hacer la petición AJAX
        const url = municipiosRouteUrl.replace('__ID__', departamentoId);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al cargar municipios');
            }
            return response.json();
        })
        .then(data => {
            // Limpiar el select
            municipioSelect.innerHTML = '<option value="">Seleccione un municipio</option>';
            
            if (data && Array.isArray(data) && data.length > 0) {
                data.forEach(municipio => {
                    const option = document.createElement('option');
                    option.value = municipio.id;
                    option.textContent = municipio.municipio;
                    
                    // Si hay un municipio seleccionado previamente, seleccionarlo
                    if (selectedId && municipio.id == selectedId) {
                        option.selected = true;
                    }
                    
                    municipioSelect.appendChild(option);
                });
            } else {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'No hay municipios disponibles';
                option.disabled = true;
                municipioSelect.appendChild(option);
            }
            
            municipioSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            municipioSelect.innerHTML = '<option value="">Error al cargar municipios</option>';
            municipioSelect.disabled = false;
        });
    }
    
    // Event listener para país
    paisSelect.addEventListener('change', function() {
        const paisId = this.value;
        cargarDepartamentos(paisId);
    });
    
    // Event listener para departamento
    departamentoSelect.addEventListener('change', function() {
        const departamentoId = this.value;
        cargarMunicipios(departamentoId);
    });
    
    // Si hay un país seleccionado al cargar, cargar los departamentos
    if (paisSelect.value) {
        cargarDepartamentos(paisSelect.value, departamentoSeleccionado);
    } else if (departamentoSelect.value) {
        // Si no hay país pero hay departamento, cargar municipios
        cargarMunicipios(departamentoSelect.value, municipioSeleccionado);
    }
});
</script>
@endpush
