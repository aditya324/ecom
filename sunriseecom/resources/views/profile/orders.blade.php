@extends('profile.layout')

@section('title', 'Orders')

@section('content')
    <h1 class="text-3xl font-semibold tracking-tight text-[#1a1a1a]">Orders</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">Orders placed from this account.</p>

    @if ($errors->has('subscription'))
        <p class="mt-4 rounded-xl border border-[#f0c9c4] bg-[#fff4f2] px-4 py-3 text-sm font-medium text-[#9b2c2c]">{{ $errors->first('subscription') }}</p>
    @endif

    @if ($subscriptions->isNotEmpty())
        <div class="mt-6 flex flex-col gap-4">
            <h2 class="text-sm font-semibold uppercase tracking-[0.14em] text-[#8a8680]">Subscriptions</h2>
            @foreach ($subscriptions as $subscription)
                @include('partials.subscription-card', ['subscription' => $subscription])
            @endforeach
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-4">
        @forelse ($orders as $order)
            <article class="rounded-2xl border border-[#ebe6df] bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <a href="{{ route('orders.show', $order) }}" class="min-w-0 flex-1">
                        <p class="font-semibold text-[#1a1a1a]">{{ $order->number }}</p>
                        <p class="mt-1 text-sm text-[#6f6a64]">{{ $order->created_at->format('d M Y') }} · {{ $order->statusLabel() }}</p>
                        @if ($order->customer_note)
                            <p class="mt-2 text-sm text-[#1a1a1a]">{{ $order->customer_note }}</p>
                        @endif
                        <ul class="mt-3 text-sm text-[#6f6a64]">
                            @foreach ($order->items as $item)
                                <li>{{ $item->service_name }} · {{ $item->duration_label }}</li>
                            @endforeach
                        </ul>
                    </a>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-[#1a1a1a]">{{ $order->money() }}</p>
                        <a href="{{ route('orders.invoice', $order) }}" class="mt-2 inline-flex text-sm font-medium text-[#c4a035]">Download invoice</a>
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-8 text-sm text-[#6f6a64]">You have not placed an order yet.</p>
        @endforelse
    </div>
@endsection
