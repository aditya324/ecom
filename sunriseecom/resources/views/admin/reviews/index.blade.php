@extends('admin.layout')

@section('title', 'Reviews')

@section('content')
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Requests</p>
        <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Reviews</h1>
    </div>

    @include('admin.partials.filters', [
        'action' => route('admin.reviews.index'),
        'values' => $filters,
        'filtering' => $filtering,
        'count' => $reviews->count(),
        'noun' => 'review',
        'fields' => [
            ['name' => 'q', 'type' => 'search', 'label' => 'Search', 'placeholder' => 'Name, email, or review'],
            ['name' => 'visibility', 'type' => 'select', 'label' => 'Visibility', 'options' => ['' => 'Visible and hidden', 'visible' => 'Visible', 'hidden' => 'Hidden']],
        ],
    ])

    <div class="mt-6 flex flex-col gap-3">
        @forelse ($reviews as $review)
            <article class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-[#1a1a1a]">{{ $review->user->name }} · {{ $review->rating }} of 5</p>
                        <p class="mt-1 text-xs text-[#8a8680]">
                            {{ $review->service->name ?? $review->plan->name ?? 'Removed' }}
                            · {{ $review->is_hidden ? 'Hidden' : 'Visible' }}
                            · {{ $review->created_at->format('j M Y') }}
                        </p>
                        <p class="mt-3 text-sm leading-6 text-[#3a3632]">{{ $review->body }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('admin.reviews.hide', $review) }}">
                            @csrf
                            <button type="submit" class="inline-flex h-9 items-center rounded-full border border-[#ece7e0] px-3 text-sm font-medium">{{ $review->is_hidden ? 'Show' : 'Hide' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" data-confirm-title="Delete this review?" data-confirm-body="The review will be removed from the store." data-confirm-accept="Delete" data-confirm-tone="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex h-9 items-center rounded-full border border-[#9b2c2c] px-3 text-sm font-medium text-[#9b2c2c]">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-8 text-sm text-[#6f6a64]">No reviews yet.</p>
        @endforelse
    </div>
@endsection
