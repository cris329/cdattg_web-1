@extends('adminlte::page')

@section('title', 'Consultar Asistencias')

@section('css')
    @vite(['resources/css/fichas.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Consultar Asistencias</h1>
                    <p class="admin-header-subtitle">Consulta asistencias registradas por ficha e instructor</p>
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
                        <i class="fas fa-clipboard-check me-1"></i>Consultar Asistencias
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="vista-fichas">
        <div class="main-card">
            <livewire:asistencia.asistencia-consulta-index />
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection
