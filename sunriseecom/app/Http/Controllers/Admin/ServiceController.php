<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReturnsToFilteredIndex;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Category;
use App\Models\Service;
use App\Support\AdminFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ServiceController extends Controller
{
    use ReturnsToFilteredIndex;

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('admin.services.index', [
            'services' => $this->filtered($filters),
            'categories' => $this->categories(),
            'filters' => $filters,
            'filtering' => AdminFilter::active($filters, ['sort' => 'name']),
            'counts' => [
                'all' => Service::query()->count(),
                'visible' => Service::query()->where('is_active', true)->count(),
                'listed' => Service::query()->where('is_active', true)->where('is_listed', true)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.services.form', [
            'service' => new Service([
                'is_active' => true,
                'is_listed' => true,
                'is_best_seller' => false,
                'is_deal' => false,
                'billing_type' => 'one-time',
                'sort_order' => 0,
            ]),
            'categories' => $this->categories(),
        ]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        Service::query()->create($this->attributes($request));

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Service saved.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.form', [
            'service' => $service,
            'categories' => $this->categories(),
        ]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($this->attributes($request, $service));

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Service saved.');
    }

    public function hide(Service $service): RedirectResponse
    {
        $service->update(['is_active' => false]);

        return $this->filteredIndex('admin.services.index', 'Service hidden from the store.');
    }

    public function restore(Service $service): RedirectResponse
    {
        $service->update(['is_active' => true]);

        return $this->filteredIndex('admin.services.index', 'Service is visible on the store.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if ($service->image) {
            $this->deleteUploadedImage($service->image);
        }

        $service->delete();

        return $this->filteredIndex('admin.services.index', 'Service deleted.');
    }

    /**
     * @return array{q: string, category: ?int, billing: string, visibility: string, listed: string, highlight: string, min: ?float, max: ?float, sort: string}
     */
    private function filters(Request $request): array
    {
        return [
            'q' => AdminFilter::text($request, 'q'),
            'category' => $request->filled('category') ? $request->integer('category') : null,
            'billing' => AdminFilter::choice($request, 'billing', ['one-time', 'monthly', 'hourly']),
            'visibility' => AdminFilter::choice($request, 'visibility', ['visible', 'hidden']),
            'listed' => AdminFilter::choice($request, 'listed', ['listed', 'unlisted']),
            'highlight' => AdminFilter::choice($request, 'highlight', ['best-seller', 'deal']),
            'min' => AdminFilter::number($request, 'min'),
            'max' => AdminFilter::number($request, 'max'),
            'sort' => AdminFilter::choice($request, 'sort', ['name', 'price_asc', 'price_desc', 'newest']) ?: 'name',
        ];
    }

    /**
     * @param  array{q: string, category: ?int, billing: string, visibility: string, listed: string, highlight: string, min: ?float, max: ?float, sort: string}  $filters
     * @return Collection<int, Service>
     */
    private function filtered(array $filters): Collection
    {
        $services = Service::query()->with('category');

        if ($filters['q'] !== '') {
            $term = AdminFilter::like($filters['q']);
            $services->where(function (Builder $query) use ($term): void {
                $query->where('name', 'like', $term)->orWhere('slug', 'like', $term);
            });
        }

        if ($filters['category']) {
            $services->where('category_id', $filters['category']);
        }

        if ($filters['billing'] !== '') {
            $services->where('billing_type', $filters['billing']);
        }

        if ($filters['visibility'] === 'visible') {
            $services->where('is_active', true);
        } elseif ($filters['visibility'] === 'hidden') {
            $services->where('is_active', false);
        }

        if ($filters['listed'] === 'listed') {
            $services->where('is_listed', true);
        } elseif ($filters['listed'] === 'unlisted') {
            $services->where('is_listed', false);
        }

        if ($filters['highlight'] === 'best-seller') {
            $services->where('is_best_seller', true);
        } elseif ($filters['highlight'] === 'deal') {
            $services->where('is_deal', true);
        }

        if ($filters['min'] !== null) {
            $services->where('price', '>=', $filters['min']);
        }

        if ($filters['max'] !== null) {
            $services->where('price', '<=', $filters['max']);
        }

        match ($filters['sort']) {
            'price_asc' => $services->orderBy('price')->orderBy('name'),
            'price_desc' => $services->orderByDesc('price')->orderBy('name'),
            'newest' => $services->latest(),
            default => $services->orderBy('name'),
        };

        return $services->get();
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories(): Collection
    {
        return Category::query()->with('parent')->orderBy('name')->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(ServiceRequest $request, ?Service $service = null): array
    {
        $attributes = $request->safe()->only([
            'category_id',
            'name',
            'slug',
            'short_description',
            'description',
            'price',
            'compare_price',
            'billing_type',
            'delivery_label',
            'video_url',
            'is_listed',
            'is_active',
            'is_best_seller',
            'is_deal',
            'deal_ends_at',
            'sort_order',
        ]);

        $details = $service?->details;
        $details = is_array($details) ? $details : [];
        $details['reasons'] = array_map(fn (array $point) => [
            'title' => $point['title'],
            'body' => $point['body'],
        ], $request->validated('points'));

        $durationPrices = $request->validated('duration_prices') ?? [];
        $hasDurationPrice = collect([3, 6, 12])->contains(fn (int $months) => ($durationPrices[$months] ?? null) !== null);

        if ($hasDurationPrice) {
            $details['durations'] = array_map(function (array $plan) use ($durationPrices) {
                if ($plan['months'] !== 1 && ($durationPrices[$plan['months']] ?? null) !== null) {
                    $plan['price'] = $durationPrices[$plan['months']];
                }

                return $plan;
            }, (new Service)->standardDurationPlans());
        } else {
            unset($details['durations']);
        }

        $attributes['details'] = $details;

        if ($request->hasFile('image')) {
            $attributes['image'] = $this->storeImage($request->file('image'), $attributes['slug']);

            if ($service?->image) {
                $this->deleteUploadedImage($service->image);
            }
        }

        return $attributes;
    }

    private function storeImage(UploadedFile $file, string $slug): string
    {
        File::ensureDirectoryExists(public_path('assets/services'));

        $extension = $file->extension() ?: 'jpg';
        $name = $slug.'-'.Str::lower(Str::random(8)).'.'.$extension;
        $file->move(public_path('assets/services'), $name);

        return $name;
    }

    private function deleteUploadedImage(string $filename): void
    {
        if (! preg_match('/-[a-z0-9]{8}\.[a-z0-9]+$/', $filename)) {
            return;
        }

        $path = public_path('assets/services/'.$filename);

        if (is_file($path)) {
            unlink($path);
        }
    }
}
