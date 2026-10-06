@extends('admin.layout')

@section('title', 'Coupons')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Coupons</h1>
            <p class="mt-2 text-sm text-[#6f6a64]">A coupon discounts only the services you choose.</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}" class="inline-flex h-11 items-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a] shadow-sm">New coupon</a>
    </div>

    @include('admin.partials.filters', [
        'action' => route('admin.coupons.index'),
        'values' => $filters,
        'filtering' => $filtering,
        'count' => $coupons->count(),
        'noun' => 'coupon',
        'fields' => [
            ['name' => 'q', 'type' => 'search', 'label' => 'Search', 'placeholder' => 'Search coupon codes'],
            ['name' => 'type', 'type' => 'select', 'label' => 'Discount', 'options' => ['' => 'Percent and fixed', 'percent' => 'Percent off', 'fixed' => 'Fixed amount']],
            ['name' => 'visibility', 'type' => 'select', 'label' => 'Store', 'options' => ['' => 'Active and hidden', 'active' => 'Active', 'hidden' => 'Hidden']],
        ],
    ])

    <div class="mt-6 overflow-hidden rounded-2xl border border-[#ebe6df] bg-white shadow-sm">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-[#fbfaf8] text-[#8a8680]">
                <tr>
                    <th class="px-5 py-3 font-medium">Code</th>
                    <th class="px-5 py-3 font-medium">Discount</th>
                    <th class="px-5 py-3 font-medium">Uses</th>
                    <th class="px-5 py-3 font-medium">Services</th>
                    <th class="px-5 py-3 font-medium">Store</th>
                    <th class="px-5 py-3 font-medium"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($coupons as $coupon)
                    <tr class="border-t border-[#f3efe9]">
                        <td class="px-5 py-4 font-semibold text-[#1a1a1a]">{{ $coupon->code }}</td>
                        <td class="px-5 py-4">{{ $coupon->label() }}</td>
                        <td class="px-5 py-4">
                            {{ $coupon->uses_count }} of {{ $coupon->usage_limit }}
                            <span class="block text-xs text-[#8a8680]">{{ $coupon->per_user_limit }} per customer</span>
                        </td>
                        <td class="px-5 py-4 text-[#6f6a64]">{{ $coupon->services->pluck('name')->join(', ') }}</td>
                        <td class="px-5 py-4">
                            @if ($coupon->is_active)
                                <span class="inline-flex items-center gap-1.5 text-sm text-[#1a1a1a]">
                                    <span class="h-2 w-2 rounded-full bg-[#2f9e44]"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-sm text-[#8a8680]">
                                    <span class="h-2 w-2 rounded-full bg-[#c8c2ba]"></span>
                                    Hidden
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="inline-flex h-9 items-center rounded-full border border-[#e4e0da] px-3 text-sm font-medium text-[#1a1a1a] hover:bg-[#fbfaf8]">Edit</a>
                                @if ($coupon->is_active)
                                    <form method="POST" action="{{ route('admin.coupons.hide', $coupon) }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}">
                                        @csrf
                                        <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#6f6a64] hover:bg-[#f6f3ee]">Hide</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.coupons.restore', $coupon) }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}">
                                        @csrf
                                        <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#1a1a1a] hover:bg-[#fff4d6]">Show</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" data-confirm-title="Delete {{ $coupon->code }}?" data-confirm-body="Customers will not be able to use this code." data-confirm-accept="Delete" data-confirm-tone="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#9b2c2c] hover:bg-[#fdecec]">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-[#6f6a64]">{{ $filtering ? 'No coupons match these filters.' : 'No coupons yet.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
