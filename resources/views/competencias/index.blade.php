@extends('adminlte::page')

@section('title', 'Competencias')

@section('css')
    @vite(['resources/css/competencias.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Competencias</h1>
                    <p class="admin-header-subtitle">Gestiona y administra las competencias del SENA</p>
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
                        <i class="fas fa-clipboard-list me-1"></i>Competencias
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="vista-competencias">
        <div class="main-card">
            <x-session-alerts />
            
            <livewire:competencias.competencia-index />
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    @vite(['resources/js/pages/competencias-index.js'])
@endsection
