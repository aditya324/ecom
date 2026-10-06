<li class="flex items-center gap-2.5 text-sm">
    <input type="checkbox" name="categories[]" form="catalog-filters" value="{{ $filterCategory->slug }}" class="h-4 w-4 rounded border-[#e4d8c4] accent-[#f5b400]" @checked(in_array($filterCategory->slug, $filters['categories'], true))>
    <a href="{{ route('categories.show', $filterCategory) }}" class="flex-1 hover:text-[#c4a035]">{{ $filterCategory->name }}</a>
    <span class="text-[#8a8680]">{{ $filterCategory->services_count }}</span>
</li>
