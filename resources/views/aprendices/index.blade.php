@extends('adminlte::page')

@section('title', 'Aprendices')

@section('css')
    @vite(['resources/css/programas.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Aprendices</h1>
                    <p class="admin-header-subtitle">Gestiona y administra los aprendices del SENA</p>
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
                        <i class="fas fa-user-graduate me-1"></i>Aprendices
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="vista-programas">
        <div class="main-card">
            <livewire:aprendices.aprendiz-index />
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    @vite(['resources/js/pages/aprendices-index.js'])
@endsection
