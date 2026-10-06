<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Service;
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

        View::composer('partials.store-plans', function ($view): void {
            $view->with([
                'plans' => Plan::active()->with('items.service')->get(),
            ]);
        });
    }
}
