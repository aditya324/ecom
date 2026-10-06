@extends('admin.layout')

@section('title', $package->exists ? 'Edit package' : 'New package')

@section('content')
    @php
        $chosen = collect(old('services', $selected))->map(fn ($id) => (int) $id)->all();
        $included = collect($chosen)->map(fn (int $id) => $services->firstWhere('id', $id))->filter();
    @endphp

    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">{{ $package->exists ? 'Edit package' : 'New package' }}</h1>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">This package was not saved.</p>
            <ul class="mt-2 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]" @if ($package->exists) data-confirm-title="Save this package?" data-confirm-body="The store will use these changes as soon as you save." data-confirm-accept="Save" @endif>
        @csrf
        @if ($package->exists)
            @method('PUT')
        @endif

        <div class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
            <div class="grid gap-4">
                <div>
                    <label for="name" class="text-sm font-medium text-[#1a1a1a]">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $package->name) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                    @error('name')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="text-sm font-medium text-[#1a1a1a]">Slug</label>
                    <input id="slug" name="slug" type="text" value="{{ old('slug', $package->slug) }}" readonly placeholder="growth-package" data-slug-from="name" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-[#fbfaf8] px-4 py-3 text-sm text-[#6f6a64] outline-none">
                    <p class="mt-1 text-xs text-[#8a8680]">Filled from the name. Growth Package becomes growth-package.</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="monthly_price" class="text-sm font-medium text-[#1a1a1a]">Monthly price</label>
                        <input id="monthly_price" name="monthly_price" type="number" min="0" step="0.01" value="{{ old('monthly_price', $package->monthly_price) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        <p class="mt-1 text-xs text-[#8a8680]">Shown when Yearly is switched off.</p>
                        @error('monthly_price')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="yearly_price" class="text-sm font-medium text-[#1a1a1a]">Yearly price</label>
                        <input id="yearly_price" name="yearly_price" type="number" min="0" step="0.01" value="{{ old('yearly_price', $package->yearly_price) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        <p class="mt-1 text-xs text-[#8a8680]">The full year amount. The store shows it divided by 12 when Yearly is on.</p>
                        @error('yearly_price')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="sort_order" class="text-sm font-medium text-[#1a1a1a]">Sort order</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $package->sort_order) }}" class="mt-1.5 w-full max-w-xs rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                    @error('sort_order')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div data-package-services>
                    <p class="text-sm font-medium text-[#1a1a1a]">Included services</p>
                    <p class="mt-1 text-xs text-[#8a8680]">Add a service or delete one, then save. These are the lines on the package card.</p>
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
                    <p data-package-empty @class(['mt-3 text-sm text-[#8a8680]', 'hidden' => $included->isNotEmpty()])>No services in this package yet.</p>
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
                    @error('services.*')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
                <p class="text-sm font-semibold text-[#1a1a1a]">Where it shows</p>
                <label class="mt-4 flex items-start gap-3 text-sm text-[#1a1a1a]">
                    <input type="checkbox" name="is_active" value="1" class="mt-0.5 h-4 w-4 accent-[#f5b400]" @checked(old('is_active', $package->is_active))>
                    <span>Visible on the store</span>
                </label>
            </div>

            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a]">Save</button>
            <a href="{{ route('admin.packages.index') }}" class="text-center text-sm font-medium text-[#6f6a64] underline underline-offset-4">Cancel</a>
        </div>
    </form>
@endsection
