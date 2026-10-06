<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WishlistItem;
use App\Support\AdminFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => AdminFilter::text($request, 'q'),
        ];

        $items = WishlistItem::query()
            ->with(['user', 'service.category'])
            ->latest()
            ->get()
            ->filter(function (WishlistItem $item) use ($filters) {
                if ($item->user === null || $item->service === null) {
                    return false;
                }

                if ($filters['q'] === '') {
                    return true;
                }

                $haystack = Str::lower($item->user->name.' '.$item->user->email.' '.$item->service->name);

                return str_contains($haystack, Str::lower($filters['q']));
            })
            ->groupBy('user_id');

        return view('admin.wishlists.index', [
            'wishlists' => $items,
            'filters' => $filters,
            'filtering' => AdminFilter::active($filters),
        ]);
    }
}
