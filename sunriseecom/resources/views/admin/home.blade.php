@extends('admin.layout')

@section('title', 'Admin')

@section('content')
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Admin</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Overview</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">Signed in as {{ auth('admin')->user()->name }}</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('admin.categories.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Categories</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Add, edit, and hide the groups shown in the store.</p>
        </a>
        <a href="{{ route('admin.services.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Services</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Add, edit, and hide the offers inside each category.</p>
        </a>
        <a href="{{ route('admin.packages.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Packages</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Add, edit, and hide the packages on the homepage.</p>
        </a>
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.orders.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Orders</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ \App\Models\Order::formatMoney($collected) }}</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Total collected, after refunds.</p>
        </a>
        <a href="{{ route('admin.quotes.index', ['status' => 'new']) }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Requests</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">New quotes</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $newQuotes }}</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Waiting for a reply.</p>
        </a>
        <a href="{{ route('admin.carts.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Open carts</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $openCarts }}</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Customers with something in the cart.</p>
        </a>
        <a href="{{ route('admin.services.index', ['visibility' => 'hidden']) }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Hidden services</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $hiddenServices }}</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Taken off the store.</p>
        </a>
    </div>

    <a href="{{ route('admin.carts.index') }}" class="mt-4 block rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
        <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Carts</p>
        <p class="mt-1 text-sm text-[#6f6a64]">See which customer added which service.</p>
    </a>

    <a href="{{ route('admin.wishlists.index') }}" class="mt-4 block rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
        <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Wishlists</p>
        <p class="mt-1 text-sm text-[#6f6a64]">See which customer saved which service.</p>
    </a>

    <a href="{{ route('admin.coupons.index') }}" class="mt-4 block rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
        <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Coupons</p>
        <p class="mt-1 text-sm text-[#6f6a64]">Add a code that discounts chosen services.</p>
    </a>

    <a href="{{ route('admin.quotes.index') }}" class="mt-4 block rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Requests</p>
        <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Quotes</p>
        <p class="mt-1 text-sm text-[#6f6a64]">Read custom quote requests and reply with a price.</p>
    </a>
@endsection
