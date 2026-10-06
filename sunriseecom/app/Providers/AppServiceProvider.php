<?php

namespace App\Providers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Quote;
use App\Models\Review;
use App\Models\Service;
use App\Models\SupportMessage;
use App\Models\User;
use App\Models\WishlistItem;
use App\Support\Cart;
use App\Support\Wishlist;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(
            ['partials.store-category-nav', 'partials.store-categories'],
            function ($view): void {
                $categories = once(fn () => Category::query()->active()->orderBy('sort_order')->get());
                $homeOrder = array_flip(Category::HOME_SLUGS);

                $view->with([
                    'navCategories' => $categories->where('show_in_nav', true)->values(),
                    'homeCategories' => $categories
                        ->where('show_on_home', true)
                        ->sortBy(fn (Category $category) => $homeOrder[$category->slug] ?? PHP_INT_MAX)
                        ->values(),
                    'categories' => $categories,
                ]);
            }
        );

        View::composer(['partials.store-deals', 'store.deals'], function ($view): void {
            $deals = once(fn () => Service::deals()->get());

            $view->with('deals', $view->name() === 'store.deals' ? $deals : $deals->take(3));
        });

        View::composer('partials.store-best-sellers', function ($view): void {
            $view->with([
                'bestSellers' => Service::bestSellers()->with('category')->get(),
            ]);
        });

        View::composer('partials.store-header', function ($view): void {
            $view->with('cartCount', (new Cart(request()))->count());
            $view->with('wishlistCount', (new Wishlist(request()))->count());
        });

        View::composer(['partials.store-service-card', 'services.show'], function ($view): void {
            $view->with('wishlistIds', (new Wishlist(request()))->ids());
        });

        View::composer(['admin.layout', 'admin.home'], function ($view): void {
            $view->with('waiting', once(function (): array {
                $distinctUsers = fn (string $table): int => (int) $table::query()->selectRaw('count(distinct user_id) as aggregate')->value('aggregate');

                return [
                    'categories' => Category::query()->count(),
                    'services' => Service::query()->count(),
                    'hidden' => Service::query()->where('is_active', false)->count(),
                    'packages' => Plan::query()->count(),
                    'orders' => Order::query()->where('status', 'placed')->count(),
                    'customers' => User::query()->count(),
                    'carts' => $distinctUsers(CartItem::class),
                    'wishlists' => $distinctUsers(WishlistItem::class),
                    'coupons' => Coupon::query()->count(),
                    'quotes' => Quote::query()->where('status', 'new')->count(),
                    'reviews' => Review::query()->count(),
                    'support' => SupportMessage::query()->where('status', 'new')->count(),
                ];
            }));
        });

        View::composer('partials.store-plans', function ($view): void {
            $view->with([
                'plans' => Plan::active()->with('items.service')->get(),
            ]);
        });
    }
}
