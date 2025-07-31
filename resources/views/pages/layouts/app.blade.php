<!-- resources/views/components/layout/app.blade.php -->
<!DOCTYPE html>
<html lang="vi" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Quản lý thông tin điều thô' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-base-100">
    <div id="app" class="min-h-screen">
        <!-- Header -->
        <x-layout.header :title="$title ?? 'Quản lý thông tin điều thô'" />
        
        <!-- Navigation Breadcrumb -->
        <x-layout.navigation :breadcrumbs="$breadcrumbs ?? []" />
        
        <!-- Main Content -->
        <main class="container mx-auto px-4 py-6">
            {{ $slot }}
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