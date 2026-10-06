<section class="bg-[#f7f4ef]" aria-labelledby="shop-by-category">
    <div class="mx-auto w-full container px-6 py-14 sm:px-8 sm:py-16">
        <div class="flex items-end justify-between gap-6">
            <div>
                <h2 id="shop-by-category" class="text-lg font-medium text-[#1a1a1a]">Shop by Category</h2>
                <p class="mt-1 text-sm text-[#6f6a64]">Explore specialized digital capabilities for your specific growth needs.</p>
            </div>
            <a href="{{ route('categories.index') }}" class="shrink-0 text-sm font-medium text-[#c4a035]">
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
                    <span class="mt-3 block text-sm text-[#1a1a1a]">{{ $category->name }}</span>
                </a>
            @endforeach
        </div>
    </div>
</section>
