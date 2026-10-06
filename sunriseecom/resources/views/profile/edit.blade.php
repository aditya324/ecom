@extends('profile.layout')

@section('title', 'Profile')

@section('content')
    <h1 class="text-3xl font-semibold tracking-tight text-[#1a1a1a]">Profile</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">Update the name and email on your account.</p>

    <form method="POST" action="{{ route('profile.update') }}" class="mt-6 rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <div class="grid gap-4">
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Name</span>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="mt-1.5 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm outline-none focus:border-[#f5b400]">
                @error('name')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Email</span>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1.5 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm outline-none focus:border-[#f5b400]">
                @error('email')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">State</span>
                <input type="text" name="billing_state" value="{{ old('billing_state', $user->billing_state) }}" class="mt-1.5 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm outline-none focus:border-[#f5b400]">
                <span class="mt-1 block text-xs text-[#8a8680]">Saved for the next checkout. A street address is only needed with a GSTIN.</span>
                @error('billing_state')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">GSTIN</span>
                <input type="text" name="billing_gstin" value="{{ old('billing_gstin', $user->billing_gstin) }}" maxlength="15" class="mt-1.5 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm uppercase outline-none focus:border-[#f5b400]">
                @error('billing_gstin')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Billing address</span>
                <input type="text" name="billing_address" value="{{ old('billing_address', $user->billing_address) }}" class="mt-1.5 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm outline-none focus:border-[#f5b400]">
                @error('billing_address')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-[#1a1a1a]">City</span>
                    <input type="text" name="billing_city" value="{{ old('billing_city', $user->billing_city) }}" class="mt-1.5 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm outline-none focus:border-[#f5b400]">
                    @error('billing_city')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-[#1a1a1a]">PIN code</span>
                    <input type="text" name="billing_pin" value="{{ old('billing_pin', $user->billing_pin) }}" inputmode="numeric" maxlength="6" class="mt-1.5 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm outline-none focus:border-[#f5b400]">
                    @error('billing_pin')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            </div>
        </div>
        <button type="submit" class="mt-5 inline-flex h-11 items-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a]">Save</button>
    </form>

    <form method="POST" action="{{ route('profile.password') }}" class="mt-6 rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        <h2 class="text-lg font-semibold text-[#1a1a1a]">{{ $user->password ? 'Change password' : 'Set a password' }}</h2>
        <div class="mt-4 grid gap-4">
            @if ($user->password)
                <label class="block">
                    <span class="text-sm font-medium text-[#1a1a1a]">Current password</span>
                    <input type="password" name="current_password" required autocomplete="current-password" class="mt-1.5 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm outline-none focus:border-[#f5b400]">
                    @error('current_password')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            @endif
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">New password</span>
                <input type="password" name="password" required autocomplete="new-password" class="mt-1.5 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm outline-none focus:border-[#f5b400]">
                @error('password')<span class="mt-1 block text-sm text-red-700">{{ $message }}</span>@enderror
            </label>
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Confirm new password</span>
                <input type="password" name="password_confirmation" required autocomplete="new-password" class="mt-1.5 h-11 w-full rounded-xl border border-[#ece7e0] bg-white px-3 text-sm outline-none focus:border-[#f5b400]">
            </label>
        </div>
        <button type="submit" class="mt-5 inline-flex h-11 items-center rounded-full bg-[#1a1a1a] px-5 text-sm font-semibold text-white">Save password</button>
    </form>
@endsection
