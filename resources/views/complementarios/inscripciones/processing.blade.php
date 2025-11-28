@extends('adminlte::page')

@section('title', 'Gestión de Aspirantes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>Procesamiento de Documentos</h1>
            <p class="text-muted">Subir documentos de identidad al sistema</p>
        </div>
    </div>
@stop

@section('css')
    {{-- Usando Vite --}}
    @vite('resources/css/complementario/procesar_documentos.css')
@stop

@section('content')
     <!-- Main Content -->
    <div class="main-content">

        <!-- Upload Form Section -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Formulario de Documento</h5>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Por favor corrija los siguientes errores:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <form action="{{ route('procesar-documentos.submit') }}" method="POST" enctype="multipart/form-data" id="documentForm">
                    @csrf

                    <div class="row">
                        <!-- Tipo de Documento -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_documento" class="form-label">
                                    <strong>Tipo de Documento *</strong>
                                </label>
                                <select class="form-control @error('tipo_documento') is-invalid @enderror"
                                        id="tipo_documento" name="tipo_documento" required>
                                    <option value="">Seleccione un tipo de documento</option>
                                    @foreach($tiposDocumento as $tipo)
                                        <option value="{{ $tipo->id }}"
                                            {{ old('tipo_documento') == $tipo->id ? 'selected' : '' }}>
                                            {{ $tipo->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tipo_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Número de Documento -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="numero_documento" class="form-label">
                                    <strong>Número de Documento *</strong>
                                </label>
                                <input type="text"
                                       class="form-control @error('numero_documento') is-invalid @enderror"
                                       id="numero_documento"
                                       name="numero_documento"
                                       value="{{ old('numero_documento') }}"
                                       placeholder="Ingrese el número de documento"
                                       required>
                                @error('numero_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- File Upload Section -->
                    <div class="form-group mt-4">
                        <label for="documento_identidad" class="form-label">
                            <strong>Documento de Identidad *</strong>
                        </label>

                        <div class="upload-area" id="uploadArea">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h5 id="uploadText">Arrastre y suelte el documento aquí</h5>
                            <p>o</p>
                            <button type="button" class="btn btn-primary" id="selectFileBtn">
                                Seleccionar Archivo
                            </button>
                            <input type="file"
                                   id="documento_identidad"
                                   name="documento_identidad"
                                   style="position: absolute; opacity: 0; width: 0.1px; height: 0.1px; overflow: hidden; z-index: -1;"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   required>
                            <div id="fileInfo" class="mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="fas fa-file"></i>
                                    <span id="fileName"></span>
                                    <span id="fileSize" class="text-muted"></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger ml-2" id="removeFileBtn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="mt-2 text-muted">Formatos aceptados: PDF, JPG, PNG (Máximo 5MB)</p>
                        </div>
                        @error('documento_identidad')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-upload"></i> Subir Documento
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-lg ml-2">
                            <i class="fas fa-redo"></i> Limpiar Formulario
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Information Section -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Información Importante</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Requisitos del Documento</h6>
                            <ul class="mb-0">
                                <li>El documento debe estar legible y en buen estado</li>
                                <li>Formatos aceptados: PDF, JPG, PNG</li>
                                <li>Tamaño máximo: 5MB</li>
                                <li>La imagen debe mostrar claramente todos los datos</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Consideraciones</h6>
                            <ul class="mb-0">
                                <li>Verifique que los datos ingresados coincidan con el documento</li>
                                <li>El documento será almacenado en Google Drive</li>
                                <li>El proceso puede tomar algunos segundos</li>
                                <li>No cierre la ventana durante la subida</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        (function() {
            let initialized = false;

            function initFileUpload() {
                // Evitar múltiples inicializaciones
                if (initialized) return;
                
                const uploadArea = document.getElementById('uploadArea');
                const fileInput = document.getElementById('documento_identidad');
                const selectFileBtn = document.getElementById('selectFileBtn');
                const fileInfo = document.getElementById('fileInfo');
                const fileName = document.getElementById('fileName');
                const fileSize = document.getElementById('fileSize');
                const removeFileBtn = document.getElementById('removeFileBtn');
                const uploadText = document.getElementById('uploadText');
                const submitBtn = document.getElementById('submitBtn');
                const form = document.getElementById('documentForm');

                // Verificar que todos los elementos existan
                if (!uploadArea || !fileInput || !selectFileBtn || !fileInfo || !fileName || 
                    !fileSize || !removeFileBtn || !uploadText || !submitBtn || !form) {
                    return false;
                }

                initialized = true;

                // Open file dialog when button is clicked
                selectFileBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    fileInput.click();
                }, true);

                // Handle file selection
                fileInput.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        handleFileSelection(this.files[0]);
                    }
                });

                // Drag and drop functionality
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, unhighlight, false);
                });

                function highlight() {
                    uploadArea.classList.add('highlight');
                }

                function unhighlight() {
                    uploadArea.classList.remove('highlight');
                }

                uploadArea.addEventListener('drop', function(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    if (files && files[0]) {
                        fileInput.files = files;
                        handleFileSelection(files[0]);
                    }
                });

                function handleFileSelection(file) {
                    // Validate file type
                    const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                    if (!validTypes.includes(file.type)) {
                        alert('Tipo de archivo no válido. Solo se permiten PDF, JPG y PNG.');
                        return;
                    }

                    // Validate file size (5MB)
                    const maxSize = 5 * 1024 * 1024; // 5MB in bytes
                    if (file.size > maxSize) {
                        alert('El archivo es demasiado grande. El tamaño máximo permitido es 5MB.');
                        return;
                    }

                    // Update UI
                    fileName.textContent = file.name;
                    fileSize.textContent = ` (${formatFileSize(file.size)})`;
                    fileInfo.style.display = 'block';
                    uploadText.textContent = 'Archivo seleccionado:';
                    uploadArea.classList.add('file-selected');
                }

                // Remove file
                removeFileBtn.addEventListener('click', function() {
                    fileInput.value = '';
                    fileInfo.style.display = 'none';
                    uploadText.textContent = 'Arrastre y suelte el documento aquí';
                    uploadArea.classList.remove('file-selected');
                });

                // Format file size
                function formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }

                // Form validation
                form.addEventListener('submit', function(e) {
                    const tipoDocumento = document.getElementById('tipo_documento').value;
                    const numeroDocumento = document.getElementById('numero_documento').value;
                    const documentoIdentidad = document.getElementById('documento_identidad').files[0];

                    if (!tipoDocumento || !numeroDocumento || !documentoIdentidad) {
                        e.preventDefault();
                        alert('Por favor complete todos los campos obligatorios.');
                        return;
                    }

                    // Show loading state
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';
                });

                return true;
            }

            // Función para intentar inicializar
            function tryInit() {
                if (!initFileUpload()) {
                    setTimeout(tryInit, 50);
                }
            }

            // Intentar inicializar en múltiples momentos
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    setTimeout(tryInit, 100);
                });
            } else {
                setTimeout(tryInit, 100);
            }

            // También intentar cuando todo esté completamente cargado
            window.addEventListener('load', function() {
                setTimeout(tryInit, 50);
            });
        })();
    </script>
@stop
