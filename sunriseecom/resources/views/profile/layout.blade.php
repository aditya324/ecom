<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto grid w-full max-w-6xl flex-1 items-start gap-6 px-6 py-8 sm:px-8 md:grid-cols-[220px_minmax(0,1fr)]">
        <aside class="rounded-2xl border border-[#ebe6df] bg-white p-3 shadow-sm">
            <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Account</p>
            <a href="{{ route('profile.edit') }}" @class([
                'flex items-center rounded-xl px-3 py-2.5 text-sm font-medium',
                'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('profile.edit'),
                'text-[#1a1a1a] hover:bg-[#f7f4ef]' => ! request()->routeIs('profile.edit'),
            ])>Profile</a>
            <a href="{{ route('profile.orders') }}" @class([
                'mt-1 flex items-center rounded-xl px-3 py-2.5 text-sm font-medium',
                'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('profile.orders'),
                'text-[#1a1a1a] hover:bg-[#f7f4ef]' => ! request()->routeIs('profile.orders'),
            ])>Orders</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="flex w-full items-center rounded-xl px-3 py-2.5 text-left text-sm font-medium text-[#6f6a64] hover:bg-[#f7f4ef]">Log out</button>
            </form>
        </aside>

        <section>
            @if (session('status'))
                <p class="mb-4 rounded-xl border border-[#ead89a] bg-[#fff8e6] px-4 py-3 text-sm font-medium text-[#1a1a1a]">{{ session('status') }}</p>
            @endif
            @yield('content')
        </section>
    </main>

    @include('partials.store-footer')
</body>
</html>
