<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Support\AdminFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => AdminFilter::text($request, 'q'),
            'billing' => AdminFilter::choice($request, 'billing', ['one-time', 'monthly', 'hourly']),
        ];

        $items = CartItem::query()
            ->with(['user', 'service'])
            ->latest()
            ->get()
            ->filter(function (CartItem $item) use ($filters) {
                if ($item->user === null || $item->service === null) {
                    return false;
                }

                if ($filters['billing'] !== '' && $item->service->billing_type !== $filters['billing']) {
                    return false;
                }

                if ($filters['q'] === '') {
                    return true;
                }

                $haystack = Str::lower($item->user->name.' '.$item->user->email.' '.$item->service->name);

                return str_contains($haystack, Str::lower($filters['q']));
            })
            ->groupBy('user_id');

        return view('admin.carts.index', [
            'carts' => $items,
            'filters' => $filters,
            'filtering' => AdminFilter::active($filters),
        ]);
    }
}
