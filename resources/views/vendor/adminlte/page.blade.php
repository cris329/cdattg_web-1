@extends('adminlte::master')

@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')
@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

@section('adminlte_css')
    @stack('css')
    @yield('css')
    <!-- Global Notifications CSS -->
    @vite(['resources/css/global-notifications.css'])
@stop

@section('classes_body', $layoutHelper->makeBodyClasses())

@section('body_data', $layoutHelper->makeBodyData())

@section('body')
    <!-- Notify Container Global -->
    <div id="notify-container"></div>
    
    <div class="wrapper">

        {{-- Preloader Animation (fullscreen mode) --}}
        @if ($preloaderHelper->isPreloaderEnabled())
            @include('adminlte::partials.common.preloader')
        @endif

        {{-- Top Navbar --}}
        @if ($layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.navbar.navbar-layout-topnav')
        @else
            @include('adminlte::partials.navbar.navbar')
        @endif

        {{-- Left Main Sidebar --}}
        @if (!$layoutHelper->isLayoutTopnavEnabled())
            @include('adminlte::partials.sidebar.left-sidebar')
        @endif

        {{-- Content Wrapper --}}
        @empty($iFrameEnabled)
            @include('adminlte::partials.cwrapper.cwrapper-default')
        @else
            @include('adminlte::partials.cwrapper.cwrapper-iframe')
        @endempty

        {{-- Footer --}}
        @include('adminlte::partials.footer.footer')

        {{-- Right Control Sidebar --}}
        @if ($layoutHelper->isRightSidebarEnabled())
            @include('adminlte::partials.sidebar.right-sidebar')
        @endif

    </div>
    
    {{-- Global Modals Container --}}
    @include('layouts.partials.global-modals')
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
    <!-- Global Notifications CSS -->
    @vite(['resources/css/global-notifications.css'])
    <!-- Global Modals CSS -->
    @vite(['resources/css/global-modals.css'])
    <!-- Footer CSS -->
    @vite(['resources/css/footer.css'])
    <!-- Global Notifications JS -->
    @vite(['resources/js/global-notifications.js'])
    <!-- Global Modals JS -->
    @vite(['resources/js/global-modals.js'])
@stop

