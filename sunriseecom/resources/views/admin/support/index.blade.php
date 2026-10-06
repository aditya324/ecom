@extends('admin.layout')

@section('title', 'Support')

@section('content')
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Requests</p>
        <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Support</h1>
        <p class="mt-2 text-sm text-[#6f6a64]">{{ $waiting }} new {{ $waiting === 1 ? 'message' : 'messages' }}</p>
    </div>

    @include('admin.partials.filters', [
        'action' => route('admin.support.index'),
        'values' => $filters,
        'filtering' => $filtering,
        'count' => $messages->count(),
        'noun' => 'message',
        'fields' => [
            ['name' => 'q', 'type' => 'search', 'label' => 'Search', 'placeholder' => 'Name, email, or message'],
            ['name' => 'status', 'type' => 'select', 'label' => 'Status', 'options' => ['' => 'All', 'new' => 'New', 'read' => 'Read', 'replied' => 'Replied']],
        ],
    ])

    <div class="mt-6 flex flex-col gap-3">
        @forelse ($messages as $message)
            <article class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-4">
                <p class="text-sm font-semibold text-[#1a1a1a]"><a href="{{ route('admin.support.show', $message) }}" class="underline">{{ $message->name }}</a> · {{ $message->email }}</p>
                <p class="mt-1 text-xs text-[#8a8680]">{{ $message->created_at->format('j M Y, g:i A') }} · {{ ucfirst($message->status) }}</p>
                <p class="mt-3 text-sm leading-6 text-[#3a3632]">{{ $message->body }}</p>
            </article>
        @empty
            <p class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-8 text-sm text-[#6f6a64]">No support messages yet.</p>
        @endforelse
    </div>
@endsection
