<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MedMeets')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<x-ui.toast-stack />

<main class="auth-shell">
    <x-ui.animated-auth-background />
    <section class="auth-card">
        @yield('content')
    </section>
</main>
<script src="{{ asset('js/toast.js') }}" defer></script>
<script src="{{ asset('js/file-upload.js') }}" defer></script>
</body>
</html>
