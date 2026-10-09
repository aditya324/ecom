<header class="bg-[#111111] text-white">
    <div class="mx-auto grid w-full max-w-[90.5vw] grid-cols-[auto_1fr] items-center gap-x-3 gap-y-3 px-4 py-3 lg:h-[72px] lg:grid-cols-[auto_minmax(0,36rem)_auto] lg:gap-6 lg:px-8 lg:py-0">
        <a href="{{ route('home') }}" class="flex items-center justify-center h-12 justify-self-start overflow-hidden lg:h-[72px] lg:justify-start">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="Sunrise Digital" class="h-32 w-auto lg:h-36">
        </a>

        <div class="justify-self-end lg:col-start-3">
            <nav class="hidden items-center gap-1 lg:flex">
                <a href="{{ route('wishlist.index') }}" aria-label="Wishlist" class="relative grid h-10 w-10 place-items-center rounded-full text-white hover:bg-white/10">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                    </svg>
                    @if (($wishlistCount ?? 0) > 0)
                        <span class="absolute top-0.5 right-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-[#f5b400] px-1 text-[10px] font-bold leading-none text-[#1a1a1a] ring-2 ring-[#111111]">{{ $wishlistCount }}</span>
                    @endif
                </a>
                <a href="{{ route('cart.index') }}" aria-label="Cart" class="relative grid h-10 w-10 place-items-center rounded-full text-white hover:bg-white/10">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="8" cy="21" r="1"/>
                        <circle cx="19" cy="21" r="1"/>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                    </svg>
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute top-0.5 right-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-[#f5b400] px-1 text-[10px] font-bold leading-none text-[#1a1a1a] ring-2 ring-[#111111]">{{ $cartCount }}</span>
                    @endif
                </a>
                @auth
                    <div class="relative">
                        <details class="group" data-menu>
                            <summary class="flex cursor-pointer list-none items-center gap-2 [&::-webkit-details-marker]:hidden">
                                <span class="grid h-10 w-10 place-items-center rounded-full bg-[#f5b400] text-sm font-semibold text-[#1a1a1a]">
                                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                </span>
                                <span class="max-w-32 truncate text-sm text-white">{{ auth()->user()->name }}</span>
                            </summary>
                            <div class="absolute right-0 z-30 mt-3 w-44 rounded-xl border border-black/5 bg-white p-1.5 text-sm text-[#1a1a1a] shadow-lg">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 hover:bg-[#f7f4ef]">Profile</a>
                                <a href="{{ route('profile.orders') }}" class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 hover:bg-[#f7f4ef]">Orders</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left hover:bg-[#f7f4ef]">Log out</button>
                                </form>
                            </div>
                        </details>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-2 pl-1">
                        <span class="grid h-10 w-10 place-items-center rounded-full bg-[#2a2a2a] text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <span class="text-sm">Account</span>
                    </a>
                @endauth
            </nav>

            <button type="button" data-store-menu-open aria-expanded="false" aria-controls="store-menu" class="relative grid h-10 w-10 place-items-center rounded-full text-white hover:bg-white/10 lg:hidden">
                <span class="sr-only">Menu</span>
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 7h16"/>
                    <path d="M4 12h16"/>
                    <path d="M4 17h16"/>   
                </svg>
                @if (($wishlistCount ?? 0) + ($cartCount ?? 0) > 0)
                    <span class="absolute top-0.5 right-0.5 grid h-4 min-w-4 place-items-center rounded-full bg-[#f5b400] px-1 text-[10px] font-bold leading-none text-[#1a1a1a] ring-2 ring-[#111111]">{{ ($wishlistCount ?? 0) + ($cartCount ?? 0) }}</span>
                @endif
            </button>
        </div>

        <form role="search" method="get" action="{{ route('search') }}" data-header-search class="relative col-span-2 hidden min-w-0 lg:col-span-1 lg:col-start-2 lg:row-start-1 lg:block">
            <div class="flex h-11 items-center rounded-full bg-white pl-4 pr-1.5">
                <label class="sr-only" for="header-search">Search</label>
                <input
                    id="header-search"
                    name="q"
                    type="search"
                    value="{{ request('q') }}"
                    placeholder="Search for services..."
                    autocomplete="off"
                    data-search-input
                    class="min-w-0 flex-1 bg-transparent text-sm text-[#1a1a1a] outline-none placeholder:text-[#9a9a9a]"
                >
                <button type="submit" aria-label="Search" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[#f5b400] text-[#1a1a1a]">
                    <span data-lottie="search" class="block h-4 w-4" aria-hidden="true"></span>
                </button>
            </div>
            <div data-search-panel hidden class="absolute top-full right-0 left-0 z-30 mt-2 overflow-hidden rounded-xl bg-white text-sm text-[#1a1a1a] shadow-lg"></div>
        </form>
    </div>
</header>

<div id="store-menu" data-store-menu hidden class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
    <button type="button" data-store-menu-close class="absolute inset-0 bg-[#111111]/50" aria-label="Close menu"></button>
    <aside class="absolute inset-y-0 right-0 flex w-full max-w-xs flex-col bg-[#111111] text-white shadow-xl">
        <div class="flex items-center justify-between px-5 py-5">
            <p class="text-sm font-semibold">Menu</p>
            <button type="button" data-store-menu-close class="grid h-10 w-10 place-items-center rounded-full text-white hover:bg-white/10" aria-label="Close menu">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/>
                    <path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>

        <nav class="flex flex-1 flex-col gap-1 px-3">
            <form role="search" method="get" action="{{ route('search') }}" data-header-search class="relative mb-3">
                <div class="flex h-11 items-center rounded-full bg-white pl-4 pr-1.5">
                    <label class="sr-only" for="menu-search">Search</label>
                    <input
                        id="menu-search"
                        name="q"
                        type="search"
                        value="{{ request('q') }}"
                        placeholder="Search for services..."
                        autocomplete="off"
                        data-search-input
                        class="min-w-0 flex-1 bg-transparent text-sm text-[#1a1a1a] outline-none placeholder:text-[#9a9a9a]"
                    >
                    <button type="submit" aria-label="Search" class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-[#f5b400] text-[#1a1a1a]">
                        <span data-lottie="search" class="block h-4 w-4" aria-hidden="true"></span>
                    </button>
                </div>
                <div data-search-panel hidden class="absolute top-full right-0 left-0 z-30 mt-2 overflow-hidden rounded-xl bg-white text-sm text-[#1a1a1a] shadow-lg"></div>
            </form>
            <a href="{{ route('wishlist.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-white/80 hover:bg-white/5 hover:text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                </svg>
                Wishlist
                @if (($wishlistCount ?? 0) > 0)
                    <span class="ml-auto grid h-5 min-w-5 place-items-center rounded-full bg-[#f5b400] px-1.5 text-[11px] font-bold leading-none text-[#1a1a1a]">{{ $wishlistCount }}</span>
                @endif
            </a>
            <a href="{{ route('cart.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-white/80 hover:bg-white/5 hover:text-white">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="8" cy="21" r="1"/>
                    <circle cx="19" cy="21" r="1"/>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                </svg>
                Cart
                @if (($cartCount ?? 0) > 0)
                    <span class="ml-auto grid h-5 min-w-5 place-items-center rounded-full bg-[#f5b400] px-1.5 text-[11px] font-bold leading-none text-[#1a1a1a]">{{ $cartCount }}</span>
                @endif
            </a>

            <div class="mt-4 border-t border-white/10 pt-4">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.16em] text-white/40">Account</p>
                @auth
                    <p class="mt-3 truncate px-3 text-sm font-medium">{{ auth()->user()->name }}</p>
                    <a href="{{ route('profile.edit') }}" class="mt-2 flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-white/80 hover:bg-white/5 hover:text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Profile
                    </a>
                    <a href="{{ route('profile.orders') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-white/80 hover:bg-white/5 hover:text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/>
                            <path d="M3 6h18"/>
                            <path d="M16 10a4 4 0 0 1-8 0"/>
                        </svg>
                        Orders
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-medium text-white/80 hover:bg-white/5 hover:text-white">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" x2="9" y1="12" y2="12"/>
                            </svg>
                            Log out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mt-2 flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-white/80 hover:bg-white/5 hover:text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        Log in
                    </a>
                @endauth
            </div>
        </nav>
    </aside>
</div>
@include('partials.store-category-nav')
