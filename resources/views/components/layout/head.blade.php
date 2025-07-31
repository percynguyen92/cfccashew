<!-- resources/views/components/layout/head.blade.php -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Quản lý thông tin điều thô' }}</title>
    
    <!-- Preload important assets -->
    <link rel="preload" href="{{ asset('css/app.css') }}" as="style">
    <link rel="preload" href="{{ asset('js/app.js') }}" as="script">
    
    <!-- Styles -->
    @vite(['resources/css/app.css'])
    
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