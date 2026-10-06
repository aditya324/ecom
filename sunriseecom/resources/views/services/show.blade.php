@php
    $durations = $service->durations();
    $selectedDuration = $durations[0] ?? null;
    $selectedMonths = (int) ($selectedDuration['months'] ?? 1);
    $displayPrice = (float) ($selectedDuration['price'] ?? $service->price);
    $displayCompare = $selectedDuration['compare'] ?? ($service->compare_price !== null ? (float) $service->compare_price : null);
    $audiences = $service->audiences();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>{{ $service->name }} — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto w-full max-w-[90.5vw] flex-1 px-6 py-8 sm:px-8">
        @if (session('status'))
            <p class="mb-6 rounded-xl border border-[#ead89a] bg-[#fff8e6] px-4 py-3 text-sm font-medium text-[#1a1a1a]">{{ session('status') }}</p>
        @endif
        <div class="grid items-start gap-x-6 gap-y-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(280px,420px)] xl:grid-cols-[minmax(0,1fr)_minmax(280px,420px)_300px]">
            <div>
                @if ($service->image)
                    <div class="overflow-hidden rounded-2xl bg-[#ece7e0]">
                        <img src="{{ asset('assets/services/'.$service->image) }}" alt="{{ $service->name }}" class="aspect-[16/10] w-full object-contain">
                    </div>
                @endif

                @if ($service->youtubeId())
                    <div class="mt-4 overflow-hidden rounded-2xl bg-black">
                        <iframe
                            src="https://www.youtube-nocookie.com/embed/{{ $service->youtubeId() }}"
                            title="{{ $service->name }}"
                            class="aspect-video w-full"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        ></iframe>
                    </div>
                @endif

                <div class="mt-3 flex items-center gap-2.5 rounded-xl bg-[#fff4e8] px-3 py-2.5">
                    <svg class="h-5 w-5 shrink-0 text-[#e07a2f]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path d="M12 3 4.5 6v5.5c0 4.2 3.1 7.3 7.5 9.5 4.4-2.2 7.5-5.3 7.5-9.5V6L12 3Z"/>
                        <path d="m9 12 2 2 4-4"/>
                    </svg>
                    <p>
                        <span class="block text-sm font-semibold text-[#1a1a1a]">Verified Agency Service</span>
                        <span class="block text-xs text-[#8a8680]">All providers vetted for industrial-scale delivery.</span>
                    </p>
                </div>
            </div>

            <div data-service-options data-base-price="{{ $service->price }}" @if ($service->compare_price !== null) data-compare-price="{{ $service->compare_price }}" @endif>
                <p class="text-[11px] font-semibold tracking-[0.16em] text-[#e07a2f] uppercase">{{ $service->category->name }}</p>
                <h1 class="mt-1.5 text-[22px] leading-snug font-bold text-[#1a1a1a]">{{ $service->name }}</h1>
                <p class="mt-2 flex flex-wrap items-center gap-1.5 text-sm" aria-label="{{ number_format((float) $service->rating, 1) }} out of 5, {{ number_format($service->review_count) }} ratings">
                    <span class="tracking-tight text-[#f5b400]" aria-hidden="true">
                        @for ($star = 1; $star <= 5; $star++)
                            {{ $star <= (int) round((float) $service->rating) ? '★' : '☆' }}
                        @endfor
                    </span>
                    <span class="font-semibold text-[#1a1a1a]">{{ number_format((float) $service->rating, 1) }}</span>
                    <span class="text-[#2f6fed]">{{ number_format($service->review_count) }} Ratings</span>
                </p>

                <div class="mt-4">
                    <p class="flex flex-wrap items-baseline gap-x-1.5">
                        @if ($service->savingsPercent())
                            <span class="text-lg font-semibold text-[#e07a2f]">-{{ $service->savingsPercent() }}%</span>
                        @endif
                        <span data-service-price class="text-[28px] leading-none font-bold tracking-tight text-[#1a1a1a]">{{ $service->money($displayPrice) }}</span>
                        @if ($service->billingLabel())
                            <span data-billing-suffix @class(['text-sm text-[#8a8680]', 'hidden' => $selectedMonths !== 1])>{{ $service->billingLabel() }}</span>
                        @endif
                    </p>
                    @if ($service->compare_price)
                        <p data-service-compare-row @class(['mt-1 text-sm text-[#8a8680]', 'hidden' => $displayCompare === null])>M.R.P.: <span data-service-compare class="line-through">{{ $displayCompare ? $service->money($displayCompare) : '' }}</span></p>
                    @endif
                    <p class="mt-1 text-xs text-[#8a8680]">{{ $service->priceNote() }}</p>
                </div>

                @if ($selectedDuration)
                    <div class="mt-4">
                        <p class="text-sm text-[#1a1a1a]">
                            Duration: <span class="font-semibold" data-duration-summary>{{ $selectedDuration['summary'] }}</span>
                            <span data-duration-recommended @class(['font-semibold text-[#e07a2f]', 'hidden' => ! $selectedDuration['recommended']])>(Recommended)</span>
                        </p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($durations as $duration)
                                <button
                                    type="button"
                                    data-duration
                                    data-summary="{{ $duration['summary'] }}"
                                    data-months="{{ $duration['months'] }}"
                                    data-price="{{ $duration['price'] }}"
                                    data-compare="{{ $duration['compare'] ?? '' }}"
                                    data-priced="{{ $duration['custom'] ? '1' : '0' }}"
                                    data-recommended="{{ $duration['recommended'] ? '1' : '0' }}"
                                    aria-pressed="{{ $duration === $selectedDuration ? 'true' : 'false' }}"
                                    @class([
                                        'rounded-md border-2 bg-white px-2.5 py-1 text-xs font-medium',
                                        'border-[#e07a2f] text-[#1a1a1a]' => $duration === $selectedDuration,
                                        'border-[#ece7e0] text-[#6f6a64]' => $duration !== $selectedDuration,
                                    ])
                                >{{ $duration['label'] }}</button>
                            @endforeach
                        </div>
                    </div>
                @elseif ($service->delivery_label)
                    <p class="mt-4 text-sm text-[#1a1a1a]">Duration: <span class="font-semibold">{{ $service->delivery_label }}</span></p>
                @endif

                @if ($audiences !== [])
                    <div class="mt-4">
                        <p class="text-sm font-semibold text-[#1a1a1a]">Target Audience:</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($audiences as $audience)
                                <button
                                    type="button"
                                    data-audience
                                    aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                    @class([
                                        'rounded-md border-2 bg-white px-2.5 py-1 text-xs font-medium',
                                        'border-[#e07a2f] text-[#1a1a1a]' => $loop->first,
                                        'border-[#ece7e0] text-[#6f6a64]' => ! $loop->first,
                                    ])
                                >{{ $audience }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <ul class="mt-4 flex flex-col gap-2">
                    @foreach ($service->deliverables() as $item)
                        <li class="flex items-start gap-2 text-sm text-[#1a1a1a]">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-[#1f9d55]" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.7-9.3a1 1 0 0 0-1.4-1.4L9 10.6 7.7 9.3a1 1 0 0 0-1.4 1.4l2 2a1 1 0 0 0 1.4 0l4-4Z" clip-rule="evenodd"/>
                            </svg>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>

                @if ($service->fullDeliverables() !== [])
                    <details class="mt-3">
                        <summary class="cursor-pointer list-none text-sm font-medium text-[#2f6fed]">
                            <span class="inline-flex items-center gap-1">
                                See full deliverables
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                            </span>
                        </summary>
                        <ul class="mt-3 flex flex-col gap-2 border-t border-[#f3eee6] pt-3 text-sm text-[#6f6a64]">
                            @foreach ($service->fullDeliverables() as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </div>

            <aside class="flex flex-col gap-3 lg:col-span-2 lg:sticky lg:top-4 xl:col-span-1">
                <div class="rounded-2xl border border-[#f0ebe3] bg-white p-4 shadow-[0_8px_24px_rgba(28,28,28,0.04)]">
                    <p class="text-[26px] leading-none font-bold tracking-tight text-[#1a1a1a]">
                        <span data-service-price>{{ $service->money($displayPrice) }}</span>@if ($service->billingShort())<span data-billing-suffix @class(['text-base font-semibold', 'hidden' => $selectedMonths !== 1])>{{ $service->billingShort() }}</span>@endif
                    </p>
                    <p class="mt-2.5 text-sm font-medium text-[#1f9d55]">{{ $service->stockLabel() }}</p>
                    <p class="mt-0.5 text-xs text-[#8a8680]">{{ $service->coverageLabel() }}</p>

                    <form method="POST" action="{{ route('cart.store', $service) }}" class="mt-3">
                        @csrf
                        @if ($selectedDuration)
                            <input type="hidden" name="months" value="{{ $selectedMonths }}" data-cart-months>
                        @endif
                        <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-full bg-[#f5b400] text-sm font-semibold text-[#1a1a1a]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <circle cx="8" cy="20" r="1.2" fill="currentColor" stroke="none"/>
                                <circle cx="18" cy="20" r="1.2" fill="currentColor" stroke="none"/>
                                <path d="M3 4h2l2.2 11.2a1.5 1.5 0 0 0 1.5 1.3H17"/>
                                <path d="M7 8h13l-1.4 6.5H8.2"/>
                            </svg>
                            Add to Cart
                        </button>
                    </form>
                    <form method="POST" action="{{ route('checkout.buy', $service) }}" class="mt-2">
                        @csrf
                        @if ($selectedDuration)
                            <input type="hidden" name="months" value="{{ $selectedMonths }}" data-cart-months>
                        @endif
                        <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-full bg-[#1a1a1a] text-sm font-semibold text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M13 2 4 14h7l-1 8 9-12h-7l1-8Z"/>
                            </svg>
                            Buy Now
                        </button>
                    </form>

                    <dl class="mt-3 grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-xs">
                        <dt class="text-[#8a8680]">Ships from</dt>
                        <dd class="text-right font-medium text-[#1a1a1a]">Sunrise Cloud</dd>
                        <dt class="text-[#8a8680]">Sold by</dt>
                        <dd class="text-right font-medium text-[#e07a2f]">Sunrise Premier Agency</dd>
                        <dt class="text-[#8a8680]">Delivery</dt>
                        <dd class="text-right font-medium text-[#1a1a1a]">Instant Access</dd>
                    </dl>

                    <a href="{{ route('quotes.create', $service) }}" class="mt-3 inline-flex h-10 w-full items-center justify-center rounded-lg bg-[#f3efe8] text-sm font-medium text-[#1a1a1a]">Request Custom Quote</a>
                    @php $saved = in_array($service->id, $wishlistIds ?? [], true); @endphp
                    <form method="POST" action="{{ route('wishlist.toggle', $service) }}" class="mt-2">
                        @csrf
                        <button type="submit" class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-[#ece7e0] bg-white text-sm font-medium text-[#1a1a1a]">
                            <svg class="h-4 w-4 {{ $saved ? 'text-[#e23b3b]' : '' }}" viewBox="0 0 24 24" fill="{{ $saved ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path d="M19.5 12.6 12 20l-7.5-7.4a4.5 4.5 0 0 1 6.4-6.3L12 7.2l1.1-1a4.5 4.5 0 0 1 6.4 6.4Z"/>
                            </svg>
                            {{ $saved ? 'Saved' : 'Add to Wishlist' }}
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl border border-[#f0ebe3] bg-white p-4">
                    <h2 class="text-[11px] font-semibold tracking-[0.14em] text-[#1a1a1a]">SUNRISE GUARANTEE</h2>
                    <ul class="mt-3 flex flex-col gap-3 text-sm">
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#e0a100]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <rect x="5" y="11" width="14" height="9" rx="2"/>
                                <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                            </svg>
                            <p>
                                <span class="block text-sm font-semibold">Secure Payment</span>
                                <span class="text-xs text-[#8a8680]">Escrow protection active</span>
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#e0a100]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/>
                                <path d="M14 3v6h6M8 13h8M8 17h5"/>
                            </svg>
                            <p>
                                <span class="block text-sm font-semibold">GST Invoice</span>
                                <span class="text-xs text-[#8a8680]">Available for input credit</span>
                            </p>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#e0a100]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path d="M4 14v-1a8 8 0 0 1 16 0v1"/>
                                <rect x="3" y="14" width="4" height="6" rx="1"/>
                                <rect x="17" y="14" width="4" height="6" rx="1"/>
                            </svg>
                            <p>
                                <span class="block text-sm font-semibold">Dedicated Support</span>
                                <span class="text-xs text-[#8a8680]">24/7 Slack channel access</span>
                            </p>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>

        <section class="mt-10 max-w-4xl">
            <h2 class="text-base font-bold text-[#1a1a1a]">{{ $service->reasonsHeading() }}</h2>
            <ol class="mt-4 list-decimal space-y-3 pl-5 text-sm leading-7 text-[#1a1a1a]">
                @foreach ($service->reasons() as $reason)
                    <li class="pl-1"><span class="font-bold">{{ $reason['title'] }}:</span> {{ $reason['body'] }}</li>
                @endforeach
            </ol>
        </section>

        <section class="mt-10">
            <div class="rounded-2xl border border-[#f0ebe3] bg-white p-5 shadow-[0_8px_24px_rgba(28,28,28,0.04)] sm:p-6">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#1a1a1a]">Customer reviews</h2>
                        @if ($reviewCount > 0)
                            @php
                                $fullStars = (int) floor($reviewAverage);
                                $halfStar = ($reviewAverage - $fullStars) >= 0.5;
                            @endphp
                            <p class="mt-3 flex flex-wrap items-center gap-x-2 gap-y-1">
                                <span class="text-[32px] leading-none font-bold tracking-tight text-[#1a1a1a]">{{ number_format($reviewAverage, 1) }}</span>
                                <span class="inline-flex tracking-tight" aria-hidden="true">
                                    @for ($star = 1; $star <= 5; $star++)
                                        @if ($star <= $fullStars)
                                            <span class="text-[#f5b400]">★</span>
                                        @elseif ($halfStar && $star === $fullStars + 1)
                                            <span class="relative inline-block text-[#e4ddd4]">★<span class="absolute inset-y-0 left-0 w-1/2 overflow-hidden text-[#f5b400]">★</span></span>
                                        @else
                                            <span class="text-[#e4ddd4]">★</span>
                                        @endif
                                    @endfor
                                </span>
                                <span class="text-sm text-[#6f6a64]">{{ $reviewCount }} {{ $reviewCount === 1 ? 'review' : 'reviews' }} from verified buyers</span>
                            </p>
                        @else
                            <p class="mt-2 text-sm text-[#6f6a64]">No customer reviews yet. Buyers can leave one after the order is placed.</p>
                        @endif
                    </div>
                    @if ($reviewCount > 0)
                        <div class="w-full max-w-sm">
                            @foreach ($reviewBreakdown as $star => $count)
                                <div class="mt-1.5 flex items-center gap-2 text-xs text-[#6f6a64]">
                                    <span class="w-8 shrink-0 text-[#1a1a1a]">{{ $star }} ★</span>
                                    <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-[#f3efe8]">
                                        <span class="block h-full rounded-full bg-[#f5b400]" style="width: {{ (int) round(($count / $reviewCount) * 100) }}%"></span>
                                    </span>
                                    <span class="w-4 text-right">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @auth
                    @if ($canReview)
                        @php $picked = (int) old('rating', $ownReview->rating ?? 5); @endphp
                        <form method="POST" action="{{ route('reviews.store', $service) }}" class="mt-6 rounded-2xl bg-[#f7f4ef] p-4 sm:p-5">
                            @csrf
                            <p class="text-sm font-semibold text-[#1a1a1a]">{{ $ownReview ? 'Update your review' : 'Write a review' }}</p>
                            <p class="mt-1 text-xs text-[#8a8680]">You bought this service, so your review is marked as a verified purchase.</p>
                            <div data-rating-picker class="mt-3 flex flex-wrap items-center gap-3">
                                <div class="flex gap-0.5" role="radiogroup" aria-label="Your rating">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <label data-star="{{ $star }}" @class(['cursor-pointer text-[28px] leading-none', 'text-[#f5b400]' => $star <= $picked, 'text-[#e4ddd4]' => $star > $picked])>
                                            <input type="radio" name="rating" value="{{ $star }}" class="sr-only" @checked($picked === $star) required>
                                            <span aria-hidden="true">★</span>
                                            <span class="sr-only">{{ $star }} of 5</span>
                                        </label>
                                    @endfor
                                </div>
                                <span data-rating-label class="text-sm font-medium text-[#1a1a1a]">{{ $picked }} of 5</span>
                            </div>
                            @error('rating')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
                            <label class="mt-3 block">
                                <span class="sr-only">Your review</span>
                                <textarea name="body" rows="4" required maxlength="1000" placeholder="What should the next buyer know?" class="w-full rounded-xl border border-[#ece7e0] bg-white px-3 py-2 text-sm leading-6 text-[#1a1a1a] outline-none focus:border-[#e0a100]">{{ old('body', $ownReview->body ?? '') }}</textarea>
                                @error('body')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
                            </label>
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#1a1a1a] px-5 text-sm font-semibold text-white">{{ $ownReview ? 'Update review' : 'Publish review' }}</button>
                                @if ($ownReview)
                                    <button type="submit" form="remove-review" class="text-sm font-medium text-[#9b2c2c]">Remove review</button>
                                @endif
                            </div>
                        </form>
                        @if ($ownReview)
                            <form id="remove-review" method="POST" action="{{ route('reviews.destroy', $ownReview) }}" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                    @else
                        <p class="mt-6 rounded-2xl bg-[#f7f4ef] px-4 py-3 text-sm text-[#6f6a64]">A review opens after you buy this service.</p>
                    @endif
                @else
                    <p class="mt-6 rounded-2xl bg-[#f7f4ef] px-4 py-3 text-sm text-[#6f6a64]"><a href="{{ route('login') }}" class="font-medium text-[#1a1a1a] underline">Log in</a> to review a service you have bought.</p>
                @endauth

                @if ($reviewCount > 0)
                    <div class="mt-6 flex flex-col gap-4">
                        @foreach ($service->reviews as $review)
                            <article @class(['flex gap-3 border-t border-[#f3eee6] pt-4', 'first:border-t-0 first:pt-0'])>
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#fff4e8] text-sm font-semibold text-[#e07a2f]" aria-hidden="true">{{ $review->initials() }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <p class="text-sm font-semibold text-[#1a1a1a]">{{ $review->user->name }}</p>
                                        @if ($ownReview && $review->is($ownReview))
                                            <span class="rounded-full bg-[#f3efe8] px-2 py-0.5 text-[11px] font-medium text-[#6f6a64]">You</span>
                                        @endif
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-[#1f9d55]">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.7-9.3a1 1 0 0 0-1.4-1.4L9 10.6 7.7 9.3a1 1 0 0 0-1.4 1.4l2 2a1 1 0 0 0 1.4 0l4-4Z" clip-rule="evenodd"/>
                                            </svg>
                                            Verified purchase
                                        </span>
                                    </div>
                                    <p class="mt-1 flex items-center gap-2 text-sm" aria-label="{{ $review->rating }} of 5">
                                        <span class="tracking-tight text-[#f5b400]" aria-hidden="true">
                                            @for ($star = 1; $star <= 5; $star++)
                                                {{ $star <= $review->rating ? '★' : '☆' }}
                                            @endfor
                                        </span>
                                        <span class="text-xs text-[#8a8680]">{{ $review->created_at->format('j M Y') }}</span>
                                    </p>
                                    <p class="mt-2 text-sm leading-6 text-[#3a3632]">{{ $review->body }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        
    </main>

    @include('partials.store-footer')
</body>
</html>
