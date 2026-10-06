<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReturnsToFilteredIndex;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageRequest;
use App\Models\Plan;
use App\Models\Service;
use App\Support\AdminFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    use ReturnsToFilteredIndex;

    public function index(Request $request): View
    {
        $filters = [
            'q' => AdminFilter::text($request, 'q'),
            'visibility' => AdminFilter::choice($request, 'visibility', ['visible', 'hidden']),
            'min' => AdminFilter::number($request, 'min'),
            'max' => AdminFilter::number($request, 'max'),
            'sort' => AdminFilter::choice($request, 'sort', ['name', 'price_asc', 'price_desc']) ?: 'name',
        ];

        $packages = Plan::query()->with('items.service');

        if ($filters['q'] !== '') {
            $packages->where('name', 'like', AdminFilter::like($filters['q']));
        }

        if ($filters['visibility'] === 'visible') {
            $packages->where('is_active', true);
        } elseif ($filters['visibility'] === 'hidden') {
            $packages->where('is_active', false);
        }

        if ($filters['min'] !== null) {
            $packages->where('monthly_price', '>=', $filters['min']);
        }

        if ($filters['max'] !== null) {
            $packages->where('monthly_price', '<=', $filters['max']);
        }

        match ($filters['sort']) {
            'price_asc' => $packages->orderBy('monthly_price')->orderBy('name'),
            'price_desc' => $packages->orderByDesc('monthly_price')->orderBy('name'),
            default => $packages->orderBy('sort_order')->orderBy('name'),
        };

        $all = Plan::query()->get();

        return view('admin.packages.index', [
            'packages' => $packages->get(),
            'counts' => [
                'all' => $all->count(),
                'visible' => $all->where('is_active', true)->count(),
            ],
            'filters' => $filters,
            'filtering' => AdminFilter::active($filters, ['sort' => 'name']),
        ]);
    }

    public function create(): View
    {
        return view('admin.packages.form', [
            'package' => new Plan([
                'is_active' => true,
                'sort_order' => 0,
            ]),
            'services' => $this->services(),
            'selected' => [],
        ]);
    }

    public function store(PackageRequest $request): RedirectResponse
    {
        $package = Plan::query()->create($this->attributes($request));
        $this->syncServices($package, $request->validated('services'));

        return redirect()
            ->route('admin.packages.index')
            ->with('status', 'Package saved.');
    }

    public function edit(Plan $package): View
    {
        $package->load('items');

        return view('admin.packages.form', [
            'package' => $package,
            'services' => $this->services(),
            'selected' => $package->items->pluck('service_id')->all(),
        ]);
    }

    public function update(PackageRequest $request, Plan $package): RedirectResponse
    {
        $package->update($this->attributes($request));
        $this->syncServices($package, $request->validated('services'));

        return redirect()
            ->route('admin.packages.index')
            ->with('status', 'Package saved.');
    }

    public function hide(Plan $package): RedirectResponse
    {
        $package->update(['is_active' => false]);

        return $this->filteredIndex('admin.packages.index', 'Package hidden from the store.');
    }

    public function restore(Plan $package): RedirectResponse
    {
        $package->update(['is_active' => true]);

        return $this->filteredIndex('admin.packages.index', 'Package is visible on the store.');
    }

    public function destroy(Plan $package): RedirectResponse
    {
        $package->delete();

        return $this->filteredIndex('admin.packages.index', 'Package deleted.');
    }

    /**
     * @return Collection<int, Service>
     */
    private function services(): Collection
    {
        return Service::query()->with('category')->orderBy('name')->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(PackageRequest $request): array
    {
        return $request->safe()->only([
            'name',
            'slug',
            'monthly_price',
            'yearly_price',
            'sort_order',
            'is_active',
        ]);
    }

    /**
     * @param  list<int>  $serviceIds
     */
    private function syncServices(Plan $package, array $serviceIds): void
    {
        $package->items()->delete();

        $package->items()->createMany(collect($serviceIds)->values()->map(
            fn (int $serviceId, int $index) => [
                'service_id' => $serviceId,
                'sort_order' => $index + 1,
            ],
        )->all());
    }
}
