@extends('admin.layout')

@section('title', $service->exists ? 'Edit service' : 'New service')

@section('content')
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">{{ $service->exists ? 'Edit service' : 'New service' }}</h1>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">This service was not saved.</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]" @if ($service->exists) data-confirm-title="Save this service?" data-confirm-body="The store will use these changes as soon as you save." data-confirm-accept="Save" @endif>
        @csrf
        @if ($service->exists)
            @method('PUT')
        @endif

        <div class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
            <div class="grid gap-4">
                <div>
                    <label for="name" class="text-sm font-medium text-[#1a1a1a]">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $service->name) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                    @error('name')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="slug" class="text-sm font-medium text-[#1a1a1a]">Slug</label>
                        <input id="slug" name="slug" type="text" value="{{ old('slug', $service->slug) }}" readonly placeholder="test-service" data-slug-from="name" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-[#fbfaf8] px-4 py-3 text-sm text-[#6f6a64] outline-none">
                        <p class="mt-1 text-xs text-[#8a8680]">Filled from the name. Test Service becomes test-service.</p>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="category_id" class="text-sm font-medium text-[#1a1a1a]">Category</label>
                        <select id="category_id" name="category_id" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                            <option value="">Choose a category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $service->category_id) === (string) $category->id)>
                                    {{ $category->parent ? $category->parent->name.' / '.$category->name : $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="short_description" class="text-sm font-medium text-[#1a1a1a]">Short description</label>
                    <textarea id="short_description" name="short_description" rows="2" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">{{ old('short_description', $service->short_description) }}</textarea>
                    @error('short_description')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div data-point-list>
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-[#1a1a1a]">Reasons</p>
                        <button type="button" data-point-add class="text-sm font-semibold text-[#b8860b]">Add point</button>
                    </div>
                    <p class="mt-1 text-xs text-[#8a8680]">Each point is a numbered line: a bold title, then the sentence.</p>
                    <div data-point-rows class="mt-3 flex flex-col gap-3">
                        @foreach (old('points', $service->reasonFields() ?: [['title' => '', 'body' => '']]) as $index => $point)
                            <div data-point-row class="rounded-xl border border-[#e4e0da] p-3">
                                <div class="flex items-center gap-2">
                                    <input data-point-title name="points[{{ $index }}][title]" type="text" value="{{ $point['title'] ?? '' }}" required placeholder="Tailored to Your Needs" class="w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                                    <button type="button" data-point-remove class="inline-flex h-11 shrink-0 items-center rounded-full px-3 text-sm font-medium text-[#9b2c2c]">Remove</button>
                                </div>
                                @error('points.'.$index.'.title')
                                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                                @enderror
                                <textarea data-point-body name="points[{{ $index }}][body]" rows="2" required placeholder="Every design we create is customized to your business goals and user needs." class="mt-2 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">{{ $point['body'] ?? '' }}</textarea>
                                @error('points.'.$index.'.body')
                                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                    @error('points')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="price" class="text-sm font-medium text-[#1a1a1a]">Price</label>
                        <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price', $service->price) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        @error('price')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="compare_price" class="text-sm font-medium text-[#1a1a1a]">Compare price</label>
                        <input id="compare_price" name="compare_price" type="number" min="0" step="0.01" value="{{ old('compare_price', $service->compare_price) }}" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        @error('compare_price')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div data-duration-prices>
                    <p class="text-sm font-medium text-[#1a1a1a]">Plan prices</p>
                    <p class="mt-1 text-xs text-[#8a8680]">Price is the 1 month amount. The totals below are what the customer pays for 3, 6, and 12 months. A lower total shows the regular amount crossed out.</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        @foreach ([3 => '3 months', 6 => '6 months', 12 => '12 months'] as $months => $label)
                            <div>
                                <label for="duration-price-{{ $months }}" class="text-xs font-medium text-[#6f6a64]">{{ $label }}</label>
                                <input id="duration-price-{{ $months }}" name="duration_prices[{{ $months }}]" type="number" min="0" step="0.01" value="{{ old('duration_prices.'.$months, $service->storedDurationPrice($months)) }}" placeholder="Total" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                                @error('duration_prices.'.$months)
                                    <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="billing_type" class="text-sm font-medium text-[#1a1a1a]">Billing</label>
                        <select id="billing_type" name="billing_type" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                            @foreach (['one-time' => 'One-time', 'monthly' => 'Monthly', 'hourly' => 'Hourly'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('billing_type', $service->billing_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('billing_type')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="delivery_label" class="text-sm font-medium text-[#1a1a1a]">Delivery</label>
                        <input id="delivery_label" name="delivery_label" type="text" value="{{ old('delivery_label', $service->delivery_label) }}" placeholder="7 Days Delivery" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        @error('delivery_label')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="sort_order" class="text-sm font-medium text-[#1a1a1a]">Sort order</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $service->sort_order) }}" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="video_url" class="text-sm font-medium text-[#1a1a1a]">YouTube link</label>
                        <input id="video_url" name="video_url" type="url" value="{{ old('video_url', $service->video_url) }}" placeholder="https://www.youtube.com/watch?v=..." class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        <p class="mt-1 text-xs text-[#8a8680]">Paste a YouTube link. The service page plays that video.</p>
                        @error('video_url')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="image" class="text-sm font-medium text-[#1a1a1a]">Image</label>
                    <div class="mt-2 overflow-hidden rounded-xl bg-[#f6f3ee]">
                        <img
                            data-image-preview
                            @if ($service->image) src="{{ asset('assets/services/'.$service->image) }}" @endif
                            alt=""
                            @class(['aspect-[16/10] w-full object-contain', 'hidden' => ! $service->image])
                        >
                    </div>
                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" data-image-input class="mt-2 block w-full text-sm text-[#6f6a64] file:mr-4 file:rounded-full file:border-0 file:bg-[#fff4d6] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#1a1a1a]">
                    <p class="mt-1 text-xs text-[#8a8680]">{{ $service->image ? 'Upload a new image to replace the current one.' : 'JPG, PNG, WEBP, or GIF. Up to 4 MB.' }}</p>
                    @error('image')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-[#1a1a1a]">Where it shows</p>
                <div class="mt-4 flex flex-col gap-3 text-sm text-[#1a1a1a]">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="is_listed" value="1" class="mt-0.5 h-4 w-4 accent-[#f5b400]" @checked(old('is_listed', $service->is_listed))>
                        <span>Show in the catalog</span>
                    </label>
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="is_active" value="1" class="mt-0.5 h-4 w-4 accent-[#f5b400]" @checked(old('is_active', $service->is_active))>
                        <span>Visible on the store</span>
                    </label>
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="is_best_seller" value="1" class="mt-0.5 h-4 w-4 accent-[#f5b400]" @checked(old('is_best_seller', $service->is_best_seller))>
                        <span>Bestseller</span>
                    </label>
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="is_deal" value="1" class="mt-0.5 h-4 w-4 accent-[#f5b400]" @checked(old('is_deal', $service->is_deal))>
                        <span>
                            Deal
                            <span class="mt-0.5 block text-xs font-normal text-[#8a8680]">Shows in Today's Deals. Put the regular amount in Compare price.</span>
                        </span>
                    </label>
                    <label class="block pl-7">
                        <span class="text-sm font-medium text-[#1a1a1a]">Deal ends</span>
                        <input type="datetime-local" name="deal_ends_at" value="{{ old('deal_ends_at', $service->deal_ends_at?->format('Y-m-d\TH:i')) }}" class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] px-3 text-sm">
                        @error('deal_ends_at')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
                    </label>
                </div>
            </div>

            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a]">Save</button>
            <a href="{{ route('admin.services.index') }}" class="text-center text-sm font-medium text-[#6f6a64] underline underline-offset-4">Cancel</a>
        </div>
    </form>
@endsection
