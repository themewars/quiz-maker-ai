<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- CSS Files -->
    @vite(['resources/css/app.css', 'resources/css/home.css', 'resources/js/app.js'])
    
    <!-- Bootstrap CSS -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
</head>
<body class="font-sans antialiased">
    <!-- Page Heading -->
    @if (isset($header))
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
                    @endif
                    
    <!-- Page Content -->
    @yield('content')

    <!-- Live Chat Widget -->
    @include('components.live-chat-widget')
    
    <!-- Bootstrap JS -->
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
</body>
</html>