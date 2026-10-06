@extends('admin.layout')

@section('title', 'Categories')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Categories</h1>
            <p class="mt-2 max-w-xl text-sm text-[#6f6a64]">These groups appear in the store menu and on the home page. Hide a category to take it off the store without deleting it.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="inline-flex h-11 items-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a] shadow-sm">New category</a>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-3">
        <a href="{{ route('admin.categories.index') }}" @class(['rounded-2xl border bg-white px-5 py-4', 'border-[#f5b400]' => ! $filtering, 'border-[#ebe6df]' => $filtering])>
            <p class="text-xs font-medium uppercase tracking-wide text-[#8a8680]">All</p>
            <p class="mt-1 text-2xl font-semibold text-[#1a1a1a]">{{ $counts['all'] }}</p>
        </a>
        <a href="{{ route('admin.categories.index', ['visibility' => 'visible']) }}" @class(['rounded-2xl border bg-white px-5 py-4', 'border-[#f5b400]' => $filters['visibility'] === 'visible' && $filters['q'] === '' && $filters['place'] === '', 'border-[#ebe6df]' => ! ($filters['visibility'] === 'visible' && $filters['q'] === '' && $filters['place'] === '')])>
            <p class="text-xs font-medium uppercase tracking-wide text-[#8a8680]">On the store</p>
            <p class="mt-1 text-2xl font-semibold text-[#1a1a1a]">{{ $counts['visible'] }}</p>
        </a>
        <a href="{{ route('admin.categories.index', ['visibility' => 'visible', 'place' => 'menu']) }}" @class(['rounded-2xl border bg-white px-5 py-4', 'border-[#f5b400]' => $filters['visibility'] === 'visible' && $filters['place'] === 'menu' && $filters['q'] === '', 'border-[#ebe6df]' => ! ($filters['visibility'] === 'visible' && $filters['place'] === 'menu' && $filters['q'] === '')])>
            <p class="text-xs font-medium uppercase tracking-wide text-[#8a8680]">In the menu</p>
            <p class="mt-1 text-2xl font-semibold text-[#1a1a1a]">{{ $counts['menu'] }}</p>
        </a>
    </div>

    @include('admin.partials.filters', [
        'action' => route('admin.categories.index'),
        'values' => $filters,
        'filtering' => $filtering,
        'count' => $categories->count(),
        'noun' => 'category',
        'fields' => [
            ['name' => 'q', 'type' => 'search', 'label' => 'Search', 'placeholder' => 'Search categories'],
            ['name' => 'visibility', 'type' => 'select', 'label' => 'Store', 'options' => ['' => 'Visible and hidden', 'visible' => 'Visible', 'hidden' => 'Hidden']],
            ['name' => 'place', 'type' => 'select', 'label' => 'Placement', 'options' => ['' => 'Any placement', 'menu' => 'In the menu', 'home' => 'On the home page', 'unplaced' => 'Not placed']],
        ],
    ])

    <div class="mt-6 overflow-hidden rounded-2xl border border-[#ebe6df] bg-white shadow-sm">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-[#fbfaf8] text-[#8a8680]">
                <tr>
                    <th class="px-5 py-3 font-medium">Category</th>
                    <th class="px-5 py-3 font-medium">Placement</th>
                    <th class="px-5 py-3 font-medium">Services</th>
                    <th class="px-5 py-3 font-medium">Store</th>
                    <th class="px-5 py-3 font-medium"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr class="border-t border-[#f3efe9]">
                        <td class="px-5 py-4" style="padding-left: {{ 1.25 + ($category->depth * 1.5) }}rem">
                            <div class="flex items-center gap-3">
                                @if ($category->image)
                                    <img src="{{ asset('assets/categories/'.$category->image) }}" alt="" class="h-10 w-10 rounded-lg object-cover">
                                @else
                                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-[#f6f3ee] text-xs font-semibold text-[#6f6a64]">{{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}</span>
                                @endif
                                <span>
                                    <span class="block font-semibold text-[#1a1a1a]">{{ $category->name }}</span>
                                    <span class="block text-xs text-[#8a8680]">{{ $category->parent->name ?? $category->slug }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @if ($category->show_in_nav)
                                    <span class="rounded-full bg-[#fff4d6] px-2.5 py-1 text-xs font-medium text-[#8a6500]">Menu</span>
                                @endif
                                @if ($category->show_on_home)
                                    <span class="rounded-full bg-[#f3efe9] px-2.5 py-1 text-xs font-medium text-[#6f6a64]">Home</span>
                                @endif
                                @if (! $category->show_in_nav && ! $category->show_on_home)
                                    <span class="text-xs text-[#b0aaa3]">Not placed</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[#6f6a64]">{{ $category->services_count }}</td>
                        <td class="px-5 py-4">
                            @if ($category->is_active)
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
                                <a href="{{ route('admin.categories.edit', $category) }}" class="inline-flex h-9 items-center rounded-full border border-[#e4e0da] px-3 text-sm font-medium text-[#1a1a1a] hover:bg-[#fbfaf8]">Edit</a>
                                @if ($category->is_active)
                                    <form method="POST" action="{{ route('admin.categories.hide', $category) }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}">
                                        @csrf
                                        <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#6f6a64] hover:bg-[#f6f3ee]">Hide</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.categories.restore', $category) }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}">
                                        @csrf
                                        <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#1a1a1a] hover:bg-[#fff4d6]">Show</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" data-confirm-title="Delete {{ $category->name }}?" data-confirm-body="Services saved in this category will be removed. This cannot be undone." data-confirm-accept="Delete" data-confirm-tone="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#9b2c2c] hover:bg-[#fdecec]">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-[#6f6a64]">{{ $filtering ? 'No categories match these filters.' : 'No categories yet.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
