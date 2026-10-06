<article class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm">
    <a href="{{ route('services.show', $deal) }}" class="relative block aspect-[16/10] bg-[#f3efe8]" aria-label="View {{ $deal->name }}">
        @if ($deal->image)
            <img src="{{ asset('assets/services/'.$deal->image) }}" alt="" class="h-full w-full object-cover">
        @endif
        @if ($deal->savingsPercent())
            <span class="absolute top-3 left-3 rounded-md bg-[#f5b400] px-2 py-1 text-xs font-semibold text-[#1a1a1a]">
                Save {{ $deal->savingsPercent() }}%
            </span>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-4">
        <h3 class="font-medium text-[#1a1a1a]">
            <a href="{{ route('services.show', $deal) }}" class="hover:text-[#c4a035]">{{ $deal->name }}</a>
        </h3>
        <p class="mt-1 text-sm leading-relaxed text-[#6f6a64]">{{ $deal->short_description }}</p>
        @if ($deal->dealEndsLabel())
            <p class="mt-2 text-sm font-medium text-[#c4a035]" @if ($deal->deal_ends_at?->isFuture()) data-deal-ends="{{ $deal->deal_ends_at->toIso8601String() }}" @endif>{{ $deal->dealEndsLabel() }}</p>
        @endif
        <div class="mt-auto flex items-end justify-between gap-3 pt-4">
            <p>
                @if ($deal->compare_price)
                    <span class="block text-sm text-[#b0aaa4] line-through">{{ $deal->money($deal->compare_price) }}</span>
                @endif
                <span class="text-lg font-semibold text-[#1a1a1a]">{{ $deal->money($deal->price) }}</span>
            </p>
            <form method="POST" action="{{ route('cart.store', $deal) }}">
                @csrf
                @if ($deal->durations() !== [])
                    <input type="hidden" name="months" value="{{ $deal->durations()[0]['months'] }}">
                @endif
                <button type="submit" class="rounded-lg bg-[#f5b400] px-4 py-2 text-sm font-medium text-[#1a1a1a]">
                    Add to Cart
                </button>
            </form>
        </div>
    </div>
</article>
