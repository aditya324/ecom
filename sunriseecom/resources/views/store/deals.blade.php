<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>Today's Deals — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto w-full max-w-[90.5vw] flex-1 px-6 py-8 sm:px-8">
        <h1 class="text-3xl font-semibold tracking-tight">Today's Deals</h1>
        <p class="mt-2 text-sm text-[#6f6a64]">Every service currently on sale.</p>

        @if ($deals->isEmpty())
            <p class="mt-10 rounded-2xl bg-white px-6 py-16 text-center text-lg font-medium">No deals right now.</p>
        @else
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($deals as $deal)
                    @include('partials.store-deal-card', ['deal' => $deal])
                @endforeach
            </div>
        @endif
    </main>

    @include('partials.store-footer')
</body>
</html>
