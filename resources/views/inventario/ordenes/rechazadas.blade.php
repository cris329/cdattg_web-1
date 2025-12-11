@extends('inventario.layouts.base')

@section('title', 'Órdenes Rechazadas')

@include('inventario._components.common-css')

@section('content_header')
    <x-page-header
        icon="fas fa-times-circle"
        title="Órdenes Rechazadas"
        subtitle="Órdenes rechazadas o canceladas"
        iconBackground="bg-danger"
        :breadcrumb="[
            ['label' => 'Inicio', 'url' => '#'],
            ['label' => 'Inventario', 'active' => true],
            ['label' => 'Órdenes', 'url' => route('inventario.ordenes.index')],
            ['label' => 'Rechazadas', 'active' => true]
        ]"
    />
@endsection

@section('content')
    @include('inventario._components.ordenes-table', ['ordenes' => $ordenes, 'estado' => 'RECHAZADA'])
@endsection

@include('inventario._components.common-footer')
