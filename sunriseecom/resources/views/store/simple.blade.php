<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto max-w-7xl px-6 py-12 sm:px-8">
        <h1 class="text-2xl font-medium">{{ $title }}</h1>
        <p class="mt-2 text-sm text-[#6f6a64]">{{ $message }}</p>
    </main>
    @include('partials.store-footer')
</body>
</html>
