<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <script>
        window.App = {!! json_encode([
            'signedIn' => Auth::check(),
            'user' => Auth::user(),
        ]) !!}
    </script>
    <style>
        body { padding-bottom: 100px; }
        .level { display: flex; align-items: center;}
        .flex { flex: 1; }
        .level > * { margin-left: 1em; }
        .level > *:first-child { margin-left: 0;}
        .ml-a { margin-left:auto; }
        .ais-highlight em { background-color: yellow; font-style: normal}
        [v-cloak] { display: none; }
    </style>
    @yield('header')
</head>
<body>
    <div id="app">
        @include('layouts.nav')
        @yield('content')
        <flash message="{{ session('flash') }}"></flash>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
