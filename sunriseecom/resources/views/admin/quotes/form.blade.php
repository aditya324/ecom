@extends('admin.layout')

@section('title', 'Reply to quote')

@section('content')
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Requests</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Reply to {{ $quote->name }}</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">{{ $quote->service?->name }} · {{ $quote->email }}</p>

    <div class="mt-6 rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-[#1a1a1a]">What they need</p>
        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#1a1a1a]">{{ $quote->message }}</p>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">This reply was not saved.</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.quotes.update', $quote) }}" class="mt-6 grid max-w-2xl gap-4">
        @csrf
        @method('PUT')
        <p class="text-sm text-[#6f6a64]">The price and reply are emailed to {{ $quote->email }}. Both are required.</p>
        <div>
            <label for="quoted_price" class="text-sm font-medium text-[#1a1a1a]">Your price</label>
            <input id="quoted_price" name="quoted_price" type="number" min="0" step="0.01" value="{{ old('quoted_price', $quote->quoted_price) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
        </div>
        <div>
            <label for="reply" class="text-sm font-medium text-[#1a1a1a]">Reply</label>
            <textarea id="reply" name="reply" rows="5" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">{{ old('reply', $quote->reply) }}</textarea>
        </div>
        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a]">Send reply</button>
            <a href="{{ route('admin.quotes.index') }}" class="text-sm font-medium text-[#6f6a64] underline underline-offset-4">Cancel</a>
        </div>
    </form>
@endsection
