<header class="bg-[#111111] text-white">
    <div class="mx-auto flex h-[72px] w-full max-w-[90.5vw] items-center gap-4 px-6 sm:gap-6 sm:px-8">
        <a href="{{ route('home') }}" class="shrink-0">
            <img src="{{ asset('assets/logo/logo.png') }}" alt="Sunrise Digital" class="h-28 w-auto">
        </a>

        <form role="search" method="get" action="{{ route('search') }}" data-header-search class="relative mx-auto w-full max-w-xl">
            <div class="flex h-11 items-center rounded-md bg-white pl-4 pr-1">
                <label class="sr-only" for="header-search">Search</label>
                <input
                    id="header-search"
                    name="q"
                    type="search"
                    value="{{ request('q') }}"
                    placeholder="Search for services..."
                    autocomplete="off"
                    data-search-input
                    class="w-full bg-transparent text-sm text-[#1a1a1a] outline-none placeholder:text-[#9a9a9a]"
                >
                <button type="submit" aria-label="Search" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-[#f5b400] text-white">
                    <span data-lottie="search" class="block h-5 w-5" aria-hidden="true"></span>
                </button>
            </div>
            <div data-search-panel hidden class="absolute top-full right-0 left-0 z-30 mt-2 overflow-hidden rounded-xl bg-white text-sm text-[#1a1a1a] shadow-lg"></div>
        </form>

        <nav class="flex shrink-0 items-center gap-3 sm:gap-5">
            <a href="{{ route('wishlist.index') }}" aria-label="Wishlist" class="relative text-white">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                </svg>
                @if (($wishlistCount ?? 0) > 0)
                    <span class="absolute -right-2 -top-2 grid h-4 min-w-4 place-items-center rounded-full bg-[#f5b400] px-1 text-[10px] font-bold leading-none text-[#1a1a1a]">{{ $wishlistCount }}</span>
                @endif
            </a>

            <a href="{{ route('cart.index') }}" aria-label="Cart" class="relative text-white">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="8" cy="21" r="1"/>
                    <circle cx="19" cy="21" r="1"/>
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                </svg>
                @if (($cartCount ?? 0) > 0)
                    <span class="absolute -right-2 -top-2 grid h-4 min-w-4 place-items-center rounded-full bg-[#f5b400] px-1 text-[10px] font-bold leading-none text-[#1a1a1a]">{{ $cartCount }}</span>
                @endif
            </a>

            @auth
                <div class="relative">
                    <details class="group" data-menu>
                        <summary class="flex cursor-pointer list-none items-center gap-2 [&::-webkit-details-marker]:hidden">
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-[#f5b400] text-sm font-semibold text-[#1a1a1a]">
                                {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </span>
                            <span class="max-w-32 truncate text-sm text-white">{{ auth()->user()->name }}</span>
                        </summary>
                        <div class="absolute right-0 z-20 mt-3 w-44 rounded-xl border border-black/5 bg-white p-1.5 text-sm text-[#1a1a1a] shadow-lg">
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-2.5 py-2 hover:bg-[#f7f4ef]">
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                Profile
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left hover:bg-[#f7f4ef]">
                                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                        <polyline points="16 17 21 12 16 7"/>
                                        <line x1="21" x2="9" y1="12" y2="12"/>
                                    </svg>
                                    Log out
                                </button>
                            </form>
                        </div>
                    </details>
                </div>
            @else
                <a href="{{ route('login') }}" class="flex items-center gap-2">
                    <span class="grid h-9 w-9 place-items-center rounded-full bg-[#2a2a2a] text-white">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>
                    <span class="hidden text-sm sm:inline">Account</span>
                </a>
            @endauth
        </nav>
    </div>
</header>
@include('partials.store-category-nav')
