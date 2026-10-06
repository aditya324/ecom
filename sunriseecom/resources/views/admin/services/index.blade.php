@extends('admin.layout')

@section('title', 'Services')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Services</h1>
      
        </div>
        <a href="{{ route('admin.services.create') }}" class="inline-flex h-11 items-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a] shadow-sm">New service</a>
    </div>

    @php
        $filterQuery = request()->getQueryString() ? '?'.request()->getQueryString() : '';
        $billingLabels = ['one-time' => 'One-time', 'monthly' => 'Monthly', 'hourly' => 'Hourly'];
        $categoryOptions = ['' => 'All categories'];
        foreach ($categories as $category) {
            $categoryOptions[$category->id] = $category->parent ? $category->parent->name.' / '.$category->name : $category->name;
        }
        $only = fn (array $expected) => collect($filters)->reject(fn ($value, $key) => $value === null || $value === '' || ($key === 'sort' && $value === 'name'))->all() == $expected;
        $storeOnly = $only(['visibility' => 'visible']);
        $catalogOnly = $only(['visibility' => 'visible', 'listed' => 'listed']);
    @endphp

    <div class="mt-6 grid gap-3 sm:grid-cols-3">
        <a href="{{ route('admin.services.index') }}" @class(['rounded-2xl border bg-white px-5 py-4', 'border-[#f5b400]' => ! $filtering, 'border-[#ebe6df]' => $filtering])>
            <p class="text-xs font-medium uppercase tracking-wide text-[#8a8680]">All</p>
            <p class="mt-1 text-2xl font-semibold text-[#1a1a1a]">{{ $counts['all'] }}</p>
        </a>
        <a href="{{ route('admin.services.index', ['visibility' => 'visible']) }}" @class(['rounded-2xl border bg-white px-5 py-4', 'border-[#f5b400]' => $storeOnly, 'border-[#ebe6df]' => ! $storeOnly])>
            <p class="text-xs font-medium uppercase tracking-wide text-[#8a8680]">On the store</p>
            <p class="mt-1 text-2xl font-semibold text-[#1a1a1a]">{{ $counts['visible'] }}</p>
        </a>
        <a href="{{ route('admin.services.index', ['visibility' => 'visible', 'listed' => 'listed']) }}" @class(['rounded-2xl border bg-white px-5 py-4', 'border-[#f5b400]' => $catalogOnly, 'border-[#ebe6df]' => ! $catalogOnly])>
            <p class="text-xs font-medium uppercase tracking-wide text-[#8a8680]">In the catalog</p>
            <p class="mt-1 text-2xl font-semibold text-[#1a1a1a]">{{ $counts['listed'] }}</p>
        </a>
    </div>

    @include('admin.partials.filters', [
        'action' => route('admin.services.index'),
        'values' => $filters,
        'defaults' => ['sort' => 'name'],
        'filtering' => $filtering,
        'count' => $services->count(),
        'noun' => 'service',
        'fields' => [
            ['name' => 'q', 'type' => 'search', 'label' => 'Search', 'placeholder' => 'Search services'],
            ['name' => 'category', 'type' => 'select', 'label' => 'Category', 'options' => $categoryOptions],
            ['name' => 'billing', 'type' => 'select', 'label' => 'Billing', 'options' => ['' => 'All billing', 'one-time' => 'One-time', 'monthly' => 'Monthly', 'hourly' => 'Hourly']],
            ['name' => 'visibility', 'type' => 'select', 'label' => 'Store', 'options' => ['' => 'Visible and hidden', 'visible' => 'Visible', 'hidden' => 'Hidden']],
            ['name' => 'listed', 'type' => 'select', 'label' => 'Catalog', 'options' => ['' => 'Listed and unlisted', 'listed' => 'In the catalog', 'unlisted' => 'Not in the catalog']],
            ['name' => 'highlight', 'type' => 'select', 'label' => 'Highlight', 'options' => ['' => 'Any highlight', 'best-seller' => 'Best sellers', 'deal' => 'Deals']],
            ['name' => 'min', 'type' => 'number', 'label' => 'Minimum price', 'placeholder' => 'Min ₹'],
            ['name' => 'max', 'type' => 'number', 'label' => 'Maximum price', 'placeholder' => 'Max ₹'],
            ['name' => 'sort', 'type' => 'select', 'label' => 'Sort', 'options' => ['name' => 'Name', 'price_asc' => 'Price: low to high', 'price_desc' => 'Price: high to low', 'newest' => 'Newest']],
        ],
    ])

    <div class="mt-6 overflow-hidden rounded-2xl border border-[#ebe6df] bg-white shadow-sm">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-[#fbfaf8] text-[#8a8680]">
                <tr>
                    <th class="px-5 py-3 font-medium">Service</th>
                    <th class="px-5 py-3 font-medium">Category</th>
                    <th class="px-5 py-3 font-medium">Price</th>
                    <th class="px-5 py-3 font-medium">Billing</th>
                    <th class="px-5 py-3 font-medium">Store</th>
                    <th class="px-5 py-3 font-medium"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr class="border-t border-[#f3efe9]">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if ($service->image)
                                    <img src="{{ asset('assets/services/'.$service->image) }}" alt="" class="h-10 w-10 rounded-lg object-cover">
                                @else
                                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-[#f6f3ee] text-xs font-semibold text-[#6f6a64]">{{ mb_strtoupper(mb_substr($service->name, 0, 1)) }}</span>
                                @endif
                                <span>
                                    <span class="block font-semibold text-[#1a1a1a]">{{ $service->name }}</span>
                                    <span class="block text-xs text-[#8a8680]">{{ $service->slug }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[#6f6a64]">{{ $service->category->name }}</td>
                        <td class="px-5 py-4 text-[#1a1a1a]">
                            {{ $service->money($service->price) }}
                            @if ($service->is_best_seller)
                                <span class="mt-1 block text-xs text-[#8a8680]">Best seller</span>
                            @endif
                            @if ($service->is_deal)
                                <span class="mt-1 block text-xs text-[#8a8680]">Deal</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-[#6f6a64]">{{ $billingLabels[$service->billing_type] ?? 'One-time' }}</td>
                        <td class="px-5 py-4">
                            @if ($service->is_active)
                                <span class="inline-flex items-center gap-1.5 text-sm text-[#1a1a1a]">
                                    <span class="h-2 w-2 rounded-full bg-[#2f9e44]"></span>
                                    Visible
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
                                <a href="{{ route('admin.services.edit', $service) }}" class="inline-flex h-9 items-center rounded-full border border-[#e4e0da] px-3 text-sm font-medium text-[#1a1a1a] hover:bg-[#fbfaf8]">Edit</a>
                                @if ($service->is_active)
                                    <form method="POST" action="{{ route('admin.services.hide', $service) }}{{ $filterQuery }}">
                                        @csrf
                                        <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#6f6a64] hover:bg-[#f6f3ee]">Hide</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.services.restore', $service) }}{{ $filterQuery }}">
                                        @csrf
                                        <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#1a1a1a] hover:bg-[#fff4d6]">Show</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.services.destroy', $service) }}{{ $filterQuery }}" data-confirm-title="Delete {{ $service->name }}?" data-confirm-body="This service will be removed from the store. This cannot be undone." data-confirm-accept="Delete" data-confirm-tone="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#9b2c2c] hover:bg-[#fdecec]">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-sm text-[#6f6a64]">
                            @if ($filtering)
                                No services match these filters.
                            @else
                                No services yet.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
