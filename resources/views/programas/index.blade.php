@extends('adminlte::page')

@section('title', 'Programas de Formación')

@section('css')
    @vite(['resources/css/programas.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Programas de Formación</h1>
                    <p class="admin-header-subtitle">Gestiona y administra los programas de formación del SENA</p>
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
                        <i class="fas fa-graduation-cap me-1"></i>Programas
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="vista-programas">
        <div class="main-card">
            <x-session-alerts />
            
            <livewire:programas.programa-index />
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    @vite(['resources/js/pages/programas-index.js'])
@endsection