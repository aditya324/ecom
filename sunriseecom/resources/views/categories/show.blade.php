@php
    $query = \Illuminate\Support\Arr::except(request()->query(), ['page']);

    $without = function (array $forget, array $replace = []) use ($catalogUrl, $query) {
        $next = array_merge($query, $replace);

        foreach ($forget as $key) {
            unset($next[$key]);
        }

        $next = array_filter($next, fn ($value) => $value !== null && $value !== '' && $value !== []);

        return $next === [] ? $catalogUrl : $catalogUrl.'?'.http_build_query($next);
    };
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $category ? $category->name.' Services' : 'All Services' }} — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto w-full max-w-[90.5vw] flex-1 px-6 py-8 sm:px-8">
        <nav class="flex items-center gap-2 text-sm text-[#8a8680]" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 hover:text-[#1a1a1a]">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="m4 11 8-7 8 7"/>
                    <path d="M6 10.5V20h12v-9.5"/>
                </svg>
                Home
            </a>
            <span aria-hidden="true">›</span>
            @if ($category)
                <a href="{{ route('categories.index') }}" class="hover:text-[#1a1a1a]">Services</a>
                @if ($category->parent)
                    <span aria-hidden="true">›</span>
                    <a href="{{ route('categories.show', $category->parent) }}" class="hover:text-[#1a1a1a]">{{ $category->parent->name }}</a>
                @endif
                <span aria-hidden="true">›</span>
                <span class="text-[#1a1a1a]">{{ $category->name }}</span>
            @else
                <span class="text-[#1a1a1a]">Services</span>
            @endif
        </nav>

        <div data-catalog-filters onchange="if (event.target.matches('[data-rating]') && event.target.checked) { this.querySelectorAll('[data-rating]').forEach((box) => { if (box !== event.target) box.checked = false }) } if (event.target.matches('[data-range-min], [data-range-max]')) return; this.querySelectorAll('[data-price-min], [data-price-max]').forEach((input) => { input.disabled = input.value === '' }); const sort = this.querySelector('[name=sort]'); if (sort && sort.value === 'recommended') sort.disabled = true; document.getElementById('catalog-filters').requestSubmit()">
            <form id="catalog-filters" method="get" action="{{ $catalogUrl }}" class="hidden"></form>
            <div class="mt-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ $category ? $category->name.' Services' : 'All Services' }}</h1>
                    <p class="mt-2 text-sm text-[#6f6a64]">{{ $category?->tagline ?: ($category ? 'Browse '.$category->name.' services from the Sunrise marketplace.' : 'Browse every service from the Sunrise marketplace.') }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <p class="text-sm text-[#6f6a64]">{{ number_format($services->total()) }} {{ \Illuminate\Support\Str::plural('Service', $services->total()) }} Available</p>
                    <label class="inline-flex items-center gap-2 rounded-xl border border-[#efe8dc] bg-white px-3 py-2 text-sm shadow-sm">
                        <span class="text-[#8a8680]">Sort:</span>
                        <select name="sort" form="catalog-filters" class="bg-transparent font-medium outline-none">
                            <option value="recommended" @selected($filters['sort'] === 'recommended')>Recommended</option>
                            <option value="price_asc" @selected($filters['sort'] === 'price_asc')>Price: Low to High</option>
                            <option value="price_desc" @selected($filters['sort'] === 'price_desc')>Price: High to Low</option>
                            <option value="rating" @selected($filters['sort'] === 'rating')>Top Rated</option>
                        </select>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-6 lg:flex-row lg:items-start">
                <aside class="w-full shrink-0 rounded-2xl bg-white p-5 shadow-[0_8px_30px_rgba(28,28,28,0.04)] lg:sticky lg:top-6 lg:w-72">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold">Filters</h2>
                        <a href="{{ $catalogUrl }}" class="text-sm font-medium text-[#e07a2f]">Clear All</a>
                    </div>

                    @if ($categoryFilters->isNotEmpty() || $moreCategoryFilters->isNotEmpty())
                        <details open class="mt-4 border-t border-[#f3eee6] pt-4">
                            <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-semibold">
                                Category
                                <svg class="h-4 w-4 text-[#8a8680]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                            </summary>
                            <ul class="mt-3 flex flex-col gap-2.5">
                                @foreach ($categoryFilters as $filterCategory)
                                    @include('partials.store-category-filter-option', ['filterCategory' => $filterCategory])
                                @endforeach
                            </ul>
                            @if ($moreCategoryFilters->isNotEmpty())
                                <details class="mt-3 border-t border-[#f3eee6] pt-3" @if ($moreCategoryFilters->contains(fn ($row) => in_array($row->slug, $filters['categories'], true))) open @endif>
                                    <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-semibold text-[#e07a2f]">
                                        View more
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                                    </summary>
                                    <ul class="mt-3 flex flex-col gap-2.5">
                                        @foreach ($moreCategoryFilters as $filterCategory)
                                            @include('partials.store-category-filter-option', ['filterCategory' => $filterCategory])
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </details>
                    @endif

                    <details open class="mt-4 border-t border-[#f3eee6] pt-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-semibold">
                            Price Range
                            <svg class="h-4 w-4 text-[#8a8680]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </summary>
                        <div class="mt-3 flex items-center gap-2">
                            <label class="flex min-w-0 flex-1 items-center gap-1 rounded-lg border border-[#efe8dc] px-2 py-1.5 text-sm">
                                <span class="text-[#8a8680]">₹</span>
                                <input type="number" name="min" form="catalog-filters" min="0" max="{{ $priceMax }}" value="{{ $filters['min'] }}" placeholder="Min" data-price-min class="w-full bg-transparent outline-none">
                            </label>
                            <span class="text-[#8a8680]">-</span>
                            <label class="flex min-w-0 flex-1 items-center gap-1 rounded-lg border border-[#efe8dc] px-2 py-1.5 text-sm">
                                <span class="text-[#8a8680]">₹</span>
                                <input type="number" name="max" form="catalog-filters" min="0" max="{{ $priceMax }}" value="{{ $filters['max'] }}" placeholder="Max" data-price-max class="w-full bg-transparent outline-none">
                            </label>
                        </div>
                        <div class="relative mt-4 h-1.5 rounded-full bg-[#f3e6d4]" data-range>
                            <div data-range-fill class="absolute h-full rounded-full bg-[#f5b400]"></div>
                            <input type="range" min="0" max="{{ $priceMax }}" step="500" value="{{ $filters['min'] ?? 0 }}" data-range-min class="catalog-range" aria-label="Minimum price">
                            <input type="range" min="0" max="{{ $priceMax }}" step="500" value="{{ $filters['max'] ?? $priceMax }}" data-range-max class="catalog-range" aria-label="Maximum price">
                        </div>
                    </details>

                    <details open class="mt-4 border-t border-[#f3eee6] pt-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-semibold">
                            Rating
                            <svg class="h-4 w-4 text-[#8a8680]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </summary>
                        <ul class="mt-3 flex flex-col gap-2.5 text-sm">
                            <li>
                                <label class="flex items-center gap-2.5">
                                    <input type="checkbox" name="rating" form="catalog-filters" value="4.5" data-rating class="h-4 w-4 rounded border-[#e4d8c4] accent-[#f5b400]" @checked($filters['rating'] === '4.5')>
                                    <span class="text-[#f5b400]">★★★★★</span>
                                    <span>4.5 & up</span>
                                </label>
                            </li>
                            <li>
                                <label class="flex items-center gap-2.5">
                                    <input type="checkbox" name="rating" form="catalog-filters" value="4" data-rating class="h-4 w-4 rounded border-[#e4d8c4] accent-[#f5b400]" @checked($filters['rating'] === '4')>
                                    <span><span class="text-[#f5b400]">★★★★</span><span class="text-[#e4d8c4]">★</span></span>
                                    <span>4.0 & up</span>
                                </label>
                            </li>
                        </ul>
                    </details>

                    <details open class="mt-4 border-t border-[#f3eee6] pt-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between text-sm font-semibold">
                            Service Type
                            <svg class="h-4 w-4 text-[#8a8680]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </summary>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (['one-time' => 'One-time', 'monthly' => 'Monthly Retainer', 'hourly' => 'Hourly'] as $value => $label)
                                <label class="cursor-pointer rounded-full border border-[#efe8dc] px-3 py-1.5 text-sm has-[:checked]:border-[#1a1a1a] has-[:checked]:bg-[#1a1a1a] has-[:checked]:text-white">
                                    <input type="checkbox" name="type[]" form="catalog-filters" value="{{ $value }}" class="sr-only" @checked(in_array($value, $filters['types'], true))>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </details>
                </aside>

                <div class="min-w-0 flex-1">
                    @if ($filters['categories'] !== [] || $filters['types'] !== [] || $filters['rating'] || $filters['min'] !== null || $filters['max'] !== null)
                        <div class="mb-4 flex flex-wrap items-center gap-2">
                            @foreach ($filters['categories'] as $slug)
                                <a href="{{ $without(['categories'], ['categories' => array_values(array_diff($filters['categories'], [$slug]))]) }}" class="inline-flex items-center gap-2 rounded-full border border-[#f0e0c4] bg-[#fff7ea] px-3 py-1 text-sm">
                                    Category: {{ $filterCategories->firstWhere('slug', $slug)?->name ?? $slug }}
                                    <span aria-hidden="true">×</span>
                                </a>
                            @endforeach
                            @if ($filters['rating'])
                                <a href="{{ $without(['rating']) }}" class="inline-flex items-center gap-2 rounded-full border border-[#f0e0c4] bg-[#fff7ea] px-3 py-1 text-sm">
                                    Rating: {{ $filters['rating'] }}+
                                    <span aria-hidden="true">×</span>
                                </a>
                            @endif
                            @foreach ($filters['types'] as $type)
                                <a href="{{ $without(['type'], ['type' => array_values(array_diff($filters['types'], [$type]))]) }}" class="inline-flex items-center gap-2 rounded-full border border-[#f0e0c4] bg-[#fff7ea] px-3 py-1 text-sm">
                                    {{ ['one-time' => 'One-time', 'monthly' => 'Monthly Retainer', 'hourly' => 'Hourly'][$type] }}
                                    <span aria-hidden="true">×</span>
                                </a>
                            @endforeach
                            @if ($filters['min'] !== null || $filters['max'] !== null)
                                <a href="{{ $without(['min', 'max']) }}" class="inline-flex items-center gap-2 rounded-full border border-[#f0e0c4] bg-[#fff7ea] px-3 py-1 text-sm">
                                    ₹{{ number_format($filters['min'] ?? 0) }} – ₹{{ number_format($filters['max'] ?? $priceMax) }}
                                    <span aria-hidden="true">×</span>
                                </a>
                            @endif
                            <a href="{{ $catalogUrl }}" class="text-sm font-medium text-[#e07a2f]">Clear all</a>
                        </div>
                    @endif

                    @php
                        $groupCategories = $filterCategories
                            ->filter(fn ($row) => $services->contains('category_id', $row->id))
                            ->values();
                    @endphp
                    @if ($services->isEmpty())
                        <div class="rounded-2xl bg-white px-6 py-16 text-center">
                            <p class="text-lg font-medium">No services match these filters.</p>
                            <a href="{{ $catalogUrl }}" class="mt-3 inline-block text-sm font-medium text-[#e07a2f]">Clear all filters</a>
                        </div>
                    @elseif ($groupCategories->isNotEmpty() && ($category === null || ! ($groupCategories->count() === 1 && $groupCategories->first()->id === $category->id)))
                        <div class="flex flex-col gap-10">
                            @foreach ($groupCategories as $groupCategory)
                                <section>
                                    <div class="mb-4 flex items-end justify-between gap-4">
                                        <h2 class="text-xl font-semibold">{{ $groupCategory->name }}</h2>
                                        <a href="{{ route('categories.show', $groupCategory) }}" class="shrink-0 text-sm font-medium text-[#c4a035]">View more</a>
                                    </div>
                                    <div class="flex flex-wrap gap-5">
                                        @foreach ($services->where('category_id', $groupCategory->id) as $service)
                                            @include('partials.store-service-card', ['service' => $service])
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-wrap gap-5">
                            @foreach ($services as $service)
                                @include('partials.store-service-card', ['service' => $service])
                            @endforeach
                        </div>
                    @endif

                    @if ($services->hasPages())
                        <div class="mt-8">
                            {{ $services->onEachSide(1)->links('partials.store-pagination') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    @include('partials.store-footer')
</body>
</html>
