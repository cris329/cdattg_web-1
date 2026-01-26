@extends('adminlte::page')

@section('title', 'Redes de Conocimiento')

@section('css')
    @vite(['resources/css/red-conocimiento.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="container-fluid">
            <div class="admin-header-content">
                <div class="admin-header-left">
                    <div class="admin-header-icon">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <div class="admin-header-text">
                        <h1 class="admin-header-title">Redes de Conocimiento</h1>
                        <p class="admin-header-subtitle">Gestiona y administra las redes de conocimiento del SENA</p>
                    </div>
                </div>
                <div class="admin-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item active">Redes de Conocimiento</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="vista-red-conocimiento">
        <div class="main-card">
            <x-session-alerts />
            
            <livewire:red-conocimiento.red-conocimiento-index />
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    @vite(['resources/js/pages/red-conocimiento-index.js'])
@endsection
