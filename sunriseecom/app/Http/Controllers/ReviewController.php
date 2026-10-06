<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, Service $service): RedirectResponse
    {
        abort_unless($service->is_active, 404);
        abort_unless($this->purchasedService($request, $service), 403);

        Review::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'service_id' => $service->id,
            ],
            [
                ...$request->safe()->only(['rating', 'body']),
                'plan_id' => null,
            ],
        );

        $service->syncReviewStats();

        return redirect()
            ->route('services.show', $service)
            ->with('status', 'Review saved.');
    }

    public function storePackage(ReviewRequest $request, Plan $plan): RedirectResponse
    {
        abort_unless($plan->is_active, 404);
        abort_unless($this->purchasedPackage($request, $plan), 403);

        Review::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'plan_id' => $plan->id,
            ],
            [
                ...$request->safe()->only(['rating', 'body']),
                'service_id' => null,
            ],
        );

        return redirect()
            ->route('packages.show', $plan)
            ->with('status', 'Review saved.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        abort_unless($review->user_id === $request->user()->id, 403);

        $service = $review->service;
        $plan = $review->plan;
        $review->delete();
        $service?->syncReviewStats();

        return redirect()
            ->to($plan ? route('packages.show', $plan) : route('services.show', $service))
            ->with('status', 'Review removed.');
    }

    private function purchasedService(ReviewRequest $request, Service $service): bool
    {
        return OrderItem::query()
            ->where('service_id', $service->id)
            ->whereHas('order', function ($query) use ($request): void {
                $query->where('user_id', $request->user()->id)
                    ->whereIn('status', Order::settledStatuses());
            })
            ->exists();
    }

    private function purchasedPackage(ReviewRequest $request, Plan $plan): bool
    {
        return OrderItem::query()
            ->where('plan_id', $plan->id)
            ->whereHas('order', function ($query) use ($request): void {
                $query->where('user_id', $request->user()->id)
                    ->whereIn('status', Order::settledStatuses());
            })
            ->exists();
    }
}
