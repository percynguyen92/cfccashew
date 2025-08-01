<!-- resources/views/layouts/master.blade.php -->
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản lý thông tin điều thô')</title>

    <!-- Preload important assets -->
    <link rel="preload" href="{{ asset('css/app.css') }}" as="style">
    <link rel="preload" href="{{ asset('js/app.js') }}" as="script">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Custom styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Loading skeleton */
        .skeleton {
            @apply animate-pulse bg-gray-200 rounded;
        }
        
        .skeleton-text {
            @apply h-4 bg-gray-200 rounded animate-pulse;
        }
    </style>
</head>
<body class="bg-base-100">
    <div id="app" class="min-h-screen">
        
        <!-- Navigation Breadcrumb -->
        <x-layout.navigation :breadcrumbs="$breadcrumbs ?? []" />
        
        <!-- Main Content -->
        <main class="container mx-auto px-4 py-6">
            @yield('content')
        </main>
        
        <!-- Loading Overlay -->
        <div x-data="{ loading: false }" 
             x-show="loading" 
             x-transition
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             style="display: none;">
            <x-shared.loading-spinner />
        </div>
        
        <!-- Alert Container -->
        <div id="alert-container" class="fixed top-4 right-4 z-40"></div>
    </div>
</body>
</html>