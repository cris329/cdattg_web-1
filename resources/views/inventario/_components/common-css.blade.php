{{-- Componente reutilizable para CSS común del módulo de inventario --}}
@section('css')
    <link href="{{ asset('css/parametros.css') }}" rel="stylesheet">
@endsection

@push('css')
    {{-- @vite(['resources/css/inventario/shared/base.css']) --}}
@endpush

