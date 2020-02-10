<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @livewireStyles

    <!-- Turbolinks -->
    <script src="{{ mix('js/turbolinks.js') }}"></script>
</head>
<body class="h-screen antialiased leading-none bg-gray-500">
    <div class="container z-0 px-4 mx-auto mt-4">
        @yield('content')
    </div>

    @livewireScripts
    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/gh/livewire/alpine-plugin@v0.1.0/dist/livewire-alpine-plugin.js"></script>
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
