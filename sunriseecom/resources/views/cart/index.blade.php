@php
    $count = array_sum(array_column($items, 'quantity'));
    $money = fn (float $amount) => '₹'.number_format($amount, 0, '.', ',');
    $summary = fn (float $amount) => '₹'.number_format($amount, 2, '.', ',');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cart — {{ config('app.name', 'Sunrise') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-[#f7f4ef] text-[#1c1c1c] antialiased">
    @include('partials.store-header')

    <main class="mx-auto w-full max-w-6xl flex-1 px-6 py-8 sm:px-8">
        <div class="flex items-center justify-between gap-4">
            <h1 class="text-4xl font-bold tracking-tight text-[#1a1a1a]">Your Cart</h1>
            <p class="rounded-full border border-[#ece7e0] bg-white px-4 py-1.5 text-sm text-[#6f6a64]">{{ $count }} {{ $count === 1 ? 'Item' : 'Items' }}</p>
        </div>

        @if (session('status'))
            <p class="mt-4 rounded-xl border border-[#ead89a] bg-[#fff8e6] px-4 py-3 text-sm font-medium text-[#1a1a1a]">{{ session('status') }}</p>
        @endif

        @if ($items === [] && $saved === [])
            <p class="mt-8 text-sm text-[#6f6a64]">Your cart is empty.</p>
            <a href="{{ route('home') }}" class="mt-4 inline-flex h-11 items-center rounded-full bg-[#1a1a1a] px-5 text-sm font-semibold text-white">Browse services</a>
        @else
            <div class="mt-8 grid items-start gap-6 md:grid-cols-[minmax(0,1fr)_320px]">
                <div class="flex flex-col gap-4">
                    @foreach ($items as $item)
                        <article class="rounded-2xl border border-[#f0ebe3] bg-white p-4 shadow-[0_8px_24px_rgba(28,28,28,0.04)] sm:p-5">
                            <div class="flex gap-4">
                                <a href="{{ route('services.show', $item['service']) }}" class="h-20 w-28 shrink-0 overflow-hidden rounded-xl bg-[#ece7e0]">
                                    @if ($item['service']->image)
                                        <img src="{{ asset('assets/services/'.$item['service']->image) }}" alt="" class="h-full w-full object-cover">
                                    @endif
                                </a>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-3">
                                        @php
                                            $monthly = $item['service']->billing_type === 'monthly';
                                            $shown = $monthly ? $item['due'] : $item['price'];
                                            $compare = $item['compare'];
                                            if ($monthly && ($item['cycles'] ?? 1) > 1 && $compare) {
                                                $compare = $compare / $item['cycles'];
                                            }
                                        @endphp
                                        <div class="min-w-0">
                                            <h2 class="truncate text-base font-bold text-[#1a1a1a]">{{ $item['service']->name }}</h2>
                                            <p class="mt-1 text-[11px] font-medium tracking-[0.12em] text-[#8a8680] uppercase">
                                                {{ $item['service']->category?->name }}
                                                <span aria-hidden="true"> · </span>
                                                {{ $item['label'] }}
                                            </p>
                                            <p class="mt-1 text-xs text-[#6f6a64]">
                                                @if ($monthly && ($item['cycles'] ?? 1) > 1)
                                                    Renews monthly for {{ $item['cycles'] }} months
                                                @elseif ($monthly)
                                                    Billed monthly
                                                @else
                                                    Pay once
                                                @endif
                                            </p>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <p class="text-lg font-bold text-[#e0a100]">{{ $money($shown * $item['quantity']) }}</p>
                                            @if ($monthly)
                                                <p class="text-[11px] font-medium tracking-[0.12em] text-[#8a8680] uppercase">/ month</p>
                                            @endif
                                            @if ($compare)
                                                <p class="text-sm text-[#b0aaa4] line-through">{{ $money($compare * $item['quantity']) }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                        <div class="inline-flex items-center rounded-lg border border-[#ece7e0] bg-white">
                                            <form method="POST" action="{{ route('cart.quantity') }}">
                                                @csrf
                                                <input type="hidden" name="key" value="{{ $item['key'] }}">
                                                <input type="hidden" name="quantity" value="{{ max(1, $item['quantity'] - 1) }}">
                                                <button type="submit" class="grid h-9 w-9 place-items-center text-lg text-[#6f6a64]" aria-label="Decrease quantity">−</button>
                                            </form>
                                            <span class="w-6 text-center text-sm font-semibold text-[#1a1a1a]">{{ $item['quantity'] }}</span>
                                            <form method="POST" action="{{ route('cart.quantity') }}">
                                                @csrf
                                                <input type="hidden" name="key" value="{{ $item['key'] }}">
                                                <input type="hidden" name="quantity" value="{{ min(99, $item['quantity'] + 1) }}">
                                                <button type="submit" class="grid h-9 w-9 place-items-center text-lg text-[#6f6a64]" aria-label="Increase quantity">+</button>
                                            </form>
                                        </div>
                                        <div class="flex items-center gap-4 text-sm">
                                            <form method="POST" action="{{ route('cart.save') }}">
                                                @csrf
                                                <input type="hidden" name="key" value="{{ $item['key'] }}">
                                                <button type="submit" class="inline-flex items-center gap-1.5 text-[#8a8680]">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                                        <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                                    </svg>
                                                    Save for later
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('cart.destroy') }}">
                                                @csrf
                                                <input type="hidden" name="key" value="{{ $item['key'] }}">
                                                <button type="submit" class="font-medium text-[#e24b4b]">Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    @if ($saved !== [])
                        <section class="mt-4">
                            <h2 class="flex items-center gap-2 text-base font-semibold text-[#1a1a1a]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                </svg>
                                Saved for Later ({{ count($saved) }})
                            </h2>
                            <div class="mt-3 flex flex-col gap-3">
                                @foreach ($saved as $item)
                                    <article class="rounded-2xl border border-[#f0ebe3] bg-[#f3f0ea] p-4">
                                        <div class="flex gap-4">
                                            <a href="{{ route('services.show', $item['service']) }}" class="h-16 w-24 shrink-0 overflow-hidden rounded-xl bg-[#e7e1d8]">
                                                @if ($item['service']->image)
                                                    <img src="{{ asset('assets/services/'.$item['service']->image) }}" alt="" class="h-full w-full object-cover">
                                                @endif
                                            </a>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <h3 class="truncate font-semibold text-[#1a1a1a]">{{ $item['service']->name }}</h3>
                                                        <p class="mt-1 text-[11px] font-medium tracking-[0.12em] text-[#8a8680] uppercase">{{ $item['service']->category?->name }} · {{ $item['label'] }}</p>
                                                    </div>
                                                    <p class="shrink-0 font-bold text-[#e0a100]">{{ $money($item['price'] * $item['quantity']) }}</p>
                                                </div>
                                                <div class="mt-3 flex items-center gap-4 text-sm">
                                                    <form method="POST" action="{{ route('cart.move') }}">
                                                        @csrf
                                                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                                                        <button type="submit" class="font-medium text-[#e0a100]">Move to Cart</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('cart.saved.destroy') }}">
                                                        @csrf
                                                        <input type="hidden" name="key" value="{{ $item['key'] }}">
                                                        <button type="submit" class="font-medium text-[#e24b4b]">Remove</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                @if ($items !== [])
                    <aside class="rounded-2xl border border-[#f0ebe3] bg-white p-5 shadow-[0_8px_24px_rgba(28,28,28,0.04)] md:sticky md:top-6">
                        <h2 class="text-lg font-bold text-[#1a1a1a]">Order Summary</h2>

                        <form method="POST" action="{{ route('cart.coupon') }}" class="mt-4 flex gap-2">
                            @csrf
                            <label class="sr-only" for="coupon-code">Coupon code</label>
                            <input id="coupon-code" name="code" type="text" placeholder="ENTER COUPON CODE" class="h-11 min-w-0 flex-1 rounded-lg border border-[#ece7e0] px-3 text-sm tracking-[0.08em] placeholder:text-[11px] placeholder:tracking-[0.14em] placeholder:text-[#b0aaa4]">
                            <button type="submit" class="h-11 shrink-0 rounded-lg bg-[#1a1a1a] px-4 text-xs font-semibold tracking-wide text-white">APPLY</button>
                        </form>
                        @error('code')
                            <p class="mt-2 text-xs text-[#e24b4b]">{{ $message }}</p>
                        @enderror
                        @if ($coupon)
                            <p class="mt-2 flex items-center justify-between text-xs text-[#1f9d55]">
                                <span>{{ $coupon->code }} applied</span>
                                <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                    @csrf
                                    <button type="submit" class="font-medium text-[#8a8680] underline">Remove</button>
                                </form>
                            </p>
                        @endif

                        @php
                            $onceRows = [];
                            $monthRows = [];
                            foreach ($items as $index => $item) {
                                $row = ['item' => $item, 'line' => $bill['lines'][$index]];
                                if ($item['service']->billing_type === 'monthly') {
                                    $monthRows[] = $row;
                                } else {
                                    $onceRows[] = $row;
                                }
                            }
                        @endphp
                        <div class="mt-5 space-y-4 text-sm">
                            @if ($onceRows !== [])
                                <div>
                                    <p class="text-[11px] font-semibold tracking-[0.14em] text-[#8a8680] uppercase">Pay once</p>
                                    <dl class="mt-2 space-y-2">
                                        @foreach ($onceRows as $row)
                                            <div class="flex items-start justify-between gap-3">
                                                <dt class="text-[#1a1a1a]">{{ $row['item']['service']->name }}</dt>
                                                <dd class="shrink-0 font-medium text-[#1a1a1a]">{{ $summary($row['line']['taxable']) }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>
                            @endif
                            @if ($monthRows !== [])
                                <div>
                                    <p class="text-[11px] font-semibold tracking-[0.14em] text-[#8a8680] uppercase">Subscription</p>
                                    <dl class="mt-2 space-y-2">
                                        @foreach ($monthRows as $row)
                                            <div>
                                                <div class="flex items-start justify-between gap-3">
                                                    <dt class="text-[#1a1a1a]">{{ $row['item']['service']->name }}</dt>
                                                    <dd class="shrink-0 font-medium text-[#1a1a1a]">{{ $summary($row['line']['taxable']) }}<span class="text-[11px] font-medium tracking-[0.08em] text-[#8a8680]"> /mo</span></dd>
                                                </div>
                                                <p class="mt-1 text-xs text-[#6f6a64]">
                                                    @if (($row['item']['cycles'] ?? 1) > 1)
                                                        Renews monthly for {{ $row['item']['cycles'] }} months
                                                    @else
                                                        Billed monthly
                                                    @endif
                                                </p>
                                            </div>
                                        @endforeach
                                    </dl>
                                </div>
                            @endif
                            @if ($bill['discount'] > 0)
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-[#1f9d55]">Discount ({{ $coupon->code }})</p>
                                    <p class="font-medium text-[#1f9d55]">−{{ $summary($bill['discount']) }}</p>
                                </div>
                            @endif
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-[#6f6a64]">Estimated GST (18%)</p>
                                <p class="font-medium text-[#1a1a1a]">{{ $summary($bill['gst']) }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-end justify-between border-t border-[#f3eee6] pt-4">
                            <p class="text-base font-semibold text-[#1a1a1a]">{{ $monthRows === [] ? 'Total' : 'Due today' }}</p>
                            <p class="text-3xl font-bold tracking-tight text-[#f5b400]">{{ $summary($bill['total']) }}</p>
                        </div>
                        @if ($monthRows !== [])
                            <p class="mt-2 text-xs text-[#6f6a64]">Subscriptions are charged again each month. One-time services are paid only today.</p>
                        @endif

                        <a href="{{ route('checkout.create') }}" class="mt-5 inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#1a1a1a] text-sm font-semibold text-white">
                            Proceed to Checkout
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                        </a>

                        <ul class="mt-4 space-y-2 rounded-xl bg-[#f7f4ef] px-3 py-3 text-xs text-[#6f6a64]">
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-[#1f9d55]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M12 3 5 6v6c0 4.5 3 7.5 7 9 4-1.5 7-4.5 7-9V6l-7-3Z"/></svg>
                                Bank-grade Secure Checkout
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-[#2f6fed]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5"/></svg>
                                GST Invoicing Available
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-[#e0a100]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                100% Satisfaction Guarantee
                            </li>
                        </ul>
                    </aside>
                @endif
            </div>
        @endif
    </main>

    @include('partials.store-footer')
</body>
</html>
