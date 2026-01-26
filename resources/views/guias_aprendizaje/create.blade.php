@extends('adminlte::page')

@section('plugins.Select2', true)

@section('css')
    @vite(['resources/css/guias_aprendizaje.css'])
    <style>
        .dashboard-header {
            background: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, .05);
            box-shadow: 0 2px 4px rgba(0, 0, 0, .03);
        }
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        .link_right_header {
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .link_right_header:hover {
            color: #4299e1;
        }
        .breadcrumb-item {
            font-size: 0.9rem;
            display: flex;
            align-items: center;
        }
        .breadcrumb-item i {
            font-size: 0.8rem;
            margin-right: 0.4rem;
        }
        .breadcrumb-item a {
            color: #4a5568;
            text-decoration: none;
        }
        .breadcrumb-item.active {
            color: #718096;
        }
        .form-section { margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e3e6f0; }
        .form-section:last-child { border-bottom: none; }
        .form-section-title { color: #4e73df; font-size: 1.1rem; margin-bottom: 1rem; font-weight: 600; }
        
        /* Estilos Select2 Mejorados e Interactivos */
        .select2-container--bootstrap4 {
            width: 100% !important;
        }
        
        /* Contenedor principal con mejor diseño - permite wrap */
        .select2-container--bootstrap4 .select2-selection--multiple {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            min-height: 38px !important;
            padding: 4px 6px !important;
            background-color: #fff !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
            cursor: text !important;
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: flex-start !important;
        }
        
        /* Asegurar que el contenedor crezca en altura */
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex-wrap: wrap !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        
        /* Efecto hover */
        .select2-container--bootstrap4 .select2-selection--multiple:hover {
            border-color: #adb5bd !important;
        }
        
        /* Focus mejorado - minimalista */
        .select2-container--bootstrap4.select2-container--focus .select2-selection--multiple {
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
            outline: 0 !important;
        }
        
        /* Tags seleccionados - diseño minimalista */
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            background-color: #e9ecef !important;
            border: 1px solid #dee2e6 !important;
            color: #495057 !important;
            border-radius: 4px !important;
            padding: 3px 8px 3px 6px !important;
            margin: 2px 4px 2px 0 !important;
            font-size: 0.875rem !important;
            font-weight: 400 !important;
            display: inline-flex !important;
            align-items: center !important;
            transition: background-color 0.15s ease !important;
            line-height: 1.5 !important;
        }
        
        /* Hover en tags - minimalista */
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice:hover {
            background-color: #dee2e6 !important;
            border-color: #ced4da !important;
        }
        
        /* Botón eliminar en tags - minimalista */
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
            color: #6c757d !important;
            margin-right: 4px !important;
            cursor: pointer !important;
            font-weight: 600 !important;
            opacity: 0.7 !important;
            transition: opacity 0.15s ease !important;
            border: none !important;
            padding: 0 !important;
            font-size: 1rem !important;
        }
        
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover {
            opacity: 1 !important;
            color: #dc3545 !important;
        }
        
        /* Campo de búsqueda mejorado - permite wrap */
        .select2-container--bootstrap4 .select2-search--inline {
            flex: 1 1 auto !important;
            min-width: 150px !important;
        }
        
        .select2-container--bootstrap4 .select2-search--inline .select2-search__field {
            border: none !important;
            outline: none !important;
            padding: 4px 6px !important;
            margin: 2px 0 !important;
            background: transparent !important;
            color: #495057 !important;
            font-size: 0.875rem !important;
            width: 100% !important;
            min-width: 150px !important;
        }
        
        .select2-container--bootstrap4 .select2-search--inline .select2-search__field::placeholder {
            color: #6c757d !important;
            font-style: italic !important;
        }
        
        /* Dropdown mejorado */
        .select2-container--bootstrap4 .select2-results__option {
            padding: 10px 15px !important;
            transition: all 0.2s ease !important;
        }
        
        .select2-container--bootstrap4 .select2-results__option--highlighted {
            background-color: #f8f9fa !important;
            color: #212529 !important;
        }
        
        .select2-container--bootstrap4 .select2-results__option[aria-selected="true"] {
            background-color: #e9ecef !important;
            color: #495057 !important;
            font-weight: 500 !important;
        }
        
        /* Contador de seleccionados */
        .select2-selection__choice__display {
            position: relative;
        }
        
        /* Mejora visual del placeholder */
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__placeholder {
            color: #6c757d !important;
            line-height: 35px !important;
            padding-left: 5px !important;
        }
        
        /* Animación al abrir dropdown */
        .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 0.375rem !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
            animation: slideDown 0.3s ease !important;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Contador de seleccionados personalizado */
        .resultados-counter {
            display: inline-block;
            margin-left: 10px;
            padding: 2px 8px;
            background: #e7f3ff;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #0056b3;
        }
    </style>
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Crear Nueva Guía</h1>
                    <p class="admin-header-subtitle">Completa los datos para crear una nueva guía de aprendizaje</p>
                                                <small class="form-text text-muted">Estado inicial de la guía</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botones de Acción -->
                                <hr class="mt-4">
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('guias-aprendizaje.index') }}" class="btn btn-light mr-2">
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i>Guardar Guía
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

@section('js')
    @vite(['resources/js/pages/guias-aprendizaje-form.js'])
    
    <script>
        // Esperar a que jQuery y Select2 estén disponibles
        function initSelect2() {
            if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
                setTimeout(initSelect2, 100);
                return;
            }
            
            const $ = jQuery;
            
            $(document).ready(function() {
                const $select = $('#resultados_aprendizaje');
                
                if ($select.length === 0) {
                    console.error('Select #resultados_aprendizaje no encontrado');
                    return;
                }
                
                // Inicializar Select2
                $select.select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: 'Escriba para buscar o haga clic para seleccionar...',
                    allowClear: true,
                    closeOnSelect: false,
                    minimumResultsForSearch: 0,
                    language: {
                        noResults: function() {
                            return '<i class="fas fa-search mr-2"></i>No se encontraron resultados. Intente con otros términos.';
                        },
                        searching: function() {
                            return '<i class="fas fa-spinner fa-spin mr-2"></i>Buscando...';
                        },
                        inputTooShort: function() {
                            return 'Escriba al menos un carácter para buscar';
                        }
                    }
                });
                
                // Función para actualizar contador
                function updateCounter() {
                    try {
                        const selected = $select.val() || [];
                        const count = selected.length;
                        const $counter = $('#resultados-counter');
                        const $counterNumber = $('#counter-number');
                        
                        if ($counter.length && $counterNumber.length) {
                            if (count > 0) {
                                $counterNumber.text(count);
                                $counter.fadeIn(300);
                            } else {
                                $counter.fadeOut(300);
                            }
                        }
                    } catch (e) {
                        console.error('Error en updateCounter:', e);
                    }
                }
                
                // Actualizar contador al cambiar selección
                $select.on('select2:select select2:unselect select2:clear', function() {
                    try {
                        updateCounter();
                    } catch (e) {
                        console.error('Error actualizando contador:', e);
                    }
                });
                
                // Inicializar contador si hay valores pre-seleccionados
                updateCounter();
                
                
                // Inicializar tooltips
                if ($.fn.tooltip) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
                
                console.log('Select2 inicializado correctamente para resultados_aprendizaje');
            });
        }
        
        // Iniciar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSelect2);
        } else {
            initSelect2();
        }
    </script>
@endsection
