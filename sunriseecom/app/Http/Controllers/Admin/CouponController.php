<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReturnsToFilteredIndex;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use App\Models\Service;
use App\Support\AdminFilter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    use ReturnsToFilteredIndex;

    public function index(Request $request): View
    {
        $filters = [
            'q' => AdminFilter::text($request, 'q'),
            'type' => AdminFilter::choice($request, 'type', ['percent', 'fixed']),
            'visibility' => AdminFilter::choice($request, 'visibility', ['active', 'hidden']),
        ];

        $coupons = Coupon::query()->with('services')->withCount('uses');

        if ($filters['q'] !== '') {
            $coupons->where('code', 'like', AdminFilter::like($filters['q']));
        }

        if ($filters['type'] !== '') {
            $coupons->where('type', $filters['type']);
        }

        if ($filters['visibility'] === 'active') {
            $coupons->where('is_active', true);
        } elseif ($filters['visibility'] === 'hidden') {
            $coupons->where('is_active', false);
        }

        return view('admin.coupons.index', [
            'coupons' => $coupons->orderBy('code')->get(),
            'filters' => $filters,
            'filtering' => AdminFilter::active($filters),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.form', [
            'coupon' => new Coupon([
                'type' => 'percent',
                'per_user_limit' => 1,
                'usage_limit' => 1,
                'is_active' => true,
            ]),
            'services' => $this->services(),
            'selected' => [],
        ]);
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        $coupon = Coupon::query()->create($request->safe()->only(['code', 'type', 'amount', 'per_user_limit', 'usage_limit', 'is_active']));
        $coupon->services()->sync($request->validated('services'));

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', 'Coupon saved.');
    }

    public function edit(Coupon $coupon): View
    {
        $coupon->load('services');

        return view('admin.coupons.form', [
            'coupon' => $coupon,
            'services' => $this->services(),
            'selected' => $coupon->services->pluck('id')->all(),
        ]);
    }

    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($request->safe()->only(['code', 'type', 'amount', 'per_user_limit', 'usage_limit', 'is_active']));
        $coupon->services()->sync($request->validated('services'));

        return redirect()
            ->route('admin.coupons.index')
            ->with('status', 'Coupon saved.');
    }

    public function hide(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => false]);

        return $this->filteredIndex('admin.coupons.index', 'Coupon hidden. It can no longer be used.');
    }

    public function restore(Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => true]);

        return $this->filteredIndex('admin.coupons.index', 'Coupon can be used again.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return $this->filteredIndex('admin.coupons.index', 'Coupon deleted.');
    }

    /**
     * @return Collection<int, Service>
     */
    private function services()
    {
        return Service::query()->with('category')->orderBy('name')->get();
    }
}
