@extends('adminlte::page')

@section('plugins.Chartjs', true)

@section('title', 'Estadísticas')

@section('content_header')
    <h1 class="mb-1"><i class="fas fa-chart-bar me-2"></i>Estadísticas</h1>
    <p class="text-muted mb-3">Panel de visualización de estadísticas del sistema</p>
@stop

@section('content')
    @livewire('complementarios.estadisticas-complementarios')
@stop

@section('css')
    <style>
        .card-header strong {
            font-size: 1rem;
        }
    </style>
@stop
