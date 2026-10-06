<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>@yield('title') — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f6f3ee] text-[#1c1c1c] antialiased ">
    <div class="flex min-h-screen">
        <aside class="sticky top-0 hidden h-screen w-60 shrink-0 flex-col bg-[#111111] text-white md:flex">
            <a href="{{ route('admin.home') }}" class="flex items-center gap-3 px-5 py-6">
                <img src="{{ asset('assets/logo/logo.png') }}" alt="" class="h-10 w-auto rounded-md bg-white px-1.5">
                <span>
                    <span class="block text-sm font-semibold leading-none">Sunrise</span>
                    <span class="mt-1 block text-xs text-white/50">Admin</span>
                </span>
            </a>

            <nav class="flex flex-1 flex-col gap-6 px-3">
                <a href="{{ route('admin.home') }}" @class([
                    'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                    'bg-white/10 text-white' => request()->routeIs('admin.home'),
                    'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.home'),
                ])>
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="7" height="9" x="3" y="3" rx="1"/>
                        <rect width="7" height="5" x="14" y="3" rx="1"/>
                        <rect width="7" height="9" x="14" y="12" rx="1"/>
                        <rect width="7" height="5" x="3" y="16" rx="1"/>
                    </svg>
                    Overview
                </a>

                <div>
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-white/40">Catalog</p>
                    <div class="mt-2 flex flex-col gap-1">
                        <a href="{{ route('admin.categories.index') }}" @class([
                            'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.categories.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.categories.*'),
                        ])>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 7h16"/>
                                <path d="M4 12h16"/>
                                <path d="M4 17h10"/>
                            </svg>
                            Categories
                        </a>
                        <a href="{{ route('admin.services.index') }}" @class([
                            'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.services.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.services.*'),
                        ])>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 8.5 12 4 3 8.5l9 4.5 9-4.5Z"/>
                                <path d="m3 12.5 9 4.5 9-4.5"/>
                                <path d="m3 16.5 9 4.5 9-4.5"/>
                            </svg>
                            Services
                        </a>
                        <a href="{{ route('admin.packages.index') }}" @class([
                            'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.packages.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.packages.*'),
                        ])>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                <path d="m3.3 7 8.7 5 8.7-5"/>
                                <path d="M12 22V12"/>
                            </svg>
                            Packages
                        </a>
                    </div>
                </div>

                <div>
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-white/40">Sales</p>
                    <div class="mt-2">
                        <a href="{{ route('admin.orders.index', ['status' => 'placed']) }}" @class([
                            'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.orders.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.orders.*'),
                        ]) @if ($waiting['orders'] > 0) aria-label="Orders, {{ $waiting['orders'] }} waiting" @endif>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                                <path d="M3 6h18"/>
                                <path d="M16 10a4 4 0 0 1-8 0"/>
                            </svg>
                            Orders
                            @include('admin.partials.waiting-badge', ['count' => $waiting['orders'], 'active' => request()->routeIs('admin.orders.*'), 'push' => true])
                        </a>
                        <a href="{{ route('admin.customers.index') }}" @class([
                            'mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.customers.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.customers.*'),
                        ])>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            Customers
                        </a>
                        <a href="{{ route('admin.carts.index') }}" @class([
                            'mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.carts.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.carts.*'),
                        ])>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="8" cy="21" r="1"/>
                                <circle cx="19" cy="21" r="1"/>
                                <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                            </svg>
                            Carts
                        </a>
                        <a href="{{ route('admin.wishlists.index') }}" @class([
                            'mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.wishlists.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.wishlists.*'),
                        ])>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                            </svg>
                            Wishlists
                        </a>
                        <a href="{{ route('admin.coupons.index') }}" @class([
                            'mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.coupons.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.coupons.*'),
                        ])>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 8h16v3a2 2 0 0 0 0 4v3H4v-3a2 2 0 0 0 0-4V8Z"/>
                                <path d="M12 8v10"/>
                            </svg>
                            Coupons
                        </a>
                    </div>
                </div>

                <div>
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-white/40">Requests</p>
                    <div class="mt-2">
                        <a href="{{ route('admin.quotes.index', ['status' => 'new']) }}" @class([
                            'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.quotes.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.quotes.*'),
                        ]) @if ($waiting['quotes'] > 0) aria-label="Quotes, {{ $waiting['quotes'] }} waiting" @endif>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            Quotes
                            @include('admin.partials.waiting-badge', ['count' => $waiting['quotes'], 'active' => request()->routeIs('admin.quotes.*'), 'push' => true])
                        </a>
                        <a href="{{ route('admin.reviews.index') }}" @class([
                            'mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.reviews.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.reviews.*'),
                        ])>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 16.9 6.6 19.8l1-6.1L3.2 9.4l6.1-.9L12 3Z"/>
                            </svg>
                            Reviews
                        </a>
                        <a href="{{ route('admin.support.index', ['status' => 'new']) }}" @class([
                            'mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.support.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.support.*'),
                        ]) @if ($waiting['support'] > 0) aria-label="Support, {{ $waiting['support'] }} waiting" @endif>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 14v-1a8 8 0 0 1 16 0v1"/>
                                <rect x="3" y="14" width="4" height="6" rx="1"/>
                                <rect x="17" y="14" width="4" height="6" rx="1"/>
                            </svg>
                            Support
                            @include('admin.partials.waiting-badge', ['count' => $waiting['support'], 'active' => request()->routeIs('admin.support.*'), 'push' => true])
                        </a>
                        <a href="{{ route('admin.business.edit') }}" @class([
                            'mt-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                            'bg-[#f5b400] text-[#1a1a1a]' => request()->routeIs('admin.business.*'),
                            'text-white/70 hover:bg-white/5 hover:text-white' => ! request()->routeIs('admin.business.*'),
                        ])>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 21h18"/>
                                <path d="M5 21V7l7-4 7 4v14"/>
                                <path d="M9 21v-6h6v6"/>
                            </svg>
                            Business
                        </a>
                    </div>
                </div>
            </nav>

            <div class="border-t border-white/10 px-4 py-4">
                <p class="truncate text-sm font-medium">{{ auth('admin')->user()->name }}</p>
                <p class="truncate text-xs text-white/45">{{ auth('admin')->user()->email }}</p>
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-sm text-white/70 hover:text-white">Log out</button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between gap-4 border-b border-[#ebe6df] bg-white px-4 py-3 md:hidden">
                <a href="{{ route('admin.home') }}" class="text-sm font-semibold">Sunrise Admin</a>
                <nav class="flex items-center gap-4 text-sm">
                    <a href="{{ route('admin.categories.index') }}" @class(['font-semibold text-[#1a1a1a]' => request()->routeIs('admin.categories.*')])>Categories</a>
                    <a href="{{ route('admin.services.index') }}" @class(['font-semibold text-[#1a1a1a]' => request()->routeIs('admin.services.*')])>Services</a>
                    <a href="{{ route('admin.packages.index') }}" @class(['font-semibold text-[#1a1a1a]' => request()->routeIs('admin.packages.*')])>Packages</a>
                    <a href="{{ route('admin.orders.index', ['status' => 'placed']) }}" @class(['inline-flex items-center gap-1.5 font-semibold text-[#1a1a1a]' => request()->routeIs('admin.orders.*'), 'inline-flex items-center gap-1.5' => ! request()->routeIs('admin.orders.*')])>Orders @include('admin.partials.waiting-badge', ['count' => $waiting['orders'], 'active' => false])</a>
                    <a href="{{ route('admin.customers.index') }}" @class(['font-semibold text-[#1a1a1a]' => request()->routeIs('admin.customers.*')])>Customers</a>
                    <a href="{{ route('admin.carts.index') }}" @class(['font-semibold text-[#1a1a1a]' => request()->routeIs('admin.carts.*')])>Carts</a>
                    <a href="{{ route('admin.wishlists.index') }}" @class(['font-semibold text-[#1a1a1a]' => request()->routeIs('admin.wishlists.*')])>Wishlists</a>
                    <a href="{{ route('admin.coupons.index') }}" @class(['font-semibold text-[#1a1a1a]' => request()->routeIs('admin.coupons.*')])>Coupons</a>
                    <a href="{{ route('admin.quotes.index', ['status' => 'new']) }}" @class(['inline-flex items-center gap-1.5 font-semibold text-[#1a1a1a]' => request()->routeIs('admin.quotes.*'), 'inline-flex items-center gap-1.5' => ! request()->routeIs('admin.quotes.*')])>Quotes @include('admin.partials.waiting-badge', ['count' => $waiting['quotes'], 'active' => false])</a>
                    <a href="{{ route('admin.reviews.index') }}" @class(['font-semibold text-[#1a1a1a]' => request()->routeIs('admin.reviews.*')])>Reviews</a>
                    <a href="{{ route('admin.support.index', ['status' => 'new']) }}" @class(['inline-flex items-center gap-1.5 font-semibold text-[#1a1a1a]' => request()->routeIs('admin.support.*'), 'inline-flex items-center gap-1.5' => ! request()->routeIs('admin.support.*')])>Support @include('admin.partials.waiting-badge', ['count' => $waiting['support'], 'active' => false])</a>
                    <a href="{{ route('admin.business.edit') }}" @class(['font-semibold text-[#1a1a1a]' => request()->routeIs('admin.business.*')])>Business</a>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="text-[#6f6a64]">Log out</button>
                    </form>
                </nav>
            </header>

            <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 lg:px-10">
                @if (session('status'))
                    <p class="mb-6 rounded-xl border border-[#ead89a] bg-[#fff8e6] px-4 py-3 text-sm font-medium text-[#1a1a1a]">{{ session('status') }}</p>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <div data-admin-confirm class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="admin-confirm-title">
        <button type="button" data-confirm-dismiss class="absolute inset-0 bg-[#111111]/50" aria-label="Close"></button>
        <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Confirm</p>
            <h2 id="admin-confirm-title" data-confirm-title class="mt-2 text-xl font-semibold text-[#1a1a1a]"></h2>
            <p data-confirm-body class="mt-2 text-sm leading-6 text-[#6f6a64]"></p>
            <div class="mt-6 flex items-center justify-end gap-3">
                <button type="button" data-confirm-dismiss class="inline-flex h-11 items-center rounded-full border border-[#e4e0da] px-5 text-sm font-medium text-[#1a1a1a]">Cancel</button>
                <button type="button" data-confirm-accept class="inline-flex h-11 items-center rounded-full px-5 text-sm font-semibold"></button>
            </div>
        </div>
    </div>
</body>
</html>
