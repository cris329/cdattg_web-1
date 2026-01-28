@extends('adminlte::page')

@section('title', 'Fichas de Caracterización')

@section('css')
    @vite(['resources/css/fichas.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Fichas de Caracterización</h1>
                    <p class="admin-header-subtitle">Gestiona y administra las fichas de caracterización del SENA</p>
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
                        <i class="fas fa-file-alt me-1"></i>Fichas de Caracterización
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="vista-fichas">
        <div class="main-card">
            <livewire:fichas.ficha-index />
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    @vite(['resources/js/pages/fichas-index.js'])
@endsection
