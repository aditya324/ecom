<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.favicon')
    <title>{{ $plan->name }} — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto w-full max-w-3xl flex-1 px-6 py-10 sm:px-8">
        @if (session('status'))
            <p class="mb-6 rounded-xl border border-[#ead89a] bg-[#fff8e6] px-4 py-3 text-sm font-medium text-[#1a1a1a]">{{ session('status') }}</p>
        @endif

        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Package</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1a1a1a]">{{ $plan->name }}</h1>
        <p class="mt-3 text-sm text-[#6f6a64]">₹{{ number_format((float) $plan->monthly_price, 0) }} a month, or ₹{{ number_format((float) $plan->yearly_price / 12, 0) }} a month when billed yearly.</p>

        <ul class="mt-6 flex flex-col gap-3 rounded-2xl border border-[#f0ebe3] bg-white p-5">
            @foreach ($plan->items as $item)
                <li class="text-sm text-[#1a1a1a]">{{ $item->service->name }}</li>
            @endforeach
        </ul>

        <form method="POST" action="{{ route('plans.subscribe', $plan) }}" class="mt-4 flex flex-wrap gap-2">
            @csrf
            <button type="submit" name="interval" value="monthly" class="inline-flex h-11 items-center rounded-full border border-[#ece7e0] bg-white px-5 text-sm font-semibold text-[#1a1a1a]">Monthly</button>
            <button type="submit" name="interval" value="yearly" class="inline-flex h-11 items-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a]">Yearly</button>
        </form>

        <section class="mt-10 rounded-2xl border border-[#f0ebe3] bg-white p-5 sm:p-6">
            <h2 class="text-base font-bold text-[#1a1a1a]">Customer reviews</h2>
            @if ($reviewCount > 0)
                <p class="mt-2 text-sm text-[#6f6a64]">{{ number_format($reviewAverage, 1) }} from {{ $reviewCount }} {{ $reviewCount === 1 ? 'review' : 'reviews' }}</p>
            @else
                <p class="mt-2 text-sm text-[#6f6a64]">No customer reviews yet. Buyers can leave one after the package is placed.</p>
            @endif

            @auth
                @if ($canReview)
                    @php $picked = (int) old('rating', $ownReview->rating ?? 5); @endphp
                    <form method="POST" action="{{ route('packages.reviews.store', $plan) }}" class="mt-4 rounded-2xl bg-[#f7f4ef] p-4">
                        @csrf
                        <p class="text-sm font-semibold text-[#1a1a1a]">{{ $ownReview ? 'Update your review' : 'Write a review' }}</p>
                        <div data-rating-picker class="mt-3 flex items-center gap-3">
                            <div class="flex gap-0.5">
                                @for ($star = 1; $star <= 5; $star++)
                                    <label data-star="{{ $star }}" @class(['cursor-pointer text-[28px] leading-none', 'text-[#f5b400]' => $star <= $picked, 'text-[#e4ddd4]' => $star > $picked])>
                                        <input type="radio" name="rating" value="{{ $star }}" class="sr-only" @checked($picked === $star) required>
                                        <span aria-hidden="true">★</span>
                                        <span class="sr-only">{{ $star }} of 5</span>
                                    </label>
                                @endfor
                            </div>
                            <span data-rating-label class="text-sm font-medium">{{ $picked }} of 5</span>
                        </div>
                        <textarea name="body" rows="4" required maxlength="1000" class="mt-3 w-full rounded-xl border border-[#ece7e0] bg-white px-3 py-2 text-sm">{{ old('body', $ownReview->body ?? '') }}</textarea>
                        <div class="mt-3 flex items-center gap-3">
                            <button type="submit" class="inline-flex h-11 items-center rounded-full bg-[#1a1a1a] px-5 text-sm font-semibold text-white">{{ $ownReview ? 'Update review' : 'Publish review' }}</button>
                            @if ($ownReview)
                                <button type="submit" form="remove-package-review" class="text-sm font-medium text-[#9b2c2c]">Remove review</button>
                            @endif
                        </div>
                    </form>
                    @if ($ownReview)
                        <form id="remove-package-review" method="POST" action="{{ route('reviews.destroy', $ownReview) }}" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                @else
                    <p class="mt-4 text-sm text-[#6f6a64]">A review opens after you buy this package.</p>
                @endif
            @else
                <p class="mt-4 text-sm text-[#6f6a64]"><a href="{{ route('login') }}" class="font-medium underline">Log in</a> to review a package you have bought.</p>
            @endauth

            @foreach ($plan->reviews as $review)
                <article class="mt-4 border-t border-[#f3eee6] pt-4">
                    <p class="text-sm font-semibold text-[#1a1a1a]">{{ $review->user->name }}</p>
                    <p class="mt-1 text-sm text-[#f5b400]" aria-label="{{ $review->rating }} of 5">@for ($star = 1; $star <= 5; $star++){{ $star <= $review->rating ? '★' : '☆' }}@endfor</p>
                    <p class="mt-2 text-sm leading-6 text-[#3a3632]">{{ $review->body }}</p>
                </article>
            @endforeach
        </section>
    </main>

    @include('partials.store-footer')
</body>
</html>
