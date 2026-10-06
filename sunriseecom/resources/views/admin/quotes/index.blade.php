@extends('admin.layout')

@section('title', 'Quotes')

@section('content')
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Requests</p>
        <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Quotes</h1>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-2">
        <div class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-4">
            <p class="text-xs font-medium uppercase tracking-wide text-[#8a8680]">All</p>
            <p class="mt-1 text-2xl font-semibold text-[#1a1a1a]">{{ $counts['all'] }}</p>
        </div>
        <div class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-4">
            <p class="text-xs font-medium uppercase tracking-wide text-[#8a8680]">Waiting</p>
            <p class="mt-1 text-2xl font-semibold text-[#1a1a1a]">{{ $counts['waiting'] }}</p>
        </div>
    </div>

    @include('admin.partials.filters', [
        'action' => route('admin.quotes.index'),
        'values' => $filters,
        'filtering' => $filtering,
        'count' => $quotes->count(),
        'noun' => 'quote',
        'fields' => [
            ['name' => 'q', 'type' => 'search', 'label' => 'Search', 'placeholder' => 'Name, email, or service'],
            ['name' => 'status', 'type' => 'select', 'label' => 'Status', 'options' => ['' => 'New and replied', 'new' => 'New', 'replied' => 'Replied']],
        ],
    ])

    <div class="mt-6 overflow-hidden rounded-2xl border border-[#ebe6df] bg-white shadow-sm">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-[#fbfaf8] text-[#8a8680]">
                <tr>
                    <th class="px-5 py-3 font-medium">Request</th>
                    <th class="px-5 py-3 font-medium">Service</th>
                    <th class="px-5 py-3 font-medium">Price</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotes as $quote)
                    <tr class="border-t border-[#f3efe9]">
                        <td class="px-5 py-4">
                            <span class="block font-semibold text-[#1a1a1a]">{{ $quote->name }}</span>
                            <span class="block text-xs text-[#8a8680]">{{ $quote->email }}</span>
                        </td>
                        <td class="px-5 py-4 text-[#6f6a64]">{{ $quote->service?->name }}</td>
                        <td class="px-5 py-4 text-[#1a1a1a]">{{ $quote->money() ?? '—' }}</td>
                        <td class="px-5 py-4">
                            @if ($quote->status === 'replied')
                                <span class="inline-flex items-center gap-1.5 text-sm text-[#1a1a1a]">
                                    <span class="h-2 w-2 rounded-full bg-[#2f9e44]"></span>
                                    Replied
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-sm text-[#1a1a1a]">
                                    <span class="h-2 w-2 rounded-full bg-[#f5b400]"></span>
                                    New
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.quotes.edit', $quote) }}" class="inline-flex h-9 items-center rounded-full border border-[#e4e0da] px-3 text-sm font-medium text-[#1a1a1a] hover:bg-[#fbfaf8]">Reply</a>
                                <form method="POST" action="{{ route('admin.quotes.destroy', $quote) }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" data-confirm-title="Delete this quote?" data-confirm-body="This request will be removed. This cannot be undone." data-confirm-accept="Delete" data-confirm-tone="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-9 items-center rounded-full px-3 text-sm font-medium text-[#9b2c2c] hover:bg-[#fdecec]">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-sm text-[#6f6a64]">{{ $filtering ? 'No quotes match these filters.' : 'No quote requests yet.' }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
