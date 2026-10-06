<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>Reset password — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-6xl items-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid w-full items-center gap-8 lg:grid-cols-2 lg:gap-16">
            <div class="overflow-hidden rounded-3xl">
                <img
                    src="{{ asset('assets/auth/login-banner.webp') }}"
                    alt="Person working on a tablet at a warmly lit desk"
                    class="h-72 w-full object-cover sm:h-96 lg:h-[640px]"
                >
            </div>

            <div class="mx-auto w-full max-w-md py-4 lg:py-8">
                <h1 class="text-3xl font-semibold tracking-tight text-[#1a1a1a] sm:text-4xl">Reset password</h1>
                <p class="mt-2 text-sm text-[#8a8680]">Choose a new password for your account.</p>

                @if ($errors->any())
                    <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</p>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div>
                        <label class="sr-only" for="email_display">Email</label>
                        <input
                            id="email_display"
                            type="email"
                            value="{{ $email }}"
                            readonly
                            class="w-full rounded-lg border border-[#e4e0da] bg-white px-4 py-3 text-sm text-[#8a8680] outline-none"
                        >
                    </div>

                    <div>
                        <label class="sr-only" for="password">New password</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="New password"
                            required
                            autofocus
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none placeholder:text-[#b0aaa3] focus:border-[#f5b400]"
                        >
                    </div>

                    <div>
                        <label class="sr-only" for="password_confirmation">Confirm password</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            placeholder="Confirm password"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none placeholder:text-[#b0aaa3] focus:border-[#f5b400]"
                        >
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-[#f5b400] px-4 py-3 text-sm font-medium text-white hover:bg-[#e5a800]">
                        Reset password
                    </button>
                </form>

                <p class="mt-8 text-center text-sm text-[#8a8680]">
                    Remember your password?
                    <a href="{{ route('login') }}" class="font-medium text-[#1a1a1a] underline underline-offset-4">Log in</a>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
