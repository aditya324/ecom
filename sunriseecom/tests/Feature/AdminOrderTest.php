<?php

use App\Models\Admin;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.razorpay.key' => 'rzp_test_example',
        'services.razorpay.secret' => 'test-secret',
    ]);
});

test('an admin can refund a one-time payment or cancel it', function () {
    $admin = Admin::factory()->create();
    $order = Order::query()->create([
        'number' => 'SR-ONCE01',
        'user_id' => User::factory()->create()->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'placed',
        'razorpay_payment_id' => 'pay_once',
        'discount' => 0,
        'gst' => 810,
        'total' => 5310,
    ]);

    Http::fake([
        'api.razorpay.com/*' => Http::response(['id' => 'rfnd_once', 'status' => 'processed']),
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Refund')
        ->assertSee('Cancel order');

    $this->post(route('admin.orders.refund', $order))
        ->assertRedirect()
        ->assertSessionHas('status', 'Payment refunded.');

    expect($order->fresh()->status)->toBe('refunded')
        ->and((float) $order->fresh()->refunded_amount)->toBe(5310.0);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/payments/pay_once/refund')
        && $request['amount'] === 531000);
});

test('an admin can refund a subscription charge and cancel the subscription separately', function () {
    $admin = Admin::factory()->create();
    $order = Order::query()->create([
        'number' => 'SR-SUB001',
        'user_id' => User::factory()->create()->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'placed',
        'razorpay_payment_id' => 'pay_sub',
        'discount' => 0,
        'gst' => 1080,
        'total' => 7080,
    ]);
    $subscription = Subscription::query()->create([
        'user_id' => $order->user_id,
        'order_id' => $order->id,
        'name' => 'test service',
        'period' => 'monthly',
        'total_count' => 3,
        'paid_count' => 1,
        'cycle_amount' => 7080,
        'status' => 'active',
        'razorpay_subscription_id' => 'sub_admin',
        'razorpay_payment_id' => 'pay_sub',
    ]);

    Http::fake([
        'api.razorpay.com/*' => Http::response(['id' => 'rfnd_sub', 'status' => 'processed']),
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Refund')
        ->assertSee('Cancel subscription')
        ->assertDontSee('Cancel order');

    $this->post(route('admin.orders.refund', $order))
        ->assertRedirect()
        ->assertSessionHas('status', 'Payment refunded.');

    expect($order->fresh()->status)->toBe('refunded')
        ->and($subscription->fresh()->status)->toBe('active');

    $this->post(route('admin.orders.cancel', $order))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($subscription->fresh()->status)->toBe('cancelled');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/subscriptions/sub_admin/cancel'));
});

test('an admin can refund only the subscription when the order also has a one-time payment', function () {
    $admin = Admin::factory()->create();
    $order = Order::query()->create([
        'number' => 'SR-MIX001',
        'user_id' => User::factory()->create()->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'placed',
        'razorpay_order_id' => 'order_once',
        'razorpay_payment_id' => 'pay_once',
        'razorpay_amount' => 531000,
        'discount' => 0,
        'gst' => 2310,
        'total' => 15143,
    ]);
    $order->items()->create([
        'service_name' => 'E-commerce Website Setup & Development',
        'duration_label' => 'One-time',
        'price' => 4500,
        'discount' => 0,
        'gst' => 810,
    ]);
    $order->items()->create([
        'service_name' => 'test service',
        'duration_label' => '3 Months · renews monthly',
        'months' => 3,
        'price' => 8333,
        'discount' => 0,
        'gst' => 1500,
    ]);
    $subscription = Subscription::query()->create([
        'user_id' => $order->user_id,
        'order_id' => $order->id,
        'name' => 'test service',
        'period' => 'monthly',
        'total_count' => 3,
        'paid_count' => 1,
        'cycle_amount' => 9833,
        'status' => 'active',
        'razorpay_subscription_id' => 'sub_mix',
        'razorpay_payment_id' => 'pay_sub',
    ]);

    Http::fake([
        'api.razorpay.com/*' => Http::response(['id' => 'rfnd_sub_only', 'status' => 'processed']),
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Refund E-commerce Website Setup & Development')
        ->assertSee('Refund test service')
        ->assertSee('The other payment on this order stays paid.', false);

    $this->post(route('admin.orders.refund', $order), [
        'part' => 'subscription-'.$subscription->id,
    ])->assertRedirect()->assertSessionHas('status', 'Payment refunded.');

    $order->refresh();

    expect($order->status)->toBe('placed')
        ->and((float) $order->refunded_amount)->toBe(9833.0)
        ->and($order->refunded_payments)->toBe(['pay_sub']);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/payments/pay_sub/refund') && $request['amount'] === 983300);
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/payments/pay_once/refund'));

    $this->get(route('admin.orders.show', $order))
        ->assertSee('test service refunded')
        ->assertSee('Refund E-commerce Website Setup & Development');
});

test('the admin orders page totals every order after refunds', function () {
    Order::query()->create([
        'number' => 'SR-SUM001',
        'user_id' => User::factory()->create()->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'placed',
        'discount' => 0,
        'gst' => 0,
        'total' => 5000,
    ]);
    Order::query()->create([
        'number' => 'SR-SUM002',
        'user_id' => User::factory()->create()->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'refunded',
        'discount' => 0,
        'gst' => 0,
        'total' => 3000,
        'refunded_amount' => 1000,
    ]);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('₹7,000')
        ->assertSee('Total');

    $this->get(route('admin.home'))
        ->assertOk()
        ->assertSee('₹7,000');
});

test('a customer cannot refund or cancel from the admin order page', function () {
    $order = Order::query()->create([
        'number' => 'SR-NOPE01',
        'user_id' => User::factory()->create()->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'placed',
        'razorpay_payment_id' => 'pay_nope',
        'discount' => 0,
        'gst' => 0,
        'total' => 1000,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.orders.refund', $order))
        ->assertRedirect(route('admin.login'));

    expect($order->fresh()->status)->toBe('placed');
});
