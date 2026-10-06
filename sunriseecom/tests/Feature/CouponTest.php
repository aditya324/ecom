<?php

use App\Models\Admin;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use App\Support\Bill;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an admin can create a coupon for chosen services', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->post(route('admin.coupons.store'), [
            'code' => 'launch 10',
            'type' => 'percent',
            'amount' => 10,
            'per_user_limit' => 2,
            'usage_limit' => 50,
            'services' => [$service->id],
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.coupons.index'));

    $coupon = Coupon::query()->where('code', 'LAUNCH10')->firstOrFail();

    expect($coupon->type)->toBe('percent')
        ->and((float) $coupon->amount)->toBe(10.0)
        ->and($coupon->per_user_limit)->toBe(2)
        ->and($coupon->usage_limit)->toBe(50)
        ->and($coupon->services()->pluck('services.id')->all())->toBe([$service->id]);

    $this->get(route('admin.coupons.index'))
        ->assertOk()
        ->assertSee('LAUNCH10')
        ->assertSee('Instagram Marketing');
});

test('a coupon discounts only the chosen service', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $other = Service::query()->where('slug', 'high-conversion-google-ads')->firstOrFail();
    $offer = $service->offer(1);
    $otherOffer = $other->offer(1);
    $discount = (float) round($offer['price'] * 0.10);
    $chosenTaxable = (int) round($offer['price'] - $discount);
    $otherTaxable = (int) round($otherOffer['price']);
    $total = $chosenTaxable + $otherTaxable + Bill::gstOn($chosenTaxable) + Bill::gstOn($otherTaxable);

    Coupon::query()->create([
        'code' => 'LAUNCH10',
        'type' => 'percent',
        'amount' => 10,
        'is_active' => true,
    ])->services()->attach($service->id);

    $this->post(route('cart.store', $service), ['months' => 1]);
    $this->post(route('cart.store', $other), ['months' => 1]);

    $this->post(route('cart.coupon'), ['code' => 'launch10'])
        ->assertRedirect(route('cart.index'));

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Coupon LAUNCH10 applied.')
        ->assertSee('Discount (LAUNCH10)')
        ->assertSee('₹'.number_format($discount, 0, '.', ','))
        ->assertSee('₹'.number_format($total, 0, '.', ','));
});

test('a coupon is rejected when the cart has none of its services', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $other = Service::query()->where('slug', 'high-conversion-google-ads')->firstOrFail();

    Coupon::query()->create([
        'code' => 'LAUNCH10',
        'type' => 'percent',
        'amount' => 10,
        'is_active' => true,
    ])->services()->attach($service->id);

    $this->from(route('cart.index'))
        ->post(route('cart.store', $other), ['months' => 1]);

    $this->post(route('cart.coupon'), ['code' => 'LAUNCH10'])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHasErrors('code');

    $this->get(route('cart.index'))
        ->assertDontSee('Discount (LAUNCH10)');
});

test('a hidden coupon cannot be used', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    Coupon::query()->create([
        'code' => 'LAUNCH10',
        'type' => 'percent',
        'amount' => 10,
        'is_active' => false,
    ])->services()->attach($service->id);

    $this->post(route('cart.store', $service), ['months' => 1]);

    $this->post(route('cart.coupon'), ['code' => 'LAUNCH10'])
        ->assertSessionHasErrors('code');
});

test('checkout keeps the coupon discount on the order', function () {
    $this->actingAs(User::factory()->create());

    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $offer = $service->offer(1);
    $discount = (float) round($offer['price'] * 0.10);

    Coupon::query()->create([
        'code' => 'LAUNCH10',
        'type' => 'percent',
        'amount' => 10,
        'is_active' => true,
    ])->services()->attach($service->id);

    $this->post(route('cart.store', $service), ['months' => 1]);
    $this->post(route('cart.coupon'), ['code' => 'LAUNCH10']);
    payForCheckout()->assertRedirect();

    $order = Order::query()->firstOrFail();
    $taxable = (int) round($offer['price'] - $discount);
    $gst = Bill::gstOn($taxable);

    expect($order->coupon_code)->toBe('LAUNCH10')
        ->and((float) $order->discount)->toBe($discount)
        ->and((float) $order->gst)->toBe((float) $gst)
        ->and((float) $order->total)->toBe((float) ($taxable + $gst))
        ->and((float) $order->items()->firstOrFail()->gst)->toBe((float) $gst);

    $this->get(route('orders.show', $order))
        ->assertSee('Discount (LAUNCH10)')
        ->assertSee('GST (18%)')
        ->assertSee('₹'.number_format($gst, 0, '.', ','))
        ->assertSee('₹'.number_format($taxable + $gst, 0, '.', ','));
});

test('gst is added on each service and a coupon only lowers the chosen one', function () {
    $this->actingAs(User::factory()->create());

    $chosen = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $other = Service::query()->where('slug', 'high-conversion-google-ads')->firstOrFail();
    $chosenOffer = $chosen->offer(1);
    $otherOffer = $other->offer(1);

    Coupon::query()->create([
        'code' => 'LAUNCH10',
        'type' => 'percent',
        'amount' => 10,
        'per_user_limit' => 1,
        'usage_limit' => 5,
        'is_active' => true,
    ])->services()->attach($chosen->id);

    $this->post(route('cart.store', $chosen), ['months' => 1]);
    $this->post(route('cart.store', $other), ['months' => 1]);
    $this->post(route('cart.coupon'), ['code' => 'LAUNCH10']);

    $chosenTaxable = (int) round($chosenOffer['price'] - round($chosenOffer['price'] * 0.10));
    $otherTaxable = (int) round($otherOffer['price']);
    $gst = Bill::gstOn($chosenTaxable) + Bill::gstOn($otherTaxable);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('GST (18%)')
        ->assertSee('₹'.number_format($gst, 0, '.', ','))
        ->assertSee('₹'.number_format($chosenTaxable + $otherTaxable + $gst, 0, '.', ','));

    payForCheckout();

    $orders = Order::query()->get();
    $chosenItem = $orders->flatMap->items->firstWhere('service_id', $chosen->id);
    $otherItem = $orders->flatMap->items->firstWhere('service_id', $other->id);

    expect($orders)->toHaveCount(2)
        ->and($chosenItem)->not->toBeNull()
        ->and($otherItem)->not->toBeNull()
        ->and($orders->sum(fn ($order) => (float) $order->gst))->toBe((float) $gst)
        ->and($orders->sum(fn ($order) => (float) $order->total))->toBe((float) ($chosenTaxable + $otherTaxable + $gst))
        ->and((float) $chosenItem->discount)->toBe((float) round($chosenOffer['price'] * 0.10))
        ->and((float) $otherItem->discount)->toBe(0.0)
        ->and((float) $otherItem->gst)->toBe((float) Bill::gstOn($otherTaxable));
});

test('a coupon needs a code and at least one service', function () {
    $this->actingAs(Admin::factory()->create(), 'admin')
        ->post(route('admin.coupons.store'), [
            'code' => '',
            'type' => 'percent',
            'amount' => 10,
            'services' => [],
        ])
        ->assertSessionHasErrors(['code', 'services', 'per_user_limit', 'usage_limit']);
});

test('a customer cannot use a coupon more times than allowed', function () {
    $this->actingAs(User::factory()->create());

    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    Coupon::query()->create([
        'code' => 'ONCE',
        'type' => 'percent',
        'amount' => 10,
        'per_user_limit' => 1,
        'usage_limit' => 5,
        'is_active' => true,
    ])->services()->attach($service->id);

    $this->post(route('cart.store', $service), ['months' => 1]);
    $this->post(route('cart.coupon'), ['code' => 'ONCE']);
    payForCheckout()->assertRedirect();

    expect(Order::query()->count())->toBe(1);

    $this->post(route('cart.store', $service), ['months' => 1]);
    $this->post(route('cart.coupon'), ['code' => 'ONCE'])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHasErrors('code');

    session(['coupon' => 'ONCE']);

    $this->from(route('checkout.create'))
        ->post(route('checkout.store'), [
            ...checkoutCustomer(),
        ])
        ->assertRedirect(route('checkout.create'))
        ->assertSessionHasErrors('email');

    expect(Order::query()->count())->toBe(1);
});

test('a coupon stops after its total number of uses', function () {
    $this->actingAs(User::factory()->create());

    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    Coupon::query()->create([
        'code' => 'TEN',
        'type' => 'percent',
        'amount' => 10,
        'per_user_limit' => 5,
        'usage_limit' => 1,
        'is_active' => true,
    ])->services()->attach($service->id);

    $this->post(route('cart.store', $service), ['months' => 1]);
    $this->post(route('cart.coupon'), ['code' => 'TEN']);
    payForCheckout()->assertRedirect();

    $this->post(route('cart.store', $service), ['months' => 1]);
    $this->post(route('cart.coupon'), ['code' => 'TEN'])
        ->assertRedirect(route('cart.index'))
        ->assertSessionHasErrors('code');

    expect(Order::query()->where('coupon_code', 'TEN')->count())->toBe(1);
});

test('a guest cannot open admin coupons', function () {
    $this->get(route('admin.coupons.index'))
        ->assertRedirect(route('admin.login'));
});
