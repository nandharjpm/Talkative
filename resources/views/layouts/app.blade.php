<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TalkSpace')</title>
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
     <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('js/chat.js') }}"></script>
    <link rel="stylesheet" href="{{ 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css' }}">
</head>
<body>
    @yield('content')
    <script src="{{ asset('js/chat.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
