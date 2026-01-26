@extends('adminlte::page')

@section('title', 'Resultados de Aprendizaje')

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Resultados de Aprendizaje</h1>
                    <p class="admin-header-subtitle">Gestiona y administra los resultados de aprendizaje del SENA</p>
                </div>
            </div>
            <nav aria-label="breadcrumb" class="admin-breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('verificarLogin') }}">
                            <i class="fas fa-home me-1"></i>Inicio
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <i class="fas fa-graduation-cap me-1"></i>Resultados de Aprendizaje
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="vista-resultados-aprendizaje">
        <div class="main-card">
            <x-session-alerts />
            
            <livewire:resultados-aprendizaje.resultado-aprendizaje-index />
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    @vite(['resources/js/pages/resultados-aprendizaje-index.js'])
@endsection