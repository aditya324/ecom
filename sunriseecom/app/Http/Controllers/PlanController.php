<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function show(Plan $plan): View
    {
        abort_unless($plan->is_active, 404);

        $plan->load(['items.service', 'reviews.user']);
        $user = request()->user();
        $reviews = $plan->reviews;
        $reviewCount = $reviews->count();
        $bought = $user !== null && OrderItem::query()
            ->where('plan_id', $plan->id)
            ->whereHas('order', fn ($query) => $query->where('user_id', $user->id)->whereIn('status', Order::settledStatuses()))
            ->exists();

        return view('packages.show', [
            'plan' => $plan,
            'ownReview' => $user ? Review::query()->where('user_id', $user->id)->where('plan_id', $plan->id)->first() : null,
            'canReview' => $bought,
            'reviewCount' => $reviewCount,
            'reviewAverage' => $reviewCount > 0 ? round((float) $reviews->avg('rating'), 1) : null,
            'reviewBreakdown' => collect(range(5, 1))->mapWithKeys(
                fn (int $star): array => [$star => $reviews->where('rating', $star)->count()],
            ),
        ]);
    }

    public function subscribe(Request $request, Plan $plan): RedirectResponse
    {
        abort_unless($plan->is_active, 404);

        $interval = $request->string('interval')->toString();

        if (! in_array($interval, ['monthly', 'yearly'], true)) {
            $interval = 'yearly';
        }

        $request->session()->put('checkout', [
            'source' => 'plan',
            'plan_id' => $plan->id,
            'interval' => $interval,
        ]);

        if ($request->user() === null) {
            $request->session()->put('url.intended', route('checkout.create'));

            return redirect()->route('login');
        }

        return redirect()->route('checkout.create');
    }
}
