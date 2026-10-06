@extends('admin.layout')

@section('title', $category->exists ? 'Edit category' : 'New category')

@section('content')
    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#b8860b]">Catalog</p>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight text-[#1a1a1a]">{{ $category->exists ? 'Edit category' : 'New category' }}</h1>

    <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]" @if ($category->exists) data-confirm-title="Save this category?" data-confirm-body="The store will use these changes as soon as you save." data-confirm-accept="Save" @endif>
        @csrf
        @if ($category->exists)
            @method('PUT')
        @endif

        <div class="rounded-2xl border border-[#ebe6df] bg-white p-6 shadow-sm">
            <div class="grid gap-4">
                <div>
                    <label for="name" class="text-sm font-medium text-[#1a1a1a]">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $category->name) }}" required class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                    @error('name')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="slug" class="text-sm font-medium text-[#1a1a1a]">Slug</label>
                        <input id="slug" name="slug" type="text" value="{{ old('slug', $category->slug) }}" readonly placeholder="test-slug" data-slug-from="name" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-[#fbfaf8] px-4 py-3 text-sm text-[#6f6a64] outline-none">
                        <p class="mt-1 text-xs text-[#8a8680]">Filled from the name. Test Slug becomes test-slug.</p>
                        @error('slug')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="sort_order" class="text-sm font-medium text-[#1a1a1a]">Sort order</label>
                        <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $category->sort_order) }}" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="tagline" class="text-sm font-medium text-[#1a1a1a]">Tagline</label>
                    <input id="tagline" name="tagline" type="text" value="{{ old('tagline', $category->tagline) }}" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                    @error('tagline')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="parent_id" class="text-sm font-medium text-[#1a1a1a]">Parent</label>
                    <select id="parent_id" name="parent_id" class="mt-1.5 w-full rounded-xl border border-[#e4e0da] bg-white px-4 py-3 text-sm outline-none focus:border-[#f5b400]">
                        <option value="">None</option>
                        @foreach ($parents as $parent)
                            <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category->parent_id) === (string) $parent->id)>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="image" class="text-sm font-medium text-[#1a1a1a]">Image</label>
                    @if ($category->image)
                        <img src="{{ asset('assets/categories/'.$category->image) }}" alt="" class="mt-2 h-24 w-40 rounded-xl object-cover">
                    @endif
                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="mt-2 block w-full text-sm text-[#6f6a64] file:mr-4 file:rounded-full file:border-0 file:bg-[#fff4d6] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-[#1a1a1a]">
                    <p class="mt-1 text-xs text-[#8a8680]">{{ $category->image ? 'Upload a new image to replace the current one.' : 'JPG, PNG, WEBP, or GIF. Up to 4 MB.' }}</p>
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
                        <input type="checkbox" name="show_in_nav" value="1" class="mt-0.5 h-4 w-4 accent-[#f5b400]" @checked(old('show_in_nav', $category->show_in_nav))>
                        <span>Show in the menu</span>
                    </label>
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="show_on_home" value="1" class="mt-0.5 h-4 w-4 accent-[#f5b400]" @checked(old('show_on_home', $category->show_on_home))>
                        <span>Show on the home page</span>
                    </label>
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="is_active" value="1" class="mt-0.5 h-4 w-4 accent-[#f5b400]" @checked(old('is_active', $category->is_active))>
                        <span>Visible on the store</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-full bg-[#f5b400] px-5 text-sm font-semibold text-[#1a1a1a]">Save</button>
            <a href="{{ route('admin.categories.index') }}" class="text-center text-sm font-medium text-[#6f6a64] underline underline-offset-4">Cancel</a>
        </div>
    </form>
@endsection
