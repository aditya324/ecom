<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Support — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto w-full max-w-xl flex-1 px-6 py-12">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sunrise Digital</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Support</h1>
        <p class="mt-2 text-sm leading-6 text-[#6f6a64]">For an order you already placed, include the order number. Sunrise replies by email.</p>

        @if (session('status'))
            <p class="mt-6 rounded-xl border border-[#ead89a] bg-[#fff8e6] px-4 py-3 text-sm font-medium text-[#1a1a1a]">{{ session('status') }}</p>
        @endif

        <form method="POST" action="{{ route('support.store') }}" class="mt-6 flex flex-col gap-4 rounded-2xl border border-[#ebe6df] bg-white p-6">
            @csrf
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Name</span>
                <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] px-3 text-sm">
                @error('name')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Email</span>
                <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] px-3 text-sm">
                @error('email')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Message</span>
                <textarea name="body" rows="5" required maxlength="2000" class="mt-1 w-full rounded-xl border border-[#ece7e0] px-3 py-2 text-sm">{{ old('body') }}</textarea>
                @error('body')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
            </label>
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#1a1a1a] px-5 text-sm font-semibold text-white">Send</button>
        </form>
    </main>

    @include('partials.store-footer')
</body>
</html>
