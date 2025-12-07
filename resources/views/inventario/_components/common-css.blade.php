{{-- Componente reutilizable para CSS común del módulo de inventario --}}
{{-- El módulo de inventario NO usa parametros.css para evitar conflictos de estilos --}}
@push('css')
    @vite(['resources/css/inventario/shared/base.css'])
@endpush

@push('js')
    @vite('resources/js/inventario/global.js')
@endpush

