@extends('adminlte::page')

@section('css')
    @vite(['resources/css/parametros.css'])
@endsection

@section('title', 'Importar catálogo de complementarios')

@section('content_header')
    <x-page-header icon="fa-file-excel" title="Importar catálogo de programas complementarios"
        subtitle="Carga el archivo Excel oficial del SENA para actualizar el catálogo interno" :breadcrumb="[
            ['label' => 'Inicio', 'url' => route('verificarLogin'), 'icon' => 'fa-home'],
            ['label' => 'Complementarios', 'url' => route('complementarios-ofertados.index'), 'icon' => 'fa-graduation-cap'],
            ['label' => 'Importar catálogo', 'icon' => 'fa-file-excel', 'active' => true],
        ]" />
@endsection

@section('content')
    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-lg-8 col-xl-7 mx-auto">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-file-excel mr-2"></i>Archivo de catálogo
                            </h3>
                            <span class="badge bg-success text-uppercase">
                                Máx. {{ $maxFileSizeMb }}MB
                            </span>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ session('success') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ session('error') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST"
                                action="{{ route('complementarios-ofertados.catalogo.import.store') }}"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="form-group">
                                    <label for="archivo_catalogo" class="form-label">
                                        Archivo Excel del catálogo oficial
                                    </label>
                                    <input type="file" name="archivo_catalogo" id="archivo_catalogo"
                                        class="form-control @error('archivo_catalogo') is-invalid @enderror"
                                        accept=".xlsx,.xls" required>
                                    <small class="form-text text-muted">
                                        Selecciona el archivo Excel descargado desde la plataforma oficial del SENA.
                                        Solo se importarán los registros con
                                        <strong>NIVEL DE FORMACION = "CURSO ESPECIAL"</strong>.
                                    </small>
                                    @error('archivo_catalogo')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="alert alert-info small">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Si un programa ya existe en el catálogo interno, solo se actualizará cuando la
                                    <strong>versión del Excel</strong> sea superior a la versión almacenada.
                                    No se mantiene historial de versiones.
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('complementarios-ofertados.index') }}"
                                        class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-cloud-upload-alt mr-1"></i> Importar catálogo
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection


