@extends('admin.layout')

@section('title', $coupon->exists ? 'Edit coupon' : 'New coupon')

@section('content')
    @php
        $chosen = collect(old('services', $selected))->map(fn ($id) => (int) $id)->all();
        $included = collect($chosen)->map(fn (int $id) => $services->firstWhere('id', $id))->filter();
    @endphp

    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">{{ $coupon->exists ? 'Edit coupon' : 'New coupon' }}</h1>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">This coupon was not saved.</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]" @if ($coupon->exists) data-confirm-title="Save this coupon?" data-confirm-body="The store will use these changes as soon as you save." data-confirm-accept="Save" @endif>
        @csrf
        @if ($coupon->exists)
            @method('PUT')
        @endif

        <div class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
            <div class="grid gap-4">
                <div>
                    <label for="code" class="text-sm font-medium text-[#1a1a1a]">Code</label>
                    <input id="code" name="code" type="text" value="{{ old('code', $coupon->code) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm uppercase outline-none focus:border-[#f5b400]">
                    <p class="mt-1 text-xs text-[#8a8680]">Customers type this on the cart. Launch 10 becomes LAUNCH10.</p>
                    @error('code')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="type" class="text-sm font-medium text-[#1a1a1a]">Discount</label>
                        <select id="type" name="type" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                            <option value="percent" @selected(old('type', $coupon->type) === 'percent')>Percent off</option>
                            <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed amount off</option>
                        </select>
                    </div>
                    <div>
                        <label for="amount" class="text-sm font-medium text-[#1a1a1a]">Amount</label>
                        <input id="amount" name="amount" type="number" min="1" step="0.01" value="{{ old('amount', $coupon->amount) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        <p class="mt-1 text-xs text-[#8a8680]">10 means 10% or ₹10. Percent comes off each chosen service. A fixed amount comes off those services once.</p>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="usage_limit" class="text-sm font-medium text-[#1a1a1a]">Total uses</label>
                        <input id="usage_limit" name="usage_limit" type="number" min="1" max="999999" value="{{ old('usage_limit', $coupon->usage_limit ?? 1) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        <p class="mt-1 text-xs text-[#8a8680]">How many orders can use this code. After that it stops for everyone.</p>
                        @error('usage_limit')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="per_user_limit" class="text-sm font-medium text-[#1a1a1a]">Times each customer can use it</label>
                        <input id="per_user_limit" name="per_user_limit" type="number" min="1" max="999" value="{{ old('per_user_limit', $coupon->per_user_limit ?? 1) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        <p class="mt-1 text-xs text-[#8a8680]">1 means once. After that, the same customer cannot use this code again.</p>
                        @error('per_user_limit')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div data-package-services>
                    <p class="text-sm font-medium text-[#1a1a1a]">Services</p>
                    <p class="mt-1 text-xs text-[#8a8680]">The coupon works only on these services.</p>
                    <div data-package-rows class="mt-3 flex flex-col gap-2">
                        @foreach ($included as $service)
                            <div data-package-row data-service-name="{{ $service->name }}" class="flex items-center justify-between gap-3 rounded-xl border border-[#e4e0da] px-3 py-2">
                                <input type="hidden" name="services[]" value="{{ $service->id }}">
                                <span>
                                    <span class="block text-sm text-[#1a1a1a]">{{ $service->name }}</span>
                                    <span class="block text-xs text-[#8a8680]">{{ $service->category?->name }}</span>
                                </span>
                                <button type="button" data-package-remove class="inline-flex h-9 shrink-0 items-center rounded-full px-3 text-sm font-medium text-[#9b2c2c]">Delete</button>
                            </div>
                        @endforeach
                    </div>
                    <p data-package-empty @class(['mt-3 text-sm text-[#8a8680]', 'hidden' => $included->isNotEmpty()])>No services chosen yet.</p>
                    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                        <select data-package-picker class="w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                            <option value="">Choose a service</option>
                            @foreach ($services->whereNotIn('id', $chosen) as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}{{ $service->category ? ' — '.$service->category->name : '' }}</option>
                            @endforeach
                        </select>
                        <button type="button" data-package-add class="inline-flex h-11 shrink-0 items-center justify-center rounded-full bg-[#fff4d6] px-5 text-sm font-semibold text-[#1a1a1a]">Add</button>
                    </div>
                    @error('services')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
                <label class="flex items-start gap-3 text-sm text-[#1a1a1a]">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 h-4 w-4 accent-[#f5b400]" @checked(old('is_active', $coupon->is_active))>
                    <span>Customers can use this code</span>
                </label>
            </div>
            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a]">Save</button>
            <a href="{{ route('admin.coupons.index') }}" class="text-center text-sm font-medium text-[#6f6a64] underline underline-offset-4">Cancel</a>
        </div>
    </form>
@endsection
