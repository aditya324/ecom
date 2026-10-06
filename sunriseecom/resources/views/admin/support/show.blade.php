@extends('admin.layout')

@section('title', 'Reply to support')

@section('content')
    <a href="{{ route('admin.support.index') }}" class="text-sm text-[#6f6a64]">Support</a>
    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Reply to {{ $message->name }}</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">{{ $message->email }} · {{ ucfirst($message->status) }}</p>

    <div class="mt-6 rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-[#1a1a1a]">Their message</p>
        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#1a1a1a]">{{ $message->body }}</p>
    </div>

    @if ($message->status === 'replied' && $message->reply)
        <div class="mt-6 rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-[#1a1a1a]">Reply already sent</p>
            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-[#3a3632]">{{ $message->reply }}</p>
        </div>
    @endif

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

    <form method="POST" action="{{ route('admin.support.update', $message) }}" class="mt-6 grid max-w-2xl gap-4">
        @csrf
        @method('PUT')
        <p class="text-sm text-[#6f6a64]">The reply is emailed to {{ $message->email }}.</p>
        <div>
            <label for="reply" class="text-sm font-medium text-[#1a1a1a]">Reply</label>
            <textarea id="reply" name="reply" rows="5" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">{{ old('reply', $message->reply) }}</textarea>
        </div>
        <div class="flex items-center gap-4">
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a]">Send reply</button>
            <a href="{{ route('admin.support.index') }}" class="text-sm font-medium text-[#6f6a64] underline underline-offset-4">Back</a>
        </div>
    </form>
@endsection
