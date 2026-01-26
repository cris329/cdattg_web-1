{{-- CSS Globales para toda la aplicación - SOLO UNO GLOBAL --}}
{{-- CSS base de la aplicación --}}
@vite(['resources/css/style.css'])

{{-- Estilos adicionales que puedan existir --}}
@yield('global_styles')

<style>
/* Fix simple para AdminLTE - SOLO lo necesario */
.content-wrapper {
    min-height: auto !important;
}

.wrapper {
    min-height: unset !important;
    height: auto !important;
}
</style>
