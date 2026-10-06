@extends('admin.layout')

@section('title', 'Admin')

@section('content')
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Admin</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">Overview</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">Signed in as {{ auth('admin')->user()->name }}</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.categories.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Categories</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $waiting['categories'] }}</p>
        </a>
        <a href="{{ route('admin.services.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Services</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $waiting['services'] }}</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Hidden services: {{ $waiting['hidden'] }}</p>
        </a>
        <a href="{{ route('admin.packages.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Packages</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $waiting['packages'] }}</p>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'placed']) }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Orders waiting</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $waiting['orders'] }}</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Placed, not started yet. {{ \App\Models\Order::formatMoney($collected) }} collected.</p>
        </a>
        <a href="{{ route('admin.customers.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Customers</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $waiting['customers'] }}</p>
        </a>
        <a href="{{ route('admin.carts.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Open carts</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $waiting['carts'] }}</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Customers with something in the cart.</p>
        </a>
        <a href="{{ route('admin.wishlists.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Wishlists</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $waiting['wishlists'] }}</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Customers who saved a service.</p>
        </a>
        <a href="{{ route('admin.coupons.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Sales</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Coupons</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $waiting['coupons'] }}</p>
        </a>
        <a href="{{ route('admin.quotes.index', ['status' => 'new']) }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Requests</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">New quotes</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $waiting['quotes'] }}</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Waiting for a reply.</p>
        </a>
        <a href="{{ route('admin.reviews.index') }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Requests</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">Reviews</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $waiting['reviews'] }}</p>
        </a>
        <a href="{{ route('admin.support.index', ['status' => 'new']) }}" class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm hover:border-[#f5b400]">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Requests</p>
            <p class="mt-2 text-lg font-semibold text-[#1a1a1a]">New messages</p>
            <p class="mt-2 text-2xl font-semibold tracking-tight text-[#1a1a1a]">{{ $waiting['support'] }}</p>
            <p class="mt-1 text-sm text-[#6f6a64]">Support waiting for a reply.</p>
        </a>
    </div>
@endsection
