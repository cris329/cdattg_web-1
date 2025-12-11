@extends('inventario.layouts.base')

@section('title', 'Órdenes Pendientes')

@include('inventario._components.common-css')

@section('content_header')
    <x-page-header
        icon="fas fa-hourglass-half"
        title="Órdenes Pendientes"
        subtitle="Órdenes en espera de aprobación"
        :breadcrumb="[
            ['label' => 'Inicio', 'url' => '#'],
            ['label' => 'Inventario', 'active' => true],
            ['label' => 'Órdenes', 'url' => route('inventario.ordenes.index')],
            ['label' => 'Pendientes', 'active' => true]
        ]"
    />
@endsection

@section('content')
    @include('inventario._components.ordenes-table', ['ordenes' => $ordenes, 'estado' => 'EN ESPERA'])
@endsection

@include('inventario._components.common-footer')
