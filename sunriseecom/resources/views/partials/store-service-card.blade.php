<article class="flex h-[485px] w-[264px] flex-col overflow-hidden rounded-[12px] bg-white shadow-[0_8px_24px_rgba(28,28,28,0.06)]">
    <div class="relative h-[210px] shrink-0 bg-[#ece7e0]">
        @if ($service->image)
            <a href="{{ route('services.show', $service) }}" class="block h-full" aria-label="View {{ $service->name }}">
                <img src="{{ asset('assets/services/'.$service->image) }}" alt="" class="h-full w-full object-cover">
            </a>
        @endif
        {{-- <img src="{{ asset('assets/logo/logo.png') }}" alt="" class="absolute top-3 left-3 h-8 w-auto rounded bg-white/90 px-1 py-0.5"> --}}
        @if ($service->catalogBadge() === 'bestseller')
            <span class="absolute bottom-3 left-3 rounded-md bg-[#1a1a1a] px-2 py-1 text-[10px] font-semibold tracking-[0.08em] text-white uppercase">Bestseller</span>
        @elseif ($service->catalogBadge() === 'verified')
            <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 rounded-md bg-[#2f6fed] px-2 py-1 text-[10px] font-semibold tracking-[0.06em] text-white uppercase">
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path d="M12 3 4.5 6v5.5c0 4.2 3.1 7.3 7.5 9.5 4.4-2.2 7.5-5.3 7.5-9.5V6L12 3Z"/>
                    <path d="m9 12 2 2 4-4"/>
                </svg>
                Verified Pro
            </span>
        @endif
        @php $saved = in_array($service->id, $wishlistIds ?? [], true); @endphp
        <form method="POST" action="{{ route('wishlist.toggle', $service) }}" class="absolute top-3 right-3">
            @csrf
            <button type="submit" class="grid h-8 w-8 place-items-center rounded-full bg-white text-[#1a1a1a] shadow-sm {{ $saved ? 'text-[#e23b3b]' : '' }}" aria-label="{{ $saved ? 'Remove '.$service->name.' from wishlist' : 'Save '.$service->name }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="{{ $saved ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path d="M19.5 12.6 12 20l-7.5-7.4a4.5 4.5 0 0 1 6.4-6.3L12 7.2l1.1-1a4.5 4.5 0 0 1 6.4 6.4Z"/>
                </svg>
            </button>
        </form>
    </div>
    <div class="flex flex-1 flex-col px-4 pt-4 pb-4">
        <div class="flex items-center justify-between gap-2">
            <p class="min-w-0 truncate text-[11px] font-semibold tracking-[0.08em] text-[#c4a035] uppercase">{{ $service->category->name }}</p>
            <p class="flex shrink-0 items-center gap-1 text-xs">
                
                <div class="flex gap-2 flex-row items-center text-sm text-[#564334] bg-[#FFF1E9] rounded-md px-2 py-1">
                <svg class="h-5 w-5 text-[#f5b400]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1L12 16.9 6.6 19.8l1-6.1L3.2 9.4l6.1-.9L12 3Z"/>
                </svg>
                    <span class="font-semibold text-[#1a1a1a]">{{ number_format((float) $service->rating, 1) }}</span>
                    <span class="text-[#8a8680]">({{ $service->reviewLabel() }})</span>
                </div>
            </p>
        </div>
        <h2 class="mt-2 line-clamp-2 text-[20px] leading-snug font-bold text-[#1a1a1a]">
            <a href="{{ route('services.show', $service) }}" class="hover:text-[#c4a035]">{{ $service->name }}</a>
        </h2>
        @if ($service->delivery_label)
            <p class="mt-3 flex items-center gap-1.5 font-inter text-sm text-[#8a8680]">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <circle cx="12" cy="12" r="8"/>
                    <path d="M12 8v4l2.5 2"/>
                </svg>
                {{ $service->delivery_label }}
            </p>
        @endif
        <div class="mt-4">
            @if ($service->compare_price)
                <p class="text-sm text-[#b0aaa4] line-through">{{ $service->money($service->compare_price) }}</p>
            @endif
            <p class="text-[22px] leading-none font-bold text-[#1a1a1a]">
                {{ $service->money($service->price) }}
                @if ($service->billingLabel())
                    <span class="text-sm font-normal text-[#8a8680]">{{ $service->billingLabel() }}</span>
                @endif
            </p>
            @if ($service->savingsPercent())
                <span class="mt-2 inline-block rounded-md bg-[#e7f8ee] px-1.5 py-0.5 text-[11px] font-semibold text-[#1f9d55]">Save {{ $service->savingsPercent() }}%</span>
            @endif
        </div>
        <div class="mt-auto flex items-center gap-2 pt-4">
            <form method="POST" action="{{ route('cart.store', $service) }}" class="flex-1">
                @csrf
                @if ($service->durations() !== [])
                    <input type="hidden" name="months" value="{{ $service->durations()[0]['months'] }}">
                @endif
                <button type="submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-[10px] bg-[#f5b400] text-sm font-semibold text-[#1a1a1a]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="8" cy="20" r="1.2" fill="currentColor" stroke="none"/>
                        <circle cx="18" cy="20" r="1.2" fill="currentColor" stroke="none"/>
                        <path d="M3 4h2l2.2 11.2a1.5 1.5 0 0 0 1.5 1.3H17"/>
                        <path d="M7 8h13l-1.4 6.5H8.2"/>
                    </svg>
                    Add to Cart
                </button>
            </form>
            <a href="{{ route('services.show', $service) }}" class="grid h-11 w-11 shrink-0 place-items-center rounded-[10px] border border-[#f0e6d4] bg-[#fffaf3] text-[#1a1a1a]" aria-label="View {{ $service->name }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M5 12h14"/>
                    <path d="m13 6 6 6-6 6"/>
                </svg>
            </a>
        </div>
    </div>
</article>
