@extends('adminlte::page')

@section('title', 'Instructores')

@section('css')
    @vite(['resources/css/instructores.css'])
@endsection

@section('content_header')
    <div class="admin-header">
        <div class="admin-header-content">
            <div class="admin-header-left">
                <div class="admin-header-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="admin-header-text">
                    <h1 class="admin-header-title">Instructores</h1>
                    <p class="admin-header-subtitle">Gestiona y administra los instructores del SENA</p>
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
                        <i class="fas fa-chalkboard-teacher me-1"></i>Instructores
                    </li>
                </ol>
            </nav>
        </div>
    </div>
@endsection

@section('content')
    <div class="vista-instructores">
        <div class="main-card">
            <x-session-alerts />
            
            <livewire:instructores.instructor-index />
        </div>
    </div>
@endsection

@section('footer')
    @include('layouts.footer')
@endsection

@section('js')
    @vite(['resources/js/pages/instructores-index.js'])
    
    <script>
    // Toast notifications system
    window.addEventListener('notify', function(event) {
        const { type, message } = event.detail;
        showToast(type, message);
    });

    // Livewire event listeners
    Livewire.on('notify', function(data) {
        showToast(data.type, data.message);
    });

    function showToast(type, message) {
        const toast = document.querySelector('.vista-instructores .toast.toast-minimal');
        const icon = toast.querySelector('.toast-icon');
        const text = toast.querySelector('.toast-text');
        
        // Set content
        text.textContent = message;
        
        // Set icon based on type
        icon.className = 'toast-icon fas';
        switch(type) {
            case 'success':
                icon.classList.add('fa-check-circle');
                toast.className = 'vista-instructores toast toast-minimal success';
                break;
            case 'error':
                icon.classList.add('fa-exclamation-circle');
                toast.className = 'vista-instructores toast toast-minimal error';
                break;
            case 'warning':
                icon.classList.add('fa-exclamation-triangle');
                toast.className = 'vista-instructores toast toast-minimal warning';
                break;
            case 'info':
            default:
                icon.classList.add('fa-info-circle');
                toast.className = 'vista-instructores toast toast-minimal info';
                break;
        }
        
        // Show toast
        toast.classList.add('show');
        
        // Hide after 4 seconds
        setTimeout(() => {
            toast.classList.remove('show');
        }, 4000);
    }

    // Initialize Select2 when Livewire updates
    document.addEventListener('livewire:initialized', () => {
        initializeSelect2();
    });

    document.addEventListener('livewire:updated', () => {
        initializeSelect2();
    });

    function initializeSelect2() {
        // Initialize Select2 for selects that are not already initialized
        $('.vista-instructores .form-control-erp[multiple]').not('.select2-hidden-accessible').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
        
        $('.vista-instructores .form-control-erp:not([multiple])').not('.select2-hidden-accessible').not('[wire\\:ignore]').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    // Handle modal close with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('.vista-instructores .modal-overlay');
            modals.forEach(modal => {
                if (modal.style.display !== 'none') {
                    Livewire.dispatch('closeModal');
                }
            });
        }
    });

    // Auto-focus search input
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('.vista-instructores .search-input');
        if (searchInput) {
            searchInput.focus();
        }
    });
    </script>
@endsection
