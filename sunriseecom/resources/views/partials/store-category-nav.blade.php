<nav class="border-b border-black/5 bg-white" aria-label="Categories">
    <div class="mx-auto flex w-full max-w-[90.5vw] items-center gap-8 px-6 py-3 text-sm text-[#1a1a1a] sm:px-8">
        <details class="relative shrink-0" data-menu>
            <summary class="flex cursor-pointer list-none items-center gap-1.5 font-medium [&::-webkit-details-marker]:hidden">
                All Categories
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m6 9 6 6 6-6"/>
                </svg>
            </summary>
            <div class="absolute left-0 z-20 mt-2 w-56 rounded-xl border border-black/5 bg-white p-1.5 shadow-lg">
                @foreach ($navCategories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="block rounded-lg px-3 py-2 hover:bg-[#f7f4ef]">{{ $category->name }}</a>
                @endforeach
            </div>
        </details>

        <div class="flex min-w-0 flex-1 items-center justify-between gap-x-4 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            @foreach ($navCategories as $category)
                <a href="{{ route('categories.show', $category) }}" class="whitespace-nowrap hover:text-[#c4a035]">{{ $category->name }}</a>
            @endforeach

            <a href="{{ route('deals') }}" class="whitespace-nowrap font-medium text-[#c4a035] hover:text-[#a8872c]">Today's Deals</a>
            <a href="{{ route('best-sellers') }}" class="whitespace-nowrap hover:text-[#c4a035]">Best Sellers</a>
        </div>
    </div>
</nav>
