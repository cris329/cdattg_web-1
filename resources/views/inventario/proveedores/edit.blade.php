@extends('inventario.layouts.base')

@section('title', 'Editar Proveedor')

@include('inventario._components.common-css')

@section('content_header')
    <x-page-header
        icon="fas fa-edit"
        title="Editar Proveedor"
        subtitle="Modificar datos del proveedor"
        :breadcrumb="[
            ['label' => 'Inicio', 'url' => '#'],
            ['label' => 'Inventario', 'active' => true],
            ['label' => 'Proveedores', 'url' => route('inventario.proveedores.index')],
            ['label' => 'Editar', 'active' => true]
        ]"
    />
@endsection

@section('content')
    <section class="content mt-4">
        <div class="container-fluid">
            <!-- Alertas -->
            @include('components.session-alerts')

            <div class="row">
                <div class="col-12">
                    <div class="card detail-card no-hover" style="display: flex; flex-direction: column;">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title m-0 font-weight-bold text-primary">
                                <i class="fas fa-info-circle mr-2"></i>
                                Información del Proveedor
                            </h5>
                        </div>

                        <div class="card-body" style="flex: 1;">
                            <form id="proveedor-form" action="{{ route('inventario.proveedores.update', $proveedor->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="proveedor">Nombre del Proveedor <span class="text-danger">*</span></label>
                                            <input
                                                type="text"
                                                class="form-control @error('proveedor') is-invalid @enderror"
                                                id="proveedor"
                                                name="proveedor"
                                                value="{{ old('proveedor', $proveedor->proveedor) }}"
                                                placeholder="Ingrese el nombre del proveedor"
                                                required
                                            >
                                            @error('proveedor')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nit">NIT</label>
                                            <input
                                                type="text"
                                                class="form-control @error('nit') is-invalid @enderror"
                                                id="nit"
                                                name="nit"
                                                value="{{ old('nit', $proveedor->nit) }}"
                                                placeholder="Ingrese el NIT del proveedor"
                                            >
                                            @error('nit')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Correo Electrónico</label>
                                            <input
                                                type="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                id="email"
                                                name="email"
                                                value="{{ old('email', $proveedor->email) }}"
                                                placeholder="Ingrese el correo electrónico"
                                            >
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="telefono">Teléfono</label>
                                            <input
                                                type="text"
                                                class="form-control @error('telefono') is-invalid @enderror"
                                                id="telefono"
                                                name="telefono"
                                                value="{{ old('telefono', $proveedor->telefono) }}"
                                                placeholder="Ingrese el teléfono"
                                            >
                                            @error('telefono')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="direccion">Dirección</label>
                                            <input
                                                type="text"
                                                class="form-control @error('direccion') is-invalid @enderror"
                                                id="direccion"
                                                name="direccion"
                                                value="{{ old('direccion', $proveedor->direccion) }}"
                                                placeholder="Ingrese la dirección"
                                            >
                                            @error('direccion')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Componente de filtro departamento-municipio --}}
                                @include('inventario._components.filtro-departamento', [
                                    'departamentos' => $departamentos,
                                    'municipios' => $municipios,
                                    'municipioSeleccionado' => $proveedor->municipio_id,
                                    'departamentoSeleccionado' => $proveedor->departamento_id
                                ])

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="contacto_nombre">Nombre del Contacto</label>
                                            <input
                                                type="text"
                                                class="form-control @error('contactos.0.nombre') is-invalid @enderror"
                                                id="contacto_nombre"
                                                name="contactos[0][nombre]"
                                                value="{{ old('contactos.0.nombre', $proveedor->contacto->nombre ?? '') }}"
                                                placeholder="Nombre del contacto"
                                            >
                                            @error('contactos.0.nombre')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="contacto_telefono">Teléfono del Contacto</label>
                                            <input
                                                type="text"
                                                class="form-control @error('contactos.0.telefono') is-invalid @enderror"
                                                id="contacto_telefono"
                                                name="contactos[0][telefono]"
                                                value="{{ old('contactos.0.telefono', $proveedor->contacto->telefono ?? '') }}"
                                                placeholder="Teléfono"
                                            >
                                            @error('contactos.0.telefono')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="contacto_email">Email del Contacto</label>
                                            <input
                                                type="email"
                                                class="form-control @error('contactos.0.email') is-invalid @enderror"
                                                id="contacto_email"
                                                name="contactos[0][email]"
                                                value="{{ old('contactos.0.email', $proveedor->contacto->email ?? '') }}"
                                                placeholder="Email"
                                            >
                                            @error('contactos.0.email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="estado_id">Estado</label>
                                            <select
                                                class="form-control @error('estado_id') is-invalid @enderror"
                                                id="estado_id"
                                                name="estado_id"
                                            >
                                                <option value="">Seleccione un estado</option>
                                                @foreach(
                                                    \App\Models\ParametroTema::with(['parametro','tema'])
                                                        ->whereHas('tema', fn($q) => $q->where('name', 'ESTADOS'))
                                                        ->where('status', 1)
                                                        ->get() as $estado
                                                )
                                                    <option value="{{ $estado->id }}" {{ old('estado_id', $proveedor->estado_id) == $estado->id ? 'selected' : '' }}>
                                                        {{ $estado->parametro->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('estado_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer bg-white py-3">
                            <div class="action-buttons">
                                <button type="submit" form="proveedor-form" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-save mr-1"></i> Guardar Cambios
                                </button>
                                <a href="{{ route('inventario.proveedores.index') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times mr-1"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@include('inventario._components.common-footer')

@push('scripts')
    @vite(['resources/js/inventario/filtro-departamento.js'])
    <script>
        // Pasar datos de municipios al JavaScript
        window.municipiosData = @json($municipios->map(function($m) {
            return [
                'id' => $m->id,
                'municipio' => $m->municipio,
                'departamento' => $m->departamento->departamento ?? ''
            ];
        }));

        // Inicializar filtro con el municipio seleccionado
        document.addEventListener('DOMContentLoaded', function() {
            initFiltroMunicipios({{ json_encode(old('municipio_id', $proveedor->municipio_id)) }});
        });
    </script>
@endpush
