@extends('adminlte::page')

@section('title', 'Crear Guía de Aprendizaje')

@section('css')
    @vite(['resources/css/guias-aprendizaje.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Crear Nueva Guía</h1>
                    <p class="admin-header-subtitle">Completa los datos para crear una nueva guía de aprendizaje</p>
                </div>
            </div>
            <nav aria-label="breadcrumb" class="admin-breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('verificarLogin') }}">
                            <i class="fas fa-home me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('guias-aprendizaje.index') }}">
                            <i class="fas fa-book-open me-1"></i>Guías de Aprendizaje
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-plus-circle me-1"></i>Crear Guía
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="main-card">
        <x-session-alerts />
        
        <livewire:guias-aprendizaje.guia-aprendizaje-form />
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    @vite(['resources/js/pages/guias-aprendizaje-index.js'])
@endsection

@if(session('success'))
    <script>
        $(document).ready(function() {
            Livewire.dispatch('notify', {
                type: 'success',
                message: '{{ session('success') }}'
            });
        });
    </script>
@endif

@if(session('error'))
    <script>
        $(document).ready(function() {
            Livewire.dispatch('notify', {
                type: 'error',
                message: '{{ session('error') }}'
            });
        });
    </script>
@endif
