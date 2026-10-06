<section class="bg-[#f7f4ef]" aria-labelledby="marketplace-best-sellers" data-sellers>
    <div class="mx-auto w-full container px-6 py-14 sm:px-8 sm:py-16">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 id="marketplace-best-sellers" class="text-lg font-medium text-[#1a1a1a]">Marketplace Best Sellers</h2>
                <p class="mt-1 text-sm text-[#8a8680]">Our most popular standalone services, trusted by hundreds of businesses.</p>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <a href="{{ route('best-sellers') }}" class="text-sm font-medium text-[#c4a035]">See all</a>
                <div class="flex gap-2">
                <button type="button" data-sellers-prev class="grid h-10 w-10 place-items-center rounded-full bg-white text-[#1a1a1a] shadow-sm disabled:opacity-40" aria-label="Previous best sellers">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </button>
                <button type="button" data-sellers-next class="grid h-10 w-10 place-items-center rounded-full bg-white text-[#1a1a1a] shadow-sm disabled:opacity-40" aria-label="Next best sellers">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </button>
                </div>
            </div>
        </div>

        <div
            data-sellers-track
            class="mt-8 flex snap-x snap-mandatory gap-5 overflow-x-auto scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
        >
            @foreach ($bestSellers as $service)
                <article class="flex w-[78%] shrink-0 snap-start flex-col rounded-2xl bg-white p-3 shadow-sm sm:w-[calc((100%-1.25rem)/2)] lg:w-[calc((100%-3.75rem)/4)]">
                    <a href="{{ route('services.show', $service) }}" class="block aspect-[5/4] overflow-hidden rounded-xl bg-[#f3efe8]" aria-label="View {{ $service->name }}">
                        @if ($service->image)
                            <img src="{{ asset('assets/services/'.$service->image) }}" alt="" class="h-full w-full object-cover">
                        @endif
                    </a>
                    <div class="flex flex-1 flex-col px-2 pt-4 pb-2">
                        <p class="text-[11px] font-medium tracking-[0.14em] text-[#8a8680] uppercase">{{ $service->category->name }}</p>
                        <h3 class="mt-2 text-[15px] leading-snug font-semibold text-[#1a1a1a]"><a href="{{ route('services.show', $service) }}" class="hover:text-[#c4a035]">{{ $service->name }}</a></h3>
                        <p class="mt-2 text-sm text-[#f5b400]" aria-label="{{ $service->rating }} out of 5, {{ $service->review_count }} reviews">
                            @for ($star = 1; $star <= 5; $star++)
                                <span aria-hidden="true">{{ $star <= (int) round((float) $service->rating) ? '★' : '☆' }}</span>
                            @endfor
                            <span class="font-medium text-[#1a1a1a]">{{ number_format((float) $service->rating, 1) }}</span>
                            <span class="text-[#8a8680]">({{ $service->review_count }} Reviews)</span>
                        </p>
                        <div class="mt-auto flex items-end justify-between gap-3 pt-5">
                            <p>
                                <span class="block text-[11px] tracking-wide text-[#8a8680] uppercase">Starting at</span>
                                <span class="text-lg font-semibold text-[#1a1a1a]">₹{{ number_format((float) $service->price, 0) }}{{ $service->price_suffix }}</span>
                            </p>
                            <button type="button" class="grid h-10 w-10 place-items-center rounded-full bg-[#f6f1ea] text-[#1a1a1a]" aria-label="Add {{ $service->name }} to cart">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="8" cy="21" r="1"/>
                                    <circle cx="19" cy="21" r="1"/>
                                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
