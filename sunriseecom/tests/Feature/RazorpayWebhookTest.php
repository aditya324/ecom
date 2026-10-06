<?php

use App\Mail\SubscriptionPaymentMail;
use App\Models\Order;
use App\Models\Service;
use App\Models\Subscription;
use App\Models\User;
use App\Support\Bill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.razorpay.key' => 'rzp_test_example',
        'services.razorpay.secret' => 'test-secret',
    ]);
});

function postRazorpayWebhook(string $event, array $payload): TestResponse
{
    $body = json_encode([
        'event' => $event,
        'payload' => $payload,
    ], JSON_THROW_ON_ERROR);

    return test()->call('POST', route('payments.razorpay.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X-Razorpay-Signature' => hash_hmac('sha256', $body, 'test-secret'),
    ], $body);
}

test('opening the razorpay webhook in a browser is accepted', function () {
    $this->get(route('payments.razorpay.webhook'))
        ->assertOk()
        ->assertSee('Payment events are accepted by POST.');
});

test('a subscription charge finishes the order when the browser never confirms it', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $gross = (int) round($service->offer(1)['price']);
    $amount = ($gross + Bill::gstOn($gross)) * 100;

    $this->actingAs(User::factory()->create());
    $this->post(route('cart.store', $service), ['months' => 1]);

    Http::fake([
        'api.razorpay.com/*' => Http::response(['id' => 'sub_hook', 'short_url' => 'https://rzp.io/i/hook']),
    ]);

    $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk();

    postRazorpayWebhook('subscription.charged', [
        'subscription' => ['entity' => [
            'id' => 'sub_hook',
            'status' => 'active',
            'paid_count' => 1,
            'short_url' => 'https://rzp.io/i/hook',
        ]],
        'payment' => ['entity' => [
            'id' => 'pay_hook',
            'amount' => $amount,
            'currency' => 'INR',
            'status' => 'captured',
        ]],
    ])->assertNoContent();

    $order = Order::query()->firstOrFail();

    expect($order->status)->toBe('placed')
        ->and($order->razorpay_payment_id)->toBe('pay_hook')
        ->and($order->subscriptions->first()->status)->toBe('active');

    $this->get(route('cart.index'))->assertDontSee('Instagram Marketing');
    $this->get(route('profile.orders'))
        ->assertOk()
        ->assertSee($order->number)
        ->assertSee('Instagram Marketing')
        ->assertSee('Update card');
});

test('a later subscription charge is a new order on the profile', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $gross = (int) round($service->offer(1)['price']);
    $amount = ($gross + Bill::gstOn($gross)) * 100;
    $charge = fn (string $paymentId, int $paidCount) => postRazorpayWebhook('subscription.charged', [
        'subscription' => ['entity' => [
            'id' => 'sub_renew',
            'status' => 'active',
            'paid_count' => $paidCount,
        ]],
        'payment' => ['entity' => [
            'id' => $paymentId,
            'amount' => $amount,
            'currency' => 'INR',
            'status' => 'captured',
        ]],
    ]);

    $this->actingAs(User::factory()->create());
    $this->post(route('cart.store', $service), ['months' => 1]);

    Http::fake([
        'api.razorpay.com/*' => Http::response(['id' => 'sub_renew']),
    ]);

    $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk();

    $charge('pay_first', 1)->assertNoContent();
    $charge('pay_first', 1)->assertNoContent();
    $charge('pay_second', 2)->assertNoContent();

    $renewal = Order::query()->where('razorpay_payment_id', 'pay_second')->firstOrFail();

    $subscription = Subscription::query()->firstOrFail();

    expect(Order::query()->where('status', 'placed')->count())->toBe(2)
        ->and($renewal->items->first()->duration_label)->toContain('renewal')
        ->and($renewal->subscription_id)->toBe($subscription->id)
        ->and($subscription->paid_count)->toBe(2);

    $this->get(route('profile.orders'))
        ->assertOk()
        ->assertSee($renewal->number)
        ->assertSee('renewal');
});

test('a processed refund shows on the profile', function () {
    $service = Service::query()->where('slug', 'comprehensive-technical-seo')->firstOrFail();
    $gross = (int) round($service->offer(null)['price']);
    $amount = ($gross + Bill::gstOn($gross)) * 100;

    $this->actingAs(User::factory()->create());
    $this->post(route('cart.store', $service));

    Http::fake([
        'api.razorpay.com/v1/orders' => Http::response(['id' => 'order_refund']),
    ]);

    $this->postJson(route('checkout.store'), [
        ...checkoutCustomer(),
    ])->assertOk();

    postRazorpayWebhook('payment.captured', [
        'payment' => ['entity' => [
            'id' => 'pay_refund',
            'order_id' => 'order_refund',
            'amount' => $amount,
            'currency' => 'INR',
            'status' => 'captured',
        ]],
    ])->assertNoContent();

    postRazorpayWebhook('refund.processed', [
        'refund' => ['entity' => [
            'id' => 'rfnd_test',
            'payment_id' => 'pay_refund',
            'amount' => $amount,
        ]],
        'payment' => ['entity' => [
            'id' => 'pay_refund',
            'amount' => $amount,
            'amount_refunded' => $amount,
            'currency' => 'INR',
        ]],
    ])->assertNoContent();

    $order = Order::query()->firstOrFail();

    expect($order->status)->toBe('refunded')
        ->and((float) $order->refunded_amount)->toBe((float) ($amount / 100));

    $this->get(route('profile.orders'))->assertSee('Refunded');
    $this->get(route('orders.show', $order))->assertOk()->assertSee('Refunded');
});

test('a failed renewal is shown and the customer can cancel it', function () {
    Mail::fake();

    $user = User::factory()->create();
    $order = Order::query()->create([
        'number' => 'SR-HALT01',
        'user_id' => $user->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'status' => 'placed',
        'discount' => 0,
        'gst' => 180,
        'total' => 1180,
    ]);
    $subscription = Subscription::query()->create([
        'user_id' => $user->id,
        'order_id' => $order->id,
        'name' => 'Instagram Marketing',
        'period' => 'monthly',
        'total_count' => 3,
        'paid_count' => 1,
        'cycle_amount' => 1180,
        'status' => 'active',
        'razorpay_subscription_id' => 'sub_halt',
        'razorpay_payment_id' => 'pay_halt',
        'short_url' => 'https://rzp.io/i/halt',
    ]);

    postRazorpayWebhook('subscription.pending', [
        'subscription' => ['entity' => [
            'id' => 'sub_halt',
            'status' => 'pending',
            'short_url' => 'https://rzp.io/i/halt',
        ]],
    ])->assertNoContent();

    $this->actingAs($user)
        ->get(route('profile.orders'))
        ->assertSee('Payment failed')
        ->assertSee('https://rzp.io/i/halt', false);

    Mail::assertSent(SubscriptionPaymentMail::class, function (SubscriptionPaymentMail $mail) {
        $mail->assertSeeInHtml('https://rzp.io/i/halt');
        $mail->assertSeeInHtml('did not go through');

        return $mail->hasTo('asha@example.com')
            && $mail->envelope()->subject === 'Payment failed for Instagram Marketing';
    });

    postRazorpayWebhook('subscription.pending', [
        'subscription' => ['entity' => [
            'id' => 'sub_halt',
            'status' => 'pending',
            'short_url' => 'https://rzp.io/i/halt',
        ]],
    ])->assertNoContent();

    Mail::assertSent(SubscriptionPaymentMail::class, 1);

    postRazorpayWebhook('subscription.halted', [
        'subscription' => ['entity' => ['id' => 'sub_halt', 'status' => 'halted']],
    ])->assertNoContent();

    expect($subscription->fresh()->status)->toBe('halted');

    Mail::assertSent(SubscriptionPaymentMail::class, function (SubscriptionPaymentMail $mail) {
        if ($mail->subscription->status !== 'halted') {
            return false;
        }

        $mail->assertSeeInHtml('https://rzp.io/i/halt');
        $mail->assertSeeInHtml('stopped further charges');

        return $mail->hasTo('asha@example.com')
            && $mail->envelope()->subject === 'Renewals stopped for Instagram Marketing';
    });
    Mail::assertSent(SubscriptionPaymentMail::class, 2);

    Http::fake([
        'api.razorpay.com/*' => Http::response(['status' => 'cancelled']),
    ]);

    $this->actingAs($user)
        ->post(route('subscriptions.cancel', $subscription))
        ->assertRedirect()
        ->assertSessionHas('status', 'Subscription cancelled.');

    expect($subscription->fresh()->status)->toBe('cancelled');

    $this->get(route('profile.orders'))->assertDontSee('Cancel subscription');
});
