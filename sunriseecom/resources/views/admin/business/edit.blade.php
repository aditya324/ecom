@extends('admin.layout')

@section('title', 'Business')

@section('content')
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Store</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Business</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">These details are printed on every invoice.</p>

    <form method="POST" action="{{ route('admin.business.update') }}" class="mt-8 max-w-xl rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div class="flex flex-col gap-4">
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Legal name</span>
                <input type="text" name="legal_name" value="{{ old('legal_name', $business->legal_name) }}" required class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] px-3 text-sm">
                @error('legal_name')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">GSTIN</span>
                <input type="text" name="gstin" value="{{ old('gstin', $business->gstin) }}" maxlength="15" class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] px-3 text-sm uppercase">
                @error('gstin')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Address</span>
                <textarea name="address" rows="4" class="mt-1 w-full rounded-xl border border-[#ece7e0] px-3 py-2 text-sm">{{ old('address', $business->address) }}</textarea>
                @error('address')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Email</span>
                <input type="email" name="email" value="{{ old('email', $business->email) }}" class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] px-3 text-sm">
                @error('email')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Instagram</span>
                <input type="url" name="instagram" value="{{ old('instagram', $business->instagram) }}" placeholder="https://instagram.com/..." class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] px-3 text-sm">
                @error('instagram')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
            </label>
        </div>
        <button type="submit" class="mt-6 inline-flex h-11 items-center rounded-full bg-[#111111] px-5 text-sm font-semibold text-white">Save</button>
    </form>
@endsection
