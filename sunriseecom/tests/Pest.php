<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Start a Razorpay checkout and confirm it with a valid test signature.
 */
/**
 * @param  array<string, string>  $overrides
 * @return array<string, string>
 */
function checkoutCustomer(array $overrides = []): array
{
    return array_merge([
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'billing_address' => '12 Market Road',
        'billing_city' => 'Bengaluru',
        'billing_state' => 'Karnataka',
        'billing_pin' => '560001',
    ], $overrides);
}

function payForCheckout(array $customer = []): TestResponse
{
    config([
        'services.razorpay.key' => 'rzp_test_example',
        'services.razorpay.secret' => 'test-secret',
    ]);

    Http::fake(function () {
        return Http::response([
            'id' => 'payref_'.strtolower(Str::random(14)),
            'currency' => 'INR',
        ]);
    });

    $started = test()->postJson(route('checkout.store'), checkoutCustomer($customer))->assertOk();

    $payload = [];
    $subscriptions = [];

    foreach ($started->json('steps') as $step) {
        $paymentId = 'pay_'.strtolower(Str::random(14));

        if ($step['type'] === 'subscription') {
            $subscriptions[] = [
                'razorpay_subscription_id' => $step['subscription_id'],
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => hash_hmac('sha256', $paymentId.'|'.$step['subscription_id'], 'test-secret'),
            ];

            continue;
        }

        $payload['razorpay_order_id'] = $step['order_id'];
        $payload['razorpay_payment_id'] = $paymentId;
        $payload['razorpay_signature'] = hash_hmac('sha256', $step['order_id'].'|'.$paymentId, 'test-secret');
    }

    if ($subscriptions !== []) {
        $payload['subscriptions'] = $subscriptions;
    }

    return test()->post(route('checkout.payment'), $payload);
}
