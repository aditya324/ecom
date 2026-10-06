@extends('admin.layout')

@section('title', $customer->name)

@section('content')
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Customer</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">{{ $customer->name }}</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">{{ $customer->email }}</p>
    <a href="{{ route('admin.customers.index') }}" class="mt-4 inline-flex text-sm font-medium text-[#1a1a1a] underline">All customers</a>

    <h2 class="mt-8 text-lg font-semibold text-[#1a1a1a]">Orders</h2>
    <div class="mt-3 overflow-hidden rounded-2xl border border-[#ebe6df] bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-[#ebe6df] text-xs uppercase tracking-wide text-[#8a8680]">
                <tr>
                    <th class="px-4 py-3 font-medium">Order</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Total</th>
                    <th class="px-4 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-b border-[#f3eee6] last:border-0">
                        <td class="px-4 py-3 font-medium text-[#1a1a1a]">{{ $order->number }}</td>
                        <td class="px-4 py-3">{{ ucfirst($order->status) }}</td>
                        <td class="px-4 py-3">{{ $order->money() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-[#1a1a1a] underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-[#6f6a64]">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2 class="mt-8 text-lg font-semibold text-[#1a1a1a]">Subscriptions</h2>
    <div class="mt-3 flex flex-col gap-3">
        @forelse ($subscriptions as $subscription)
            <article class="rounded-2xl border border-[#ebe6df] bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-semibold text-[#1a1a1a]">{{ $subscription->name }}</p>
                        <p class="mt-1 text-sm text-[#6f6a64]">{{ $subscription->statusLabel() }} · renews {{ $subscription->period }} · {{ $subscription->paid_count }} of {{ $subscription->total_count }} paid</p>
                    </div>
                    <p class="font-semibold text-[#1a1a1a]">₹{{ number_format((float) $subscription->cycle_amount, 0, '.', ',') }}</p>
                </div>
                @if ($subscription->order)
                    <a href="{{ route('admin.orders.show', $subscription->order) }}" class="mt-3 inline-flex text-sm font-medium text-[#1a1a1a] underline">Order {{ $subscription->order->number }}</a>
                @endif
            </article>
        @empty
            <p class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-8 text-sm text-[#6f6a64]">No subscriptions yet.</p>
        @endforelse
    </div>
@endsection
