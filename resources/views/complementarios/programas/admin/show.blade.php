@extends('adminlte::page')

@section('title', 'Detalles del Programa Complementario')

@section('css')
    @vite(['resources/css/parametros.css'])
@endsection

@section('content_header')
    <x-page-header icon="fa-graduation-cap" title="Detalles del Programa"
        subtitle="Información completa del programa complementario" :breadcrumb="[
            ['label' => 'Inicio', 'url' => route('verificarLogin'), 'icon' => 'fa-home'],
            [
                'label' => 'Programas Complementarios',
                'url' => route('complementarios-ofertados.index'),
                'icon' => 'fa-graduation-cap',
            ],
            ['label' => 'Detalles', 'icon' => 'fa-eye', 'active' => true],
        ]" />
@endsection

@section('content')
    <section class="content mt-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-xxl-10 mx-auto">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <a href="{{ route('complementarios-ofertados.index') }}"
                            class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                        </a>
                        <div>
                            <a href="{{ route('complementarios-ofertados.edit', $programa->id) }}"
                                class="btn btn-warning btn-sm">
                                <i class="fas fa-edit mr-1"></i> Editar
                            </a>
                        </div>
                    </div>

                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-header bg-gradient-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-graduation-cap mr-2"></i>{{ $programa->nombre }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Código</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-primary">{{ $programa->codigo }}</span>
                                </dd>

                                <dt class="col-sm-4">Descripción</dt>
                                <dd class="col-sm-8">{{ $programa->descripcion ?? 'N/A' }}</dd>

                                @if($programa->justificacion)
                                <dt class="col-sm-4">Justificación</dt>
                                <dd class="col-sm-8">{{ $programa->justificacion }}</dd>
                                @endif

                                @if($programa->requisitos_ingreso)
                                <dt class="col-sm-4">Requisitos de Ingreso</dt>
                                <dd class="col-sm-8">{{ $programa->requisitos_ingreso }}</dd>
                                @endif

                                <dt class="col-sm-4">Duración</dt>
                                <dd class="col-sm-8">{{ $programa->duracion }} horas</dd>

                                <dt class="col-sm-4">Cupos</dt>
                                <dd class="col-sm-8">
                                    <span class="font-weight-bold text-primary">{{ $programa->cupos }}</span>
                                </dd>

                                <dt class="col-sm-4">Modalidad</dt>
                                <dd class="col-sm-8">
                                    {{ $programa->modalidad->parametro->name ?? 'N/A' }}
                                </dd>

                                <dt class="col-sm-4">Jornada</dt>
                                <dd class="col-sm-8">
                                    {{ $programa->jornada->jornada ?? 'N/A' }}
                                </dd>

                                <dt class="col-sm-4">Ambiente</dt>
                                <dd class="col-sm-8">
                                    @if($programa->ambiente)
                                        {{ $programa->ambiente->title }}
                                        @if($programa->ambiente->piso)
                                            · {{ $programa->ambiente->piso->piso }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Estado</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-pill {{ $programa->badge_class }}">
                                        {{ $programa->estado_label }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Días de formación</dt>
                                <dd class="col-sm-8">
                                    @if($programa->diasFormacion && $programa->diasFormacion->count() > 0)
                                        @foreach($programa->diasFormacion as $dia)
                                            <span class="badge badge-info mr-1">
                                                {{ $dia->name ?? 'Día' }} 
                                                ({{ $dia->pivot->hora_inicio }} - {{ $dia->pivot->hora_fin }})
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No definidos</span>
                                    @endif
                                </dd>
                            </dl>

                            @if($programa->competencias && $programa->competencias->count() > 0)
                            <hr>
                            <h6 class="font-weight-bold mb-3">
                                <i class="fas fa-tasks mr-2"></i>Competencias
                            </h6>
                            <div class="row">
                                @foreach($programa->competencias as $competencia)
                                <div class="col-md-6 mb-2">
                                    <div class="card card-outline card-info">
                                        <div class="card-body p-2">
                                            <strong>{{ $competencia->codigo }}</strong> - {{ $competencia->nombre }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            @if($programa->raps && $programa->raps->count() > 0)
                            <hr>
                            <h6 class="font-weight-bold mb-3">
                                <i class="fas fa-check-circle mr-2"></i>Resultados de Aprendizaje (RAPs)
                            </h6>
                            <div class="row">
                                @foreach($programa->raps as $rap)
                                <div class="col-md-6 mb-2">
                                    <div class="card card-outline card-success">
                                        <div class="card-body p-2">
                                            <strong>{{ $rap->codigo }}</strong> - {{ $rap->nombre }}
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('complementarios-ofertados.index') }}"
                                    class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Volver
                                </a>
                                <div>
                                    <a href="{{ route('complementarios-ofertados.edit', $programa->id) }}"
                                        class="btn btn-warning">
                                        <i class="fas fa-edit mr-1"></i> Editar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

