<section class="bg-[#f7f4ef]" aria-labelledby="shop-by-category">
    <div class="mx-auto w-full container px-6 py-14 sm:px-8 sm:py-16">
        <div class="flex items-end justify-between gap-6">
            <div>
                <h2 id="shop-by-category" class="text-2xl font-semibold tracking-tight text-[#1a1a1a] sm:text-3xl">Shop by Category</h2>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-[#5c574f] sm:text-base">Explore specialized digital capabilities for your specific growth needs.</p>
            </div>
            <a href="{{ route('categories.index') }}" class="shrink-0 text-sm font-semibold tracking-wide text-[#a68420] hover:text-[#8a6914]">
                View All
                <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="mt-10 grid grid-cols-2 gap-x-5 gap-y-8 sm:grid-cols-4 sm:gap-x-8">
            @foreach ($homeCategories as $category)
                <a href="{{ route('categories.show', $category) }}" class="group text-center">
                    <span class="block aspect-[2.4/1] overflow-hidden rounded-full bg-white shadow-sm">
                        @if ($category->image)
                            <img
                                src="{{ asset('assets/categories/'.$category->image) }}"
                                alt=""
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            >
                        @endif
                    </span>
                    <span class="mt-4 block text-[15px] font-semibold leading-snug tracking-tight text-[#1a1a1a] sm:text-base">{{ $category->name }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
