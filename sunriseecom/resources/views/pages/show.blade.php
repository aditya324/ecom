<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>{{ $page['title'] }} — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto w-full max-w-3xl flex-1 px-6 py-12 sm:px-8">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sunrise Digital</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1a1a1a]">{{ $page['title'] }}</h1>
        <div class="mt-6 flex flex-col gap-4 text-sm leading-7 text-[#3a3632]">
            @foreach ($page['body'] as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    </main>

    @include('partials.store-footer')
</body>
</html>
