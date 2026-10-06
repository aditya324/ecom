<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>Create an account — {{ config('app.name', 'Sunrise') }}</title>
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
                <h1 class="text-3xl font-semibold tracking-tight text-[#1a1a1a] sm:text-4xl">Create an account</h1>
                <p class="mt-2 text-sm text-[#8a8680]">Enter your details below</p>

                @if ($errors->any())
                    <p class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</p>
                @endif

                <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
                    @csrf

                    <div>
                        <label class="sr-only" for="name">Name</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            placeholder="Your Name"
                            required
                            autofocus
                            autocomplete="name"
                            class="w-full rounded-lg border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none placeholder:text-[#b0aaa3] focus:border-[#f5b400]"
                        >
                    </div>

                    <div>
                        <label class="sr-only" for="email">Email</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="Your Email / phone no"
                            required
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
                            autocomplete="new-password"
                            class="w-full rounded-lg border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none placeholder:text-[#b0aaa3] focus:border-[#f5b400]"
                        >
                    </div>

                    <button type="submit" class="w-full rounded-lg bg-[#f5b400] px-4 py-3 text-sm font-medium text-white hover:bg-[#e5a800]">
                        Create Account
                    </button>
                </form>

                <a href="{{ route('auth.google') }}" class="mt-4 flex w-full items-center justify-center gap-3 rounded-lg border border-[#e4e0da] bg-white px-4 py-3 text-sm font-medium text-[#1a1a1a]">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.2-2.27H12v4.3h6.46a5.52 5.52 0 0 1-2.4 3.62v3h3.88c2.27-2.09 3.55-5.17 3.55-8.65z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.96-1.07 7.95-2.91l-3.88-3c-1.08.72-2.46 1.15-4.07 1.15-3.13 0-5.78-2.11-6.73-4.96H1.26v3.09A12 12 0 0 0 12 24z"/>
                        <path fill="#FBBC05" d="M5.27 14.28A7.2 7.2 0 0 1 4.89 12c0-.79.14-1.56.38-2.28V6.63H1.26a12 12 0 0 0 0 10.74l4.01-3.09z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.45-3.45C17.95 1.14 15.24 0 12 0 7.31 0 3.26 2.69 1.26 6.63l4.01 3.09C6.22 6.87 8.87 4.75 12 4.75z"/>
                    </svg>
                    Sign up with Google
                </a>

                <p class="mt-8 text-center text-sm text-[#8a8680]">
                    Already have account?
                    <a href="{{ route('login') }}" class="font-medium text-[#1a1a1a] underline underline-offset-4">Log in</a>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
