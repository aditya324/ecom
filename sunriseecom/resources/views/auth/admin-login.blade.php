<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>Admin log in — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-md items-center px-4 py-8">
        <div class="w-full">
            <h1 class="text-3xl font-semibold tracking-tight text-[#1a1a1a]">Admin log in</h1>
            <p class="mt-2 text-sm text-[#8a8680]">Staff access for Sunrise</p>

            @if ($errors->any())
                <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</p>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 flex flex-col gap-4">
                @csrf

                <div>
                    <label class="sr-only" for="email">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        placeholder="Email"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full rounded-lg border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none placeholder:text-[#b0aaa3] focus:border-[#f5b400]"
                    >
                </div>

                <div>
                    <label class="sr-only" for="password">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none placeholder:text-[#b0aaa3] focus:border-[#f5b400]"
                    >
                </div>

                <button type="submit" class="w-full rounded-lg bg-[#f5b400] px-4 py-3 text-sm font-medium text-white hover:bg-[#e5a800]">
                    Log in
                </button>
            </form>
        </div>
    </main>
</body>
</html>
