<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReturnsToFilteredIndex;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\AdminFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use ReturnsToFilteredIndex;

    public function index(Request $request): View
    {
        $filters = [
            'q' => AdminFilter::text($request, 'q'),
            'visibility' => AdminFilter::choice($request, 'visibility', ['visible', 'hidden']),
            'place' => AdminFilter::choice($request, 'place', ['menu', 'home', 'unplaced']),
        ];

        $categories = $this->inTreeOrder(
            Category::query()->with('parent')->withCount('services')->orderBy('sort_order')->orderBy('name')->get(),
        );

        return view('admin.categories.index', [
            'categories' => $categories->filter(function (Category $category) use ($filters) {
                if ($filters['q'] !== '' && ! str_contains(Str::lower($category->name.' '.$category->slug), Str::lower($filters['q']))) {
                    return false;
                }

                if ($filters['visibility'] === 'visible' && ! $category->is_active) {
                    return false;
                }

                if ($filters['visibility'] === 'hidden' && $category->is_active) {
                    return false;
                }

                if ($filters['place'] === 'menu' && ! $category->show_in_nav) {
                    return false;
                }

                if ($filters['place'] === 'home' && ! $category->show_on_home) {
                    return false;
                }

                if ($filters['place'] === 'unplaced' && ($category->show_in_nav || $category->show_on_home)) {
                    return false;
                }

                return true;
            })->values(),
            'counts' => [
                'all' => $categories->count(),
                'visible' => $categories->where('is_active', true)->count(),
                'menu' => $categories->where('show_in_nav', true)->where('is_active', true)->count(),
            ],
            'filters' => $filters,
            'filtering' => AdminFilter::active($filters),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category([
                'is_active' => true,
                'show_in_nav' => false,
                'show_on_home' => false,
                'sort_order' => 0,
            ]),
            'parents' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::query()->create($this->attributes($request));

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category saved.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', [
            'category' => $category,
            'parents' => Category::query()->whereKeyNot($category->id)->orderBy('name')->get(),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($this->attributes($request, $category));

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category saved.');
    }

    public function hide(Category $category): RedirectResponse
    {
        $category->update(['is_active' => false]);

        return $this->filteredIndex('admin.categories.index', 'Category hidden from the store.');
    }

    public function restore(Category $category): RedirectResponse
    {
        $category->update(['is_active' => true]);

        return $this->filteredIndex('admin.categories.index', 'Category is visible on the store.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->children()->update(['parent_id' => $category->parent_id]);

        if ($category->image) {
            $this->deleteUploadedImage($category->image);
        }

        $category->delete();

        return $this->filteredIndex('admin.categories.index', 'Category deleted.');
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return Collection<int, Category>
     */
    private function inTreeOrder(Collection $categories): Collection
    {
        $ordered = collect();

        $walk = function (?int $parentId, int $depth) use (&$walk, $categories, $ordered): void {
            foreach ($categories->where('parent_id', $parentId)->values() as $category) {
                $category->setAttribute('depth', $depth);
                $ordered->push($category);
                $walk($category->id, $depth + 1);
            }
        };

        $walk(null, 0);

        foreach ($categories as $category) {
            if ($ordered->contains(fn (Category $row) => $row->id === $category->id)) {
                continue;
            }

            $category->setAttribute('depth', 0);
            $ordered->push($category);
        }

        return $ordered;
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(CategoryRequest $request, ?Category $category = null): array
    {
        $attributes = $request->safe()->only([
            'parent_id',
            'name',
            'slug',
            'tagline',
            'show_in_nav',
            'show_on_home',
            'is_active',
            'sort_order',
        ]);

        if ($request->hasFile('image')) {
            $attributes['image'] = $this->storeImage($request->file('image'), $attributes['slug']);

            if ($category?->image) {
                $this->deleteUploadedImage($category->image);
            }
        }

        return $attributes;
    }

    private function storeImage(UploadedFile $file, string $slug): string
    {
        $name = $slug.'-'.Str::lower(Str::random(8)).'.'.$file->extension();
        $file->move(public_path('assets/categories'), $name);

        return $name;
    }

    private function deleteUploadedImage(string $filename): void
    {
        if (str_starts_with($filename, 'category-')) {
            return;
        }

        $path = public_path('assets/categories/'.$filename);

        if (is_file($path)) {
            unlink($path);
        }
    }
}
