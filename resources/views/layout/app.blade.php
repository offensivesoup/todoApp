<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Todo List</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/common/css/app.css') }}">
</head>
<body>
    
    @include('layout.loading')

    <div class="container">
        
        @include('components.app-bar')

        <div class="d-flex justify-content-center" style="min-height: 100vh;">
            <div style="width: 100%; min-width: 320px;">
                @yield('content')
            </div>
        </div>
        
    </div>

    <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/common/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>