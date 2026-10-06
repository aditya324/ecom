<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>{{ $term === '' ? 'Search' : 'Search: '.$term }} — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto w-full max-w-[90.5vw] flex-1 px-6 py-8 sm:px-8">
        <h1 class="text-3xl font-semibold tracking-tight">Search</h1>
        @if ($term === '')
            <p class="mt-2 text-sm text-[#6f6a64]">Type a service name in the header.</p>
        @else
            <p class="mt-2 text-sm text-[#6f6a64]">{{ $services->count() }} {{ \Illuminate\Support\Str::plural('service', $services->count()) }} and {{ $plans->count() }} {{ \Illuminate\Support\Str::plural('package', $plans->count()) }} for “{{ $term }}”.</p>
        @endif

        @if ($term !== '' && $plans->isNotEmpty())
            <div class="mt-8 flex flex-col gap-3">
                @foreach ($plans as $plan)
                    <a href="{{ route('packages.show', $plan) }}" class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-[#b8860b]">Package</p>
                        <p class="mt-1 font-semibold text-[#1a1a1a]">{{ $plan->name }}</p>
                    </a>
                @endforeach
            </div>
        @endif

        @if ($term !== '' && $services->isEmpty() && $plans->isEmpty())
            <p class="mt-10 rounded-2xl bg-white px-6 py-16 text-center text-lg font-medium">No services match that search.</p>
        @elseif ($services->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-5">
                @foreach ($services as $service)
                    @include('partials.store-service-card', ['service' => $service])
                @endforeach
            </div>
        @endif
    </main>

    @include('partials.store-footer')
</body>
</html>
