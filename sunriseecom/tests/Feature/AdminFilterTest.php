<?php

use App\Models\Admin;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Quote;
use App\Models\Service;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an admin can filter every list', function () {
    $admin = Admin::factory()->create();
    $customer = User::factory()->create(['name' => 'Filter Asha', 'email' => 'filter-asha@example.com']);
    $other = User::factory()->create(['name' => 'Filter Rohan', 'email' => 'filter-rohan@example.com']);
    $monthly = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $once = Service::query()->where('billing_type', 'one-time')->firstOrFail();

    Order::query()->create([
        'number' => 'SR-FILT01',
        'user_id' => $customer->id,
        'name' => 'Filter Asha',
        'email' => 'filter-asha@example.com',
        'status' => 'placed',
        'discount' => 0,
        'gst' => 0,
        'total' => 8000,
    ]);
    Order::query()->create([
        'number' => 'SR-FILT02',
        'user_id' => $other->id,
        'name' => 'Filter Rohan',
        'email' => 'filter-rohan@example.com',
        'status' => 'pending',
        'discount' => 0,
        'gst' => 0,
        'total' => 500,
    ]);

    Plan::query()->create([
        'name' => 'Filter Hidden Plan',
        'slug' => 'filter-hidden-plan',
        'monthly_price' => 100,
        'yearly_price' => 1000,
        'is_active' => false,
    ]);
    Plan::query()->create([
        'name' => 'Filter Visible Plan',
        'slug' => 'filter-visible-plan',
        'monthly_price' => 9000,
        'yearly_price' => 90000,
        'is_active' => true,
    ]);

    Coupon::query()->create([
        'code' => 'FILTER10',
        'type' => 'percent',
        'amount' => 10,
        'per_user_limit' => 1,
        'usage_limit' => 5,
        'is_active' => true,
    ]);
    Coupon::query()->create([
        'code' => 'FILTERCASH',
        'type' => 'fixed',
        'amount' => 50,
        'per_user_limit' => 1,
        'usage_limit' => 5,
        'is_active' => true,
    ]);

    Quote::query()->create([
        'service_id' => $monthly->id,
        'name' => 'Filter Quote',
        'email' => 'filter-quote@example.com',
        'message' => 'Need a price.',
        'status' => 'new',
    ]);
    Quote::query()->create([
        'service_id' => $once->id,
        'name' => 'Answered Quote',
        'email' => 'answered-quote@example.com',
        'message' => 'Already priced.',
        'status' => 'replied',
    ]);

    CartItem::query()->create([
        'user_id' => $customer->id,
        'service_id' => $monthly->id,
        'months' => 1,
        'quantity' => 1,
    ]);
    CartItem::query()->create([
        'user_id' => $other->id,
        'service_id' => $once->id,
        'quantity' => 1,
    ]);

    WishlistItem::query()->create([
        'user_id' => $customer->id,
        'service_id' => $monthly->id,
    ]);
    WishlistItem::query()->create([
        'user_id' => $other->id,
        'service_id' => $once->id,
    ]);

    $category = Category::query()->where('slug', 'development')->firstOrFail();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.orders.index', ['q' => 'Filter Asha', 'status' => 'placed']))
        ->assertOk()
        ->assertSee('SR-FILT01')
        ->assertDontSee('SR-FILT02');

    $this->get(route('admin.orders.index', ['status' => 'pending']))
        ->assertOk()
        ->assertSee('SR-FILT02')
        ->assertDontSee('SR-FILT01');

    $this->get(route('admin.categories.index', ['q' => $category->name]))
        ->assertOk()
        ->assertSee($category->name)
        ->assertDontSee('Email Marketing');

    $this->get(route('admin.packages.index', ['visibility' => 'hidden']))
        ->assertOk()
        ->assertSee('Filter Hidden Plan')
        ->assertDontSee('Filter Visible Plan');

    $this->get(route('admin.coupons.index', ['type' => 'percent', 'q' => 'FILTER']))
        ->assertOk()
        ->assertSee('FILTER10')
        ->assertDontSee('FILTERCASH');

    $this->get(route('admin.quotes.index', ['status' => 'new', 'q' => 'Filter Quote']))
        ->assertOk()
        ->assertSee('Filter Quote')
        ->assertDontSee('Answered Quote');

    $this->get(route('admin.carts.index', ['q' => 'Filter Asha', 'billing' => 'monthly']))
        ->assertOk()
        ->assertSee('Filter Asha')
        ->assertSee($monthly->name)
        ->assertDontSee('Filter Rohan');

    $this->get(route('admin.wishlists.index', ['q' => 'Filter Rohan']))
        ->assertOk()
        ->assertSee('Filter Rohan')
        ->assertDontSee('Filter Asha');
});
