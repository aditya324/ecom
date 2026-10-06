@php
    $count = count($services);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wishlist — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto w-full max-w-3xl flex-1 px-6 py-8 sm:px-8">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-4xl font-bold tracking-tight text-[#1a1a1a]">Wishlist</h1>
            <p class="rounded-full border border-[#ece7e0] bg-white px-4 py-1.5 text-sm text-[#6f6a64]">{{ $count }} {{ \Illuminate\Support\Str::plural('item', $count) }}</p>
        </div>

        @if (session('status'))
            <p class="mt-4 rounded-xl border border-[#ead89a] bg-[#fff8e6] px-4 py-3 text-sm font-medium text-[#1a1a1a]">{{ session('status') }}</p>
        @endif

        @if ($services === [])
            <p class="mt-8 text-sm text-[#6f6a64]">Your wishlist is empty.</p>
            <a href="{{ route('home') }}" class="mt-4 inline-flex h-11 items-center rounded-full bg-[#1a1a1a] px-5 text-sm font-semibold text-white">Browse services</a>
        @else
            <div class="mt-8 flex flex-col gap-4">
                @foreach ($services as $service)
                    <article class="rounded-2xl border border-[#f0ebe3] bg-white p-4 shadow-[0_8px_24px_rgba(28,28,28,0.04)] sm:p-5">
                        <div class="flex gap-4">
                            <a href="{{ route('services.show', $service) }}" class="h-20 w-28 shrink-0 overflow-hidden rounded-xl bg-[#ece7e0]">
                                @if ($service->image)
                                    <img src="{{ asset('assets/services/'.$service->image) }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </a>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h2 class="truncate text-base font-bold text-[#1a1a1a]">
                                            <a href="{{ route('services.show', $service) }}">{{ $service->name }}</a>
                                        </h2>
                                        <p class="mt-1 text-[11px] font-medium tracking-[0.12em] text-[#8a8680] uppercase">{{ $service->category?->name }}</p>
                                    </div>
                                    <p class="shrink-0 text-lg font-bold text-[#e0a100]">
                                        {{ $service->money($service->price) }}
                                        @if ($service->billingLabel())
                                            <span class="text-sm font-normal text-[#8a8680]">{{ $service->billingLabel() }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="mt-4 flex flex-wrap items-center gap-3">
                                    <form method="POST" action="{{ route('cart.store', $service) }}">
                                        @csrf
                                        @if ($service->durations() !== [])
                                            <input type="hidden" name="months" value="{{ $service->durations()[0]['months'] }}">
                                        @endif
                                        <button type="submit" class="inline-flex h-10 items-center rounded-full bg-[#f5b400] px-4 text-sm font-semibold text-[#1a1a1a]">Add to Cart</button>
                                    </form>
                                    <form method="POST" action="{{ route('wishlist.toggle', $service) }}">
                                        @csrf
                                        <button type="submit" class="text-sm font-medium text-[#e24b4b]">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </main>

    @include('partials.store-footer')
</body>
</html>
