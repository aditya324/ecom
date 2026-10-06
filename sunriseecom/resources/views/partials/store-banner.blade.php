@php
    $slides = [
        [
            'image' => 'assets/banners/growth.jpg',
            'eyebrow' => 'High-conversion solutions',
            'title' => 'Grow Your Business With Digital Solutions That Work.',
            'body' => 'Scale your operations instantly with pre-packaged, agency-grade services. From full-stack development to targeted performance marketing.',
        ],
        [
            'image' => 'assets/banners/marketing.jpg',
            'eyebrow' => 'Performance marketing',
            'title' => 'Reach the Right Customers With Campaigns That Convert.',
            'body' => 'Run targeted ads, SEO, and content programs built to bring in qualified leads. One team for strategy, creative, and reporting.',
        ],
        [
            'image' => 'assets/banners/packages.jpg',
            'eyebrow' => 'Ready-made packages',
            'title' => 'Launch Faster With Packages Built for Growing Brands.',
            'body' => 'Pick a package and get a clear scope, timeline, and price. Websites, marketing, and support without starting from a blank brief.',
        ],
    ];
@endphp

<section data-banner class="relative bg-[#111111] text-white" aria-roledescription="carousel" aria-label="Featured solutions">
    <div
        data-banner-track
        class="flex cursor-grab snap-x snap-mandatory overflow-x-auto scroll-smooth active:cursor-grabbing [scrollbar-width:none] [&::-webkit-scrollbar]:hidden"
    >
        @foreach ($slides as $slide)
            <article class="relative flex h-[min(640px,68vh)] min-h-[480px] w-full shrink-0 snap-start items-center overflow-hidden" aria-roledescription="slide">
                <img src="{{ asset($slide['image']) }}" alt="" class="absolute inset-0 h-full w-full object-cover object-[70%_center]">
                <div class="absolute inset-0 bg-gradient-to-r from-[#111111] via-[#111111]/85 to-[#111111]/15"></div>
                <div class="relative mx-auto w-full max-w-[95.5vw] px-6 py-16 sm:px-8">
                    <div class="max-w-xl">
                        <p class="inline-flex items-center gap-2 rounded-full border border-[#f5b400]/70 bg-[#1c1408]/80 px-3 py-1 text-[11px] font-semibold tracking-[0.14em] text-[#f5b400] uppercase">
                            <span aria-hidden="true">✦</span>
                            {{ $slide['eyebrow'] }}
                        </p>
                        <h2 class="mt-5 max-w-lg text-3xl font-medium leading-tight text-white sm:text-5xl">{{ $slide['title'] }}</h2>
                        <p class="mt-4 max-w-lg text-sm leading-relaxed text-[#d4d4d4] sm:text-base">{{ $slide['body'] }}</p>
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#f5b400] px-5 py-2.5 text-sm font-medium text-[#1a1a1a]">
                                Shop Services
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14"/>
                                    <path d="m12 5 7 7-7 7"/>
                                </svg>
                            </a>
                            <a href="{{ route('home') }}#packages" class="inline-flex items-center rounded-lg border border-white/10 bg-[#2a2a2a] px-5 py-2.5 text-sm font-medium text-white">
                                Explore Packages
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="pointer-events-none absolute inset-x-0 bottom-5">
        <div class="mx-auto flex w-full max-w-[95.5vw] justify-end px-6 sm:px-8">
            <div class="pointer-events-auto flex items-center gap-2" role="tablist" aria-label="Banner slides">
                @foreach ($slides as $index => $slide)
                    <button
                        type="button"
                        data-banner-dot
                        @if ($index === 0) data-active @endif
                        class="h-1 w-8 rounded-full bg-[#4a4a4a] data-active:bg-[#f5b400]"
                        aria-label="Show slide {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
        </div>
    </div>
</section>
