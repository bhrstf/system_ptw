<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PTW System') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Figtree', sans-serif !important; background-color: #f8fafc; overflow-x: hidden; }

        /* ===== MAIN WRAPPER (ANTI NUMPUK) ===== */
        #mainWrapper { 
            margin-left: 260px !important; 
            width: calc(100% - 260px) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            min-height: 100vh;
            position: relative;
        }

        /* Geser Konten Saat Sidebar Diciutkan */
        #sidebar.collapsed + #mainWrapper {
            margin-left: 85px !important;
            width: calc(100% - 85px) !important;
        }

        @media (max-width: 991.98px) {
            #mainWrapper { margin-left: 0 !important; width: 100% !important; }
            .sidebar-overlay {
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.5); z-index: 1040; display: none;
            }
            .sidebar-overlay.show { display: block; }
        }
    </style>
    @livewireStyles
</head>
<body class="antialiased">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="d-flex">
        @include('layouts.sidebar')

        <div id="mainWrapper">
            <div class="p-3">
                @include('layouts.header')
            </div>

            <main class="px-4 pb-5">
                @if (isset($slot)) {{ $slot }} @else @yield('content') @endif
            </main>
        </div>
    </div>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (btn && sidebar) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (window.innerWidth > 991.98) {
                        sidebar.classList.toggle('collapsed');
                    } else {
                        sidebar.classList.toggle('active');
                        overlay.classList.toggle('show');
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html>