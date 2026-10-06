<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout — {{ config('app.name', 'Sunrise') }}</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto w-full max-w-xl flex-1 px-6 py-10">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Checkout</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Place your order</h1>
        @if ($hasSubscription ?? false)
            <p class="mt-2 text-sm text-[#6f6a64]">Subscriptions renew automatically. Razorpay charges the first period today.</p>
        @endif

        @if ($errors->any())
            <p class="mt-4 rounded-xl border border-[#f0c9c4] bg-[#fff4f2] px-4 py-3 text-sm font-medium text-[#9b2c2c]">This order was not placed.</p>
        @endif

        <ul class="mt-6 divide-y divide-[#f0ebe3] rounded-2xl border border-[#f0ebe3] bg-white">
            @foreach ($items as $item)
                <li class="flex items-start justify-between gap-4 px-5 py-4">
                    <div>
                        <p class="font-semibold text-[#1a1a1a]">{{ $item['service']->name }}</p>
                        <p class="mt-1 text-sm text-[#6f6a64]">{{ $item['label'] }}</p>
                    </div>
                    <p class="font-semibold text-[#1a1a1a]">{{ $item['service']->money($item['price']) }}</p>
                </li>
            @endforeach
        </ul>
        @if ($bill['discount'] > 0)
            <p class="mt-4 text-right text-sm text-[#1f9d55]">Discount ({{ $bill['code'] }}) −₹{{ number_format($bill['discount'], 2, '.', ',') }}</p>
        @endif
        <p class="mt-2 text-right text-sm text-[#6f6a64]">Estimated GST (18%) ₹{{ number_format($bill['gst'], 2, '.', ',') }}</p>
        <p class="mt-2 text-right text-xl font-semibold text-[#1a1a1a]">@if ($hasSubscription ?? false)Due today @endif₹{{ number_format($bill['total'], 2, '.', ',') }}</p>

        <form method="POST" action="{{ route('checkout.store') }}" data-razorpay-checkout data-confirm="{{ route('checkout.payment') }}" data-cart="{{ route('cart.index') }}" class="mt-6 flex flex-col gap-4">
            @csrf
            <p data-checkout-error @unless ($errors->has('payment')) hidden @endunless class="rounded-xl border border-[#f0c9c4] bg-[#fff4f2] px-4 py-3 text-sm font-medium text-[#9b2c2c]">{{ $errors->first('payment') }}</p>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Name</span>
                <input type="text" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm">
                @error('name')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Email</span>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm">
                @error('email')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
            </label>
            @php $account = auth()->user(); @endphp
            @if ($account?->hasSavedBilling() && ! $errors->any())
                <input type="hidden" name="billing_state" value="{{ $account->billing_state }}">
                <input type="hidden" name="billing_address" value="{{ $account->billing_address }}">
                <input type="hidden" name="billing_city" value="{{ $account->billing_city }}">
                <input type="hidden" name="billing_pin" value="{{ $account->billing_pin }}">
                <input type="hidden" name="billing_gstin" value="{{ $account->billing_gstin }}">
                <p class="rounded-2xl bg-white px-4 py-3 text-sm text-[#3a3632]">Billed in {{ $account->billing_state }}@if ($account->billing_gstin) · GSTIN {{ $account->billing_gstin }}@endif. <a href="{{ route('profile.edit') }}" class="font-medium text-[#1a1a1a] underline">Change</a></p>
            @else
                <label class="block">
                    <span class="text-sm font-medium text-[#1a1a1a]">State</span>
                    <input type="text" name="billing_state" value="{{ old('billing_state', $account->billing_state ?? '') }}" required class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm">
                    <span class="mt-1 block text-xs text-[#8a8680]">Used as the place of supply on the invoice.</span>
                    @error('billing_state')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-[#1a1a1a]">GSTIN <span class="font-normal text-[#8a8680]">only if you need it on the invoice</span></span>
                    <input type="text" name="billing_gstin" value="{{ old('billing_gstin', $account->billing_gstin ?? '') }}" maxlength="15" class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm uppercase">
                    @error('billing_gstin')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-[#1a1a1a]">Billing address <span class="font-normal text-[#8a8680]">required with a GSTIN</span></span>
                    <input type="text" name="billing_address" value="{{ old('billing_address', $account->billing_address ?? '') }}" class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm">
                    @error('billing_address')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-medium text-[#1a1a1a]">City</span>
                        <input type="text" name="billing_city" value="{{ old('billing_city', $account->billing_city ?? '') }}" class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm">
                        @error('billing_city')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
                    </label>
                    <label class="block">
                        <span class="text-sm font-medium text-[#1a1a1a]">PIN code</span>
                        <input type="text" name="billing_pin" value="{{ old('billing_pin', $account->billing_pin ?? '') }}" inputmode="numeric" maxlength="6" class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm">
                        @error('billing_pin')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
                    </label>
                </div>
            @endif
            <button type="submit" data-checkout-button class="inline-flex h-11 items-center justify-center rounded-full bg-[#f5b400] text-sm font-semibold text-[#1a1a1a] disabled:opacity-60">Pay with Razorpay</button>
        </form>
    </main>

    @include('partials.store-footer')
</body>
</html>
