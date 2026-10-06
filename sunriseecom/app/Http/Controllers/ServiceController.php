<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\Service;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function show(Service $service): View
    {
        abort_unless($service->is_active, 404);

        $service->load(['category', 'reviews.user']);
        $user = request()->user();
        $bought = $user !== null && OrderItem::query()
            ->where('service_id', $service->id)
            ->whereHas('order', fn ($query) => $query->where('user_id', $user->id)->whereIn('status', Order::settledStatuses()))
            ->exists();
        $service->syncReviewStats();
        $reviews = $service->reviews;
        $reviewCount = $reviews->count();

        return view('services.show', [
            'service' => $service,
            'ownReview' => $user ? Review::query()->where('user_id', $user->id)->where('service_id', $service->id)->first() : null,
            'canReview' => $bought,
            'reviewCount' => $reviewCount,
            'reviewAverage' => $reviewCount > 0 ? round((float) $reviews->avg('rating'), 1) : null,
            'reviewBreakdown' => collect(range(5, 1))->mapWithKeys(
                fn (int $star): array => [$star => $reviews->where('rating', $star)->count()],
            ),
        ]);
    }
}
