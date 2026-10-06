<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request a quote — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto w-full max-w-xl flex-1 px-6 py-10 sm:px-8">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">{{ $service->name }}</p>
        <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Request a custom quote</h1>
        <p class="mt-2 text-sm leading-6 text-[#6f6a64]">Tell Sunrise what you need. The reply comes back with a price for this service.</p>

        @if ($errors->any())
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">This request was not sent.</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('quotes.store', $service) }}" class="mt-6 rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-4">
                <div>
                    <label for="name" class="text-sm font-medium text-[#1a1a1a]">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', auth()->user()?->name) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                </div>
                <div>
                    <label for="email" class="text-sm font-medium text-[#1a1a1a]">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', auth()->user()?->email) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                    <p class="mt-1 text-xs text-[#8a8680]">Required. The reply is sent to this address.</p>
                </div>
                <div>
                    <label for="message" class="text-sm font-medium text-[#1a1a1a]">What you need</label>
                    <textarea id="message" name="message" rows="5" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">{{ old('message') }}</textarea>
                </div>
            </div>
            <button type="submit" class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-full bg-[#f5b400] text-sm font-semibold text-[#1a1a1a]">Send request</button>
            <a href="{{ route('services.show', $service) }}" class="mt-3 block text-center text-sm font-medium text-[#6f6a64] underline underline-offset-4">Back to the service</a>
        </form>
    </main>

    @include('partials.store-footer')
</body>
</html>
