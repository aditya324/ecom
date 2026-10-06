<article class="rounded-2xl border border-[#ebe6df] bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="font-semibold text-[#1a1a1a]">{{ $subscription->name }}</p>
            <p class="mt-1 text-sm text-[#6f6a64]">{{ $subscription->statusLabel() }} · renews {{ $subscription->period }} · {{ $subscription->paid_count }} of {{ $subscription->total_count }} paid</p>
            @if ($subscription->razorpay_subscription_id)
                <p class="mt-1 text-xs text-[#8a8680]">Razorpay {{ $subscription->razorpay_subscription_id }}</p>
            @endif
        </div>
        <p class="font-semibold text-[#1a1a1a]">₹{{ number_format((float) $subscription->cycle_amount, 0, '.', ',') }}</p>
    </div>
    @if ($subscription->canManage())
        <div class="mt-4 flex flex-wrap gap-3">
            @if ($subscription->short_url)
                <a href="{{ $subscription->short_url }}" class="rounded-lg border border-[#ece7e0] px-3 py-2 text-sm font-medium text-[#1a1a1a]" target="_blank" rel="noopener">Update card</a>
            @endif
            <form method="POST" action="{{ route('subscriptions.cancel', $subscription) }}">
                @csrf
                <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium text-[#9b2c2c]">Cancel subscription</button>
            </form>
        </div>
    @endif
</article>
