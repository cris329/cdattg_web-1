@extends('inventario.layouts.base')

@section('plugins.Chartjs', true)

@section('title', 'Dashboard de Inventario')

@include('inventario._components.common-css')

@section('content_header')
    <x-page-header
        icon="fas fa-chart-bar"
        title="Dashboard de Inventario"
        subtitle="Resumen general del inventario"
        :breadcrumb="[
            ['label' => 'Inicio', 'url' => '/home'],
            ['label' => 'Inventario', 'active' => true],
            ['label' => 'Dashboard', 'active' => true]
        ]"
    />
@endsection

@section('content')
    <section class="content">
        <div class="container-fluid">
            @livewire(\App\Livewire\Inventario\DashboardInventario::class)
        </div>
    </section>
@endsection

@include('inventario._components.common-footer')
