<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Support\AdminFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => AdminFilter::text($request, 'q'),
        ];

        $customers = User::query()
            ->withCount([
                'orders as placed_orders_count' => fn ($query) => $query->whereIn('status', Order::settledStatuses()),
                'subscriptions',
            ]);

        if ($filters['q'] !== '') {
            $term = AdminFilter::like($filters['q']);
            $customers->where(function ($query) use ($term): void {
                $query->where('name', 'like', $term)->orWhere('email', 'like', $term);
            });
        }

        return view('admin.customers.index', [
            'customers' => $customers->orderBy('name')->get(),
            'filters' => $filters,
            'filtering' => AdminFilter::active($filters),
        ]);
    }

    public function show(User $customer): View
    {
        return view('admin.customers.show', [
            'customer' => $customer,
            'orders' => $customer->orders()->latest()->get(),
            'subscriptions' => $customer->subscriptions()->with('order')->latest()->get(),
        ]);
    }
}
