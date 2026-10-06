<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Support\AdminFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => AdminFilter::text($request, 'q'),
            'visibility' => AdminFilter::choice($request, 'visibility', ['visible', 'hidden']),
        ];

        $reviews = Review::query()->with(['user', 'service', 'plan']);

        if ($filters['q'] !== '') {
            $term = AdminFilter::like($filters['q']);
            $reviews->where(function ($query) use ($term): void {
                $query->where('body', 'like', $term)
                    ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $term)->orWhere('email', 'like', $term))
                    ->orWhereHas('service', fn ($service) => $service->where('name', 'like', $term))
                    ->orWhereHas('plan', fn ($plan) => $plan->where('name', 'like', $term));
            });
        }

        if ($filters['visibility'] === 'visible') {
            $reviews->where('is_hidden', false);
        } elseif ($filters['visibility'] === 'hidden') {
            $reviews->where('is_hidden', true);
        }

        return view('admin.reviews.index', [
            'reviews' => $reviews->latest()->get(),
            'filters' => $filters,
            'filtering' => AdminFilter::active($filters),
        ]);
    }

    public function hide(Review $review): RedirectResponse
    {
        $review->update(['is_hidden' => ! $review->is_hidden]);
        $review->service?->syncReviewStats();

        return back()->with('status', $review->is_hidden ? 'Review hidden.' : 'Review is visible again.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $service = $review->service;
        $review->delete();
        $service?->syncReviewStats();

        return back()->with('status', 'Review deleted.');
    }
}
