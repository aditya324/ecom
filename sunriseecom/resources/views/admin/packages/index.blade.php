@extends('admin.layout')

@section('title', 'Packages')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Packages</h1>
        </div>
        <a href="{{ route('admin.packages.create') }}" class="inline-flex h-11 items-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a] shadow-sm">New package</a>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-2">
        <div class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-4">
            <p class="text-xs font-medium uppercase tracking-wide text-[#8a8680]">All</p>
            <p class="mt-1 text-2xl font-semibold text-[#1a1a1a]">{{ $counts['all'] }}</p>
        </div>
        <div class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-4">
            <p class="text-xs font-medium uppercase tracking-wide text-[#8a8680]">On the store</p>
            <p class="mt-1 text-2xl font-semibold text-[#1a1a1a]">{{ $counts['visible'] }}</p>
        </div>
    </div>

    @include('admin.partials.filters', [
        'action' => route('admin.packages.index'),
        'values' => $filters,
        'defaults' => ['sort' => 'name'],
        'filtering' => $filtering,
        'count' => $packages->count(),
        'noun' => 'package',
        'fields' => [
            ['name' => 'q', 'type' => 'search', 'label' => 'Search', 'placeholder' => 'Search packages'],
            ['name' => 'visibility', 'type' => 'select', 'label' => 'Store', 'options' => ['' => 'Visible and hidden', 'visible' => 'Visible', 'hidden' => 'Hidden']],
            ['name' => 'min', 'type' => 'number', 'label' => 'Minimum monthly price', 'placeholder' => 'Min ₹'],
            ['name' => 'max', 'type' => 'number', 'label' => 'Maximum monthly price', 'placeholder' => 'Max ₹'],
            ['name' => 'sort', 'type' => 'select', 'label' => 'Sort', 'options' => ['name' => 'Name', 'price_asc' => 'Monthly: low to high', 'price_desc' => 'Monthly: high to low']],
        ],
    ])

    <div class="mt-6 overflow-hidden rounded-2xl border border-[#ebe6df] bg-white shadow-sm">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-[#fbfaf8] text-[#8a8680]">
                <tr>
                    <th class="px-5 py-3 font-medium">Package</th>
                    <th class="px-5 py-3 font-medium">Monthly</th>
                    <th class="px-5 py-3 font-medium">Yearly</th>
                    <th class="px-5 py-3 font-medium">Store</th>
                    <th class="px-5 py-3 font-medium"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($packages as $package)
                    <tr class="border-t border-[#f3efe9]">
                        <td class="px-5 py-4">
                            <span class="block font-semibold text-[#1a1a1a]">{{ $package->name }}</span>
                            <span class="mt-1 block text-xs text-[#8a8680]">{{ $package->items->map(fn ($item) => $item->service?->name)->filter()->join(', ') }}</span>
                        </td>
                        <td class="px-5 py-4 text-[#1a1a1a]">₹{{ number_format((float) $package->monthly_price, 0, '.', ',') }}</td>
                        <td class="px-5 py-4 text-[#1a1a1a]">₹{{ number_format((float) $package->yearly_price, 0, '.', ',') }}</td>
                        <td class="px-5 py-4">
                            @if ($package->is_active)
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
                                <a href="{{ route('admin.packages.edit', $package) }}" class="inline-flex h-9 items-center rounded-full border border-[#e4e0da] px-3 text-sm font-medium text-[#1a1a1a] hover:bg-[#fbfaf8]">Edit</a>
                                @if ($package->is_active)
                                    <form method="POST" action="{{ route('admin.packages.hide', $package) }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}">
                                        @csrf
                                        <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#6f6a64] hover:bg-[#f6f3ee]">Hide</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.packages.restore', $package) }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}">
                                        @csrf
                                        <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#1a1a1a] hover:bg-[#fff4d6]">Show</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.packages.destroy', $package) }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" data-confirm-title="Delete {{ $package->name }}?" data-confirm-body="This package will be removed from the store. This cannot be undone." data-confirm-accept="Delete" data-confirm-tone="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#9b2c2c] hover:bg-[#fdecec]">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-[#6f6a64]">{{ $filtering ? 'No packages match these filters.' : 'No packages yet.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
