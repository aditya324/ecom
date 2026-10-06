<?php

use App\Models\Plan;
use App\Models\User;
use App\Support\Bill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.razorpay.key' => 'rzp_test_example',
        'services.razorpay.secret' => 'test-secret',
    ]);
});

test('get a plan starts a yearly razorpay subscription', function () {
    $plan = Plan::query()->where('slug', 'mini')->firstOrFail();
    $taxable = (int) round((float) $plan->yearly_price);
    $amount = ($taxable + Bill::gstOn($taxable)) * 100;

    $this->actingAs(User::factory()->create());

    $this->post(route('plans.subscribe', $plan), [
        'interval' => 'yearly',
    ])->assertRedirect(route('checkout.create'));

    $this->get(route('checkout.create'))
        ->assertOk()
        ->assertSee($plan->name)
        ->assertSee('renews each year')
        ->assertSee('Due today');

    Http::fake(function ($request) {
        if (str_contains($request->url(), '/plans')) {
            return Http::response(['id' => 'plan_yearly']);
        }

        return Http::response(['id' => 'sub_yearly']);
    });

    $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk()
        ->assertJsonPath('steps.0.type', 'subscription')
        ->assertJsonPath('steps.0.subscription_id', 'sub_yearly')
        ->assertJsonMissingPath('steps.0.order_id');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/plans')
        && $request['period'] === 'yearly'
        && $request['item']['amount'] === $amount);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/subscriptions')
        && $request['plan_id'] === 'plan_yearly'
        && $request['total_count'] === 5);
});

test('a guest is sent to login before starting a plan', function () {
    $plan = Plan::query()->where('slug', 'mini')->firstOrFail();

    $this->post(route('plans.subscribe', $plan), [
        'interval' => 'monthly',
    ])->assertRedirect(route('login'));

    expect(session('url.intended'))->toBe(route('checkout.create'));
});
