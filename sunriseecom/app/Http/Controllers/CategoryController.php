<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CategoryController extends Controller
{
    private const PER_PAGE = 9;

    public function index(Request $request): View
    {
        return $this->catalog($request, null);
    }

    public function show(Request $request, Category $category): View
    {
        abort_unless($category->is_active, 404);

        return $this->catalog($request, $category);
    }

    private function catalog(Request $request, ?Category $category): View
    {
        $filters = $this->filters($request);
        $category?->load('parent');

        $catalogCategories = $this->catalogCategories();

        if ($category === null) {
            [$categoryFilters, $moreCategoryFilters] = $this->splitAllCategoryFilters($catalogCategories);
            $scopeIds = $catalogCategories->pluck('id')->all();
        } else {
            [$categoryFilters, $moreCategoryFilters] = $this->splitCategoryFilters($catalogCategories, $category);
            $scopeIds = array_values(array_unique(array_merge(
                [$category->id],
                $catalogCategories->where('parent_id', $category->id)->pluck('id')->all(),
            )));
        }

        $selectedIds = [];

        foreach ($catalogCategories->whereIn('slug', $filters['categories']) as $selected) {
            $selectedIds[] = $selected->id;

            foreach ($catalogCategories->where('parent_id', $selected->id) as $child) {
                $selectedIds[] = $child->id;
            }
        }

        $categoryIds = $selectedIds !== [] ? array_values(array_unique($selectedIds)) : $scopeIds;

        $base = Service::query()
            ->whereIn('category_id', $categoryIds)
            ->where('is_listed', true)
            ->where('is_active', true);

        $priceMax = max((int) ceil((float) Service::query()
            ->whereIn('category_id', $categoryIds)
            ->where('is_listed', true)
            ->where('is_active', true)
            ->max('price')), 1000);

        $services = $this->applyFilters(clone $base, $filters)
            ->with('category')
            ->when($filters['sort'] === 'price_asc', fn (Builder $query) => $query->orderBy('price'))
            ->when($filters['sort'] === 'price_desc', fn (Builder $query) => $query->orderByDesc('price'))
            ->when($filters['sort'] === 'rating', fn (Builder $query) => $query->orderByDesc('rating'))
            ->when($filters['sort'] === 'recommended', fn (Builder $query) => $query->orderBy('sort_order')->orderBy('name'))
            ->orderBy('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return view('categories.show', [
            'category' => $category,
            'catalogUrl' => $category === null ? route('categories.index') : route('categories.show', $category),
            'services' => $services,
            'filterCategories' => $catalogCategories,
            'categoryFilters' => $categoryFilters,
            'moreCategoryFilters' => $moreCategoryFilters,
            'filters' => $filters,
            'priceMax' => $priceMax,
        ]);
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return array{0: Collection<int, Category>, 1: Collection<int, Category>}
     */
    private function splitAllCategoryFilters(Collection $categories): array
    {
        $ordered = collect();

        foreach ($categories->whereNull('parent_id') as $parent) {
            $ordered->push($parent);

            foreach ($categories->where('parent_id', $parent->id) as $child) {
                $ordered->push($child);
            }
        }

        $listed = $ordered->pluck('id');

        foreach ($categories as $row) {
            if ($listed->contains($row->id)) {
                continue;
            }

            $ordered->push($row);
        }

        $ordered = $ordered->values();

        return [$ordered->take(5), $ordered->slice(5)->values()];
    }

    /**
     * @return Collection<int, Category>
     */
    private function catalogCategories(): Collection
    {
        $categories = Category::query()
            ->active()
            ->withCount(['services as services_count' => fn (Builder $query) => $query->where('is_listed', true)->where('is_active', true)])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $direct = $categories->mapWithKeys(fn (Category $row) => [$row->id => (int) $row->services_count]);

        foreach ($categories as $row) {
            $row->setAttribute(
                'services_count',
                $direct[$row->id] + (int) $categories
                    ->where('parent_id', $row->id)
                    ->sum(fn (Category $child) => $direct[$child->id]),
            );
        }

        return $categories;
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return array{0: Collection<int, Category>, 1: Collection<int, Category>}
     */
    private function splitCategoryFilters(Collection $categories, Category $category): array
    {
        $children = $categories->where('parent_id', $category->id)->values();
        $others = collect();

        foreach ($categories->whereNull('parent_id') as $parent) {
            if ($parent->id === $category->id) {
                continue;
            }

            $others->push($parent);

            foreach ($categories->where('parent_id', $parent->id) as $child) {
                $others->push($child);
            }
        }

        $listed = $children->pluck('id')->merge($others->pluck('id'));

        foreach ($categories as $row) {
            if ($listed->contains($row->id)) {
                continue;
            }

            $others->push($row);
            $listed->push($row->id);
        }

        if ($children->isNotEmpty()) {
            return [$children, $others->values()];
        }

        $ordered = $others->values();

        return [$ordered->take(5), $ordered->slice(5)->values()];
    }

    /**
     * @return array{categories: list<string>, types: list<string>, rating: ?string, min: ?int, max: ?int, sort: string}
     */
    private function filters(Request $request): array
    {
        $categories = array_values(array_filter(
            (array) $request->input('categories', []),
            fn (mixed $slug) => is_string($slug) && $slug !== '',
        ));

        $types = array_values(array_intersect(
            (array) $request->input('type', []),
            ['one-time', 'monthly', 'hourly'],
        ));

        $rating = in_array($request->input('rating'), ['4', '4.5'], true)
            ? (string) $request->input('rating')
            : null;

        $sort = in_array($request->input('sort'), ['price_asc', 'price_desc', 'rating'], true)
            ? (string) $request->input('sort')
            : 'recommended';

        $min = $request->filled('min') ? max(0, $request->integer('min')) : null;
        $max = $request->filled('max') ? max(0, $request->integer('max')) : null;

        if ($min !== null && $max !== null && $min > $max) {
            [$min, $max] = [$max, $min];
        }

        return [
            'categories' => $categories,
            'types' => $types,
            'rating' => $rating,
            'min' => $min,
            'max' => $max,
            'sort' => $sort,
        ];
    }

    /**
     * @param  Builder<Service>  $query
     * @param  array{categories: list<string>, types: list<string>, rating: ?string, min: ?int, max: ?int, sort: string}  $filters
     * @return Builder<Service>
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['types'] !== [], fn (Builder $query) => $query->whereIn('billing_type', $filters['types']))
            ->when($filters['rating'] !== null, fn (Builder $query) => $query->where('rating', '>=', $filters['rating']))
            ->when($filters['min'] !== null, fn (Builder $query) => $query->where('price', '>=', $filters['min']))
            ->when($filters['max'] !== null, fn (Builder $query) => $query->where('price', '<=', $filters['max']));
    }
}
