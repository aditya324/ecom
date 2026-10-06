@extends('admin.layout')

@section('title', 'Customers')

@section('content')
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Customers</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">Accounts, with the orders and subscriptions on each one.</p>

    @include('admin.partials.filters', [
        'action' => route('admin.customers.index'),
        'values' => $filters,
        'filtering' => $filtering,
        'count' => $customers->count(),
        'noun' => 'customer',
        'fields' => [
            ['name' => 'q', 'type' => 'search', 'label' => 'Search', 'placeholder' => 'Name or email'],
        ],
    ])

    <div class="mt-6 overflow-hidden rounded-2xl border border-[#ebe6df] bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-[#ebe6df] text-xs uppercase tracking-wide text-[#8a8680]">
                <tr>
                    <th class="px-4 py-3 font-medium">Customer</th>
                    <th class="px-4 py-3 font-medium">Orders</th>
                    <th class="px-4 py-3 font-medium">Subscriptions</th>
                    <th class="px-4 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr class="border-b border-[#f3eee6] last:border-0">
                        <td class="px-4 py-3">
                            <p class="font-medium text-[#1a1a1a]">{{ $customer->name }}</p>
                            <p class="text-xs text-[#8a8680]">{{ $customer->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $customer->placed_orders_count }}</td>
                        <td class="px-4 py-3">{{ $customer->subscriptions_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="font-medium text-[#1a1a1a] underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-[#6f6a64]">{{ $filtering ? 'No customers match these filters.' : 'No customers yet.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
