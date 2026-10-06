<?php

use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Support\Bill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.razorpay.key' => 'rzp_test_example',
        'services.razorpay.secret' => 'test-secret',
    ]);
});

test('a mixed cart pays once for the one-time service and subscribes to the monthly service', function () {
    $monthly = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $once = Service::query()->where('slug', 'comprehensive-technical-seo')->firstOrFail();
    $onceGross = (int) round($once->offer(null)['price']);
    $onceAmount = ($onceGross + Bill::gstOn($onceGross)) * 100;

    $this->actingAs(User::factory()->create());
    $this->post(route('cart.store', $monthly), ['months' => 3]);
    $this->post(route('cart.store', $once));

    Http::fake(function ($request) {
        if (str_contains($request->url(), '/orders')) {
            return Http::response(['id' => 'order_mixed_cart']);
        }

        if (str_contains($request->url(), '/plans')) {
            return Http::response(['id' => 'plan_mixed']);
        }

        return Http::response(['id' => 'sub_mixed']);
    });

    $this->get(route('checkout.create'))
        ->assertOk()
        ->assertSee('Pay with Razorpay')
        ->assertSee('renews monthly')
        ->assertSee('https://checkout.razorpay.com/v1/checkout.js', false);

    $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk()
        ->assertJsonPath('key', 'rzp_test_example')
        ->assertJsonPath('steps.0.type', 'order')
        ->assertJsonPath('steps.0.order_id', 'order_mixed_cart')
        ->assertJsonPath('steps.0.amount', $onceAmount)
        ->assertJsonPath('steps.1.type', 'subscription')
        ->assertJsonPath('steps.1.subscription_id', 'sub_mixed');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/subscriptions') && $request['total_count'] === 3);

    expect(Order::query()->count())->toBe(2);

    $onceOrder = Order::query()->where('razorpay_order_id', 'order_mixed_cart')->firstOrFail();
    $subscriptionOrder = Order::query()->whereKeyNot($onceOrder->id)->firstOrFail();

    expect($onceOrder->status)->toBe('pending')
        ->and($onceOrder->items)->toHaveCount(1)
        ->and($onceOrder->items->first()->service_name)->toBe($once->name)
        ->and($onceOrder->subscriptions)->toHaveCount(0)
        ->and($subscriptionOrder->status)->toBe('pending')
        ->and($subscriptionOrder->items)->toHaveCount(1)
        ->and($subscriptionOrder->subscriptions)->toHaveCount(1);

    $this->get(route('orders.show', $onceOrder))->assertNotFound();
    $this->get(route('cart.index'))
        ->assertSee('Instagram Marketing')
        ->assertSee('Comprehensive Technical SEO Audit');
});

test('a paid one-time service leaves the unpaid subscription in the cart and shows on the profile', function () {
    $monthly = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $once = Service::query()->where('slug', 'comprehensive-technical-seo')->firstOrFail();

    $this->actingAs(User::factory()->create());
    $this->post(route('cart.store', $monthly), ['months' => 3]);
    $this->post(route('cart.store', $once));

    Http::fake(function ($request) {
        if (str_contains($request->url(), '/orders')) {
            return Http::response(['id' => 'order_once_only']);
        }

        if (str_contains($request->url(), '/plans')) {
            return Http::response(['id' => 'plan_left']);
        }

        return Http::response(['id' => 'sub_left']);
    });

    $started = $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk();

    $paymentId = 'pay_once_only';

    $this->postJson(route('checkout.payment'), [
        'razorpay_order_id' => 'order_once_only',
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature' => hash_hmac('sha256', 'order_once_only|'.$paymentId, 'test-secret'),
    ])->assertOk()
        ->assertJsonPath('done', false);

    $onceOrder = Order::query()->where('razorpay_order_id', 'order_once_only')->firstOrFail();

    expect($onceOrder->status)->toBe('placed')
        ->and($onceOrder->items)->toHaveCount(1)
        ->and(Order::query()->where('status', 'pending')->count())->toBe(1);

    $this->get(route('profile.orders'))
        ->assertOk()
        ->assertSee($onceOrder->number)
        ->assertSee($once->name)
        ->assertDontSee($monthly->name);

    $this->get(route('orders.show', $onceOrder))
        ->assertOk()
        ->assertSee($once->name);

    $this->get(route('cart.index'))
        ->assertSee($monthly->name)
        ->assertDontSee($once->name);

    expect($started->json('steps.1.subscription_id'))->toBe('sub_left');
});

test('paying one subscription leaves the other subscription in the cart', function () {
    $first = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $second = Service::query()->where('slug', 'high-conversion-google-ads')->firstOrFail();

    $this->actingAs(User::factory()->create());
    $this->post(route('cart.store', $first), ['months' => 1]);
    $this->post(route('cart.store', $second), ['months' => 1]);

    Http::fake(function ($request) {
        if (str_contains($request->url(), '/plans')) {
            return Http::response(['id' => 'plan_'.substr(md5($request['item']['name']), 0, 6)]);
        }

        return Http::response(['id' => 'sub_'.substr(md5($request['plan_id']), 0, 6)]);
    });

    $started = $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk();

    $subscriptionId = $started->json('steps.0.subscription_id');
    $paymentId = 'pay_one_subscription';

    $this->postJson(route('checkout.payment'), [
        'razorpay_subscription_id' => $subscriptionId,
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature' => hash_hmac('sha256', $paymentId.'|'.$subscriptionId, 'test-secret'),
    ])->assertOk()->assertJsonPath('done', false);

    expect(Order::query()->where('status', 'placed')->count())->toBe(1)
        ->and(Order::query()->where('status', 'pending')->count())->toBe(1);

    $this->get(route('profile.orders'))
        ->assertSee($first->name)
        ->assertDontSee($second->name);

    $this->get(route('cart.index'))
        ->assertSee($second->name)
        ->assertDontSee($first->name);
});

test('starting checkout again reuses the unpaid subscription', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $this->actingAs(User::factory()->create());
    $this->post(route('cart.store', $service), ['months' => 1]);

    Http::fake(function ($request) {
        if ($request->method() === 'GET') {
            return Http::response(['status' => 'created']);
        }

        if (str_contains($request->url(), '/cancel')) {
            return Http::response(['status' => 'cancelled']);
        }

        if (str_contains($request->url(), '/plans')) {
            return Http::response(['id' => 'plan_'.strtolower(Str::random(6))]);
        }

        return Http::response(['id' => 'sub_'.strtolower(Str::random(6))]);
    });

    $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk()->assertJsonPath('steps.0.type', 'subscription');

    $this->post(route('cart.quantity'), [
        'key' => $service->id.'-1',
        'quantity' => 2,
    ]);

    $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk();

    expect(Order::query()->where('status', 'pending')->count())->toBe(1)
        ->and(Order::query()->where('status', 'pending')->first()->items->first()->price)->not->toBeNull();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/cancel'));

    $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk()->assertJsonPath('steps.0.subscription_id', Order::query()->first()->subscriptions()->value('razorpay_subscription_id'));

    expect(Order::query()->count())->toBe(1);
});

test('a rejected payment leaves the order unpaid and the cart in place', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $this->actingAs(User::factory()->create());
    $this->post(route('cart.store', $service), ['months' => 1]);

    Http::fake([
        'api.razorpay.com/*' => Http::response(['id' => 'sub_unpaid']),
    ]);

    $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk();

    $this->post(route('checkout.payment'), [
        'razorpay_subscription_id' => 'sub_unpaid',
        'razorpay_payment_id' => 'pay_fake',
        'razorpay_signature' => 'not-a-real-signature',
    ])->assertRedirect(route('checkout.create'))
        ->assertSessionHasErrors('payment');

    expect(Order::query()->firstOrFail()->status)->toBe('pending');

    $this->get(route('cart.index'))->assertSee('Instagram Marketing');
});

test('a captured webhook marks the same order paid', function () {
    $service = Service::query()->where('slug', 'comprehensive-technical-seo')->firstOrFail();
    $gross = (int) round($service->offer(null)['price']);
    $amount = ($gross + Bill::gstOn($gross)) * 100;

    $this->actingAs(User::factory()->create());
    $this->post(route('cart.store', $service));

    Http::fake([
        'api.razorpay.com/v1/orders' => Http::response(['id' => 'order_webhook']),
    ]);

    $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk();

    $body = json_encode([
        'event' => 'payment.captured',
        'payload' => [
            'payment' => [
                'entity' => [
                    'id' => 'pay_webhook',
                    'order_id' => 'order_webhook',
                    'amount' => $amount,
                    'currency' => 'INR',
                    'status' => 'captured',
                ],
            ],
        ],
    ], JSON_THROW_ON_ERROR);

    $this->call('POST', route('payments.razorpay.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X-Razorpay-Signature' => hash_hmac('sha256', $body, 'test-secret'),
    ], $body)->assertNoContent();

    $order = Order::query()->firstOrFail();

    expect($order->status)->toBe('placed')
        ->and($order->razorpay_payment_id)->toBe('pay_webhook');

    $this->call('POST', route('payments.razorpay.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X-Razorpay-Signature' => 'invalid',
    ], $body)->assertBadRequest();
});

test('razorpay downtime does not leave an unpaid order behind', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $this->actingAs(User::factory()->create());
    $this->post(route('checkout.buy', $service), ['months' => 1]);

    Http::fake([
        'api.razorpay.com/*' => Http::response(['error' => 'unavailable'], 500),
    ]);

    $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertStatus(502);

    expect(Order::query()->count())->toBe(0);
});
