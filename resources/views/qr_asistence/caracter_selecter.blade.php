@extends('adminlte::page')

@section('title', 'Fichas de formación')

@section('css')
    @vite(['resources/css/Asistencia/caracter_selecter.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Fichas de formación</h1>
                    <p class="admin-header-subtitle">Gestión de fichas de formación</p>
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
                        <i class="fas fa-file-alt me-1"></i>Fichas de formación
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <livewire:asistencia.crear-evidencia-modal />
    
    <x-session-alerts />

    <div class="vista-asistencia-selector">
        <div class="main-card">
            <section class="content">
                <div class="container-fluid">
                    @if (empty($instructorFicha) || $instructorFicha->isEmpty())
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="empty-state">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <h3>No tienes fichas asignadas</h3>
                                    <p>No se encontraron fichas de formación asignadas a tu cuenta.</p>
                                    <p>Contacta al administrador para que te asigne las fichas correspondientes.</p>
                                    <a href="{{ route('verificarLogin') }}" class="btn-primary-modern">
                                        <i class="fas fa-home mr-2"></i>Volver al inicio
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="toolbar">
                            <div class="search-container">
                                <i class="fas fa-search search-icon"></i>
                                <input
                                    type="text"
                                    id="ficha-search-input"
                                    class="search-input"
                                    placeholder="Buscar por programa o número de ficha...">
                            </div>
                        </div>

                        <div id="no-results" class="empty-state" style="display: none; padding: 2rem 1rem;">
                            <i class="fas fa-search"></i>
                            <h3>No se encontraron resultados</h3>
                            <p>Intenta con otro nombre de programa o número de ficha.</p>
                        </div>

                        <div class="cards-grid">
                            @foreach ($instructorFicha as $caracterizacion)
                                <div class="card-modern" data-programa="{{ $caracterizacion->ficha->programaFormacion->nombre ?? '' }}" data-ficha="{{ $caracterizacion->ficha->ficha ?? '' }}">
                                    <div class="card-header-gradient">
                                        <div class="header-text">
                                            <h3 class="card-title">
                                                {{ $caracterizacion->ficha->programaFormacion->nombre }}
                                                <span class="ficha-codigo">Ficha {{ $caracterizacion->ficha->ficha }}</span>
                                            </h3>
                                        </div>
                                        <div class="modalidad-badge">
                                            {{ $caracterizacion->ficha->modalidadFormacion->name ?? 'Presencial' }}
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Sección: Información Académica -->
                                        <div class="info-section">
                                            <div class="section-title">
                                                <i class="fas fa-graduation-cap section-icon"></i>
                                                Información Académica
                                            </div>
                                            <div class="info-item">
                                                <i class="far fa-sun info-icon"></i>
                                                <div>
                                                    <div class="info-label">Jornada</div>
                                                    <div class="info-value">
                                                        {{ $caracterizacion->ficha->jornadaFormacion->parametro->name ?? 'No asignada' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="info-item">
                                                <i class="fas fa-map-marker-alt info-icon"></i>
                                                <div>
                                                    <div class="info-label">Sede / Ambiente</div>
                                                    <div class="info-value">
                                                        {{ $caracterizacion->ficha->sede->sede ?? 'No asignada' }} / 
                                                        {{ $caracterizacion->ficha->ambiente->title ?? 'No asignado' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sección: Instructor -->
                                        <div class="info-section">
                                            <div class="section-title">
                                                <i class="fas fa-chalkboard-teacher section-icon"></i>
                                                Instructor Líder
                                            </div>
                                            <div class="info-item">
                                                <i class="fas fa-user info-icon"></i>
                                                <div>
                                                    <div class="info-value">
                                                        @if ($caracterizacion->ficha->instructor && $caracterizacion->ficha->instructor->persona)
                                                            {{ $caracterizacion->ficha->instructor->persona->primer_nombre }}
                                                            {{ $caracterizacion->ficha->instructor->persona->primer_apellido }}
                                                        @else
                                                            <span class="badge-modern badge-secondary">Sin asignar</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer de acciones -->
                                        <div class="card-actions">
                                            <div class="aprendices-badge">
                                                <i class="fas fa-users"></i>
                                                <span class="badge-number">{{ $caracterizacion->ficha->aprendices->count() ?? 0 }}</span>
                                                <span>Aprendices</span>
                                            </div>
                                            <button onclick="Livewire.dispatch('openModalEvidencia', { fichaId: {{ $caracterizacion->id }} })"
                                                class="btn-primary-modern">
                                                <i class="fas fa-qrcode"></i>
                                                Tomar Asistencia
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('ficha-search-input');
            const cards = document.querySelectorAll('.cards-grid .card-modern');
            const noResults = document.getElementById('no-results');

            if (!input) return;

            function applyFilter() {
                const q = (input.value || '').trim().toLowerCase();
                let visible = 0;

                cards.forEach(card => {
                    const programa = (card.getAttribute('data-programa') || '').toLowerCase();
                    const ficha = (card.getAttribute('data-ficha') || '').toLowerCase();
                    const match = q === '' || programa.includes(q) || ficha.includes(q);
                    card.style.display = match ? '' : 'none';
                    if (match) visible++;
                });

                if (noResults) {
                    noResults.style.display = (cards.length > 0 && visible === 0) ? '' : 'none';
                }
            }

            input.addEventListener('input', applyFilter);
            applyFilter();
        });
    </script>
@endsection