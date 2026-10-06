@extends('admin.layout')

@section('title', 'Orders')

@section('content')
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Orders</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">{{ $orders->count() }} {{ \Illuminate\Support\Str::plural('order', $orders->count()) }}</p>
    <p class="mt-4 text-3xl font-semibold tracking-tight text-[#1a1a1a]">{{ \App\Models\Order::formatMoney($collected) }}</p>
    <p class="mt-1 text-sm text-[#6f6a64]">Total collected, after refunds</p>

    @include('admin.partials.filters', [
        'action' => route('admin.orders.index'),
        'values' => $filters,
        'defaults' => ['sort' => 'newest'],
        'filtering' => $filtering,
        'count' => $orders->count(),
        'noun' => 'order',
        'fields' => [
            ['name' => 'q', 'type' => 'search', 'label' => 'Search', 'placeholder' => 'Order, customer, or email'],
            ['name' => 'status', 'type' => 'select', 'label' => 'Status', 'options' => ['' => 'Paid orders', 'all' => 'Every status', 'placed' => 'Placed', 'in_progress' => 'In progress', 'delivered' => 'Delivered', 'refunded' => 'Refunded', 'cancelled' => 'Cancelled', 'pending' => 'Pending']],
            ['name' => 'kind', 'type' => 'select', 'label' => 'Payment', 'options' => ['' => 'All payments', 'once' => 'One-time', 'subscription' => 'Subscription']],
            ['name' => 'min', 'type' => 'number', 'label' => 'Minimum total', 'placeholder' => 'Min ₹'],
            ['name' => 'max', 'type' => 'number', 'label' => 'Maximum total', 'placeholder' => 'Max ₹'],
            ['name' => 'sort', 'type' => 'select', 'label' => 'Sort', 'options' => ['newest' => 'Newest', 'oldest' => 'Oldest', 'total_desc' => 'Total: high to low', 'total_asc' => 'Total: low to high']],
        ],
    ])

    <div class="mt-6 overflow-hidden rounded-2xl border border-[#ebe6df] bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-[#ebe6df] text-xs uppercase tracking-wide text-[#8a8680]">
                <tr>
                    <th class="px-4 py-3 font-medium">Order</th>
                    <th class="px-4 py-3 font-medium">Customer</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Total</th>
                    <th class="px-4 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-b border-[#f3eee6] last:border-0">
                        <td class="px-4 py-3 font-medium text-[#1a1a1a]">{{ $order->number }}</td>
                        <td class="px-4 py-3">
                            <p>{{ $order->name }}</p>
                            <p class="text-xs text-[#8a8680]">{{ $order->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $order->statusLabel() }}</td>
                        <td class="px-4 py-3">{{ $order->money() }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-[#1a1a1a] underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-[#6f6a64]">{{ $filtering ? 'No orders match these filters.' : 'No orders yet.' }}</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($orders->isNotEmpty())
                <tfoot class="border-t border-[#ebe6df] bg-[#fbfaf8]">
                    <tr>
                        <td class="px-4 py-3 font-semibold text-[#1a1a1a]" colspan="3">{{ $filtering ? 'Showing' : 'Total' }}</td>
                        <td class="px-4 py-3 font-semibold text-[#1a1a1a]">{{ \App\Models\Order::formatMoney($shown) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
