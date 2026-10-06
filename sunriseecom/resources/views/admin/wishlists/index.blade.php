@extends('admin.layout')

@section('title', 'Wishlists')

@section('content')
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Wishlists</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">Services signed-in customers have saved.</p>

    @include('admin.partials.filters', [
        'action' => route('admin.wishlists.index'),
        'values' => $filters,
        'filtering' => $filtering,
        'count' => $wishlists->count(),
        'noun' => 'wishlist',
        'fields' => [
            ['name' => 'q', 'type' => 'search', 'label' => 'Search', 'placeholder' => 'Customer or service'],
        ],
    ])

    <div class="mt-8 flex flex-col gap-4">
        @forelse ($wishlists as $items)
            @php $customer = $items->first()->user; @endphp
            <section class="overflow-hidden rounded-2xl border border-[#ebe6df] bg-white">
                <div class="border-b border-[#f3eee6] px-5 py-4">
                    <p class="font-semibold text-[#1a1a1a]">{{ $customer->name }}</p>
                    <p class="text-sm text-[#8a8680]">{{ $customer->email }}</p>
                </div>
                <ul class="divide-y divide-[#f3eee6]">
                    @foreach ($items as $item)
                        <li class="flex items-start justify-between gap-4 px-5 py-4 text-sm">
                            <div>
                                <p class="font-medium text-[#1a1a1a]">{{ $item->service->name }}</p>
                                <p class="mt-1 text-[#6f6a64]">{{ $item->service->category?->name }}</p>
                            </div>
                            <p class="font-semibold text-[#1a1a1a]">{{ $item->service->money($item->service->price) }}</p>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-8 text-sm text-[#6f6a64]">{{ $filtering ? 'No wishlists match these filters.' : 'No wishlists yet.' }}</p>
        @endforelse
    </div>
@endsection
