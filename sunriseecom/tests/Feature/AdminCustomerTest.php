<?php

use App\Models\Admin;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Quote;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a guest is sent to the admin login', function () {
    $this->get(route('admin.customers.index'))
        ->assertRedirect(route('admin.login'));
});

test('an admin can browse a customer and their orders and subscriptions', function () {
    $admin = Admin::factory()->create();
    $customer = User::factory()->create([
        'name' => 'Customer Asha',
        'email' => 'customer-asha@example.com',
    ]);
    $other = User::factory()->create([
        'name' => 'Customer Rohan',
        'email' => 'customer-rohan@example.com',
    ]);
    $service = Service::query()->where('is_active', true)->firstOrFail();

    $order = Order::query()->create([
        'number' => 'SR-CUST01',
        'user_id' => $customer->id,
        'name' => $customer->name,
        'email' => $customer->email,
        'status' => 'placed',
        'discount' => 0,
        'gst' => 180,
        'total' => 1180,
    ]);

    Subscription::query()->create([
        'user_id' => $customer->id,
        'order_id' => $order->id,
        'name' => 'Monthly SEO',
        'period' => 'monthly',
        'total_count' => 12,
        'paid_count' => 1,
        'cycle_amount' => 1200,
        'status' => 'active',
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.customers.index'))
        ->assertOk()
        ->assertSee('Customer Asha')
        ->assertSee('Customer Rohan');

    $this->actingAs($admin, 'admin')
        ->get(route('admin.customers.index', ['q' => 'Asha']))
        ->assertOk()
        ->assertSee('Customer Asha')
        ->assertDontSee('Customer Rohan');

    $this->actingAs($admin, 'admin')
        ->get(route('admin.customers.show', $customer))
        ->assertOk()
        ->assertSee('SR-CUST01')
        ->assertSee('Monthly SEO')
        ->assertSee('Active');

    Quote::query()->create([
        'service_id' => $service->id,
        'name' => 'Overview Quote',
        'email' => 'overview-quote@example.com',
        'message' => 'Need a price.',
        'status' => 'new',
    ]);
    CartItem::query()->create([
        'user_id' => $customer->id,
        'service_id' => $service->id,
        'quantity' => 1,
    ]);

    $hidden = Service::query()->where('is_active', false)->count();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.home'))
        ->assertOk()
        ->assertSee('New quotes')
        ->assertSee('Open carts')
        ->assertSee('Hidden services')
        ->assertSee((string) $hidden)
        ->assertSee('1', false);
});
