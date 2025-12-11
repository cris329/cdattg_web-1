@extends('inventario.layouts.base')

@section('title', 'Órdenes Aprobadas')

@include('inventario._components.common-css')

@section('content_header')
    <x-page-header
        icon="fas fa-check-circle"
        title="Órdenes Aprobadas"
        subtitle="Órdenes completadas y aprobadas"
        iconBackground="bg-success"
        :breadcrumb="[
            ['label' => 'Inicio', 'url' => '#'],
            ['label' => 'Inventario', 'active' => true],
            ['label' => 'Órdenes', 'url' => route('inventario.ordenes.index')],
            ['label' => 'Aprobadas', 'active' => true]
        ]"
    />
@endsection

@section('content')
    @include('inventario._components.ordenes-table', ['ordenes' => $ordenes, 'estado' => 'APROBADA'])
@endsection

@include('inventario._components.common-footer')
