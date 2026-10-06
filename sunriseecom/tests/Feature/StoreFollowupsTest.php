<?php

use App\Mail\OrderProgressMail;
use App\Mail\RefundMail;
use App\Mail\SupportReceivedMail;
use App\Mail\SupportReplyMail;
use App\Models\Admin;
use App\Models\Business;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Review;
use App\Models\Service;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('an admin can mark an order in progress and leave a note', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();
    $order = Order::query()->create([
        'number' => 'SR-NOTE1',
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'status' => 'placed',
        'discount' => 0,
        'gst' => 18,
        'total' => 118,
    ]);

    Mail::fake();

    $this->actingAs($admin, 'admin')
        ->put(route('admin.orders.update', $order), [
            'status' => 'in_progress',
            'customer_note' => 'We start the homepage this week.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    Mail::assertSent(OrderProgressMail::class, function (OrderProgressMail $mail) use ($order, $user) {
        return $mail->order->is($order) && $mail->hasTo($user->email);
    });

    $this->actingAs($user)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('In progress')
        ->assertSee('We start the homepage this week.');
});

test('a refund emails the customer', function () {
    Mail::fake();
    Http::fake([
        'api.razorpay.com/*' => Http::response(['id' => 'rfnd_test', 'status' => 'processed']),
    ]);

    $admin = Admin::factory()->create();
    $user = User::factory()->create();
    $order = Order::query()->create([
        'number' => 'SR-REFUND1',
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'status' => 'placed',
        'razorpay_payment_id' => 'pay_refund_1',
        'discount' => 0,
        'gst' => 18,
        'total' => 118,
    ]);

    $this->actingAs($admin, 'admin')
        ->post(route('admin.orders.refund', $order), ['part' => 'once'])
        ->assertRedirect()
        ->assertSessionHas('status', 'Payment refunded.');

    Mail::assertSent(RefundMail::class, fn (RefundMail $mail) => $mail->hasTo($user->email));
});

test('an admin can hide a review and the buyer can remove it', function () {
    $user = User::factory()->create();
    $service = Service::query()->where('slug', 'comprehensive-technical-seo')->firstOrFail();
    $review = Review::query()->create([
        'user_id' => $user->id,
        'service_id' => $service->id,
        'rating' => 4,
        'body' => 'Clear and useful.',
    ]);
    $service->syncReviewStats();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->post(route('admin.reviews.hide', $review))
        ->assertRedirect();

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertDontSee('Clear and useful.');

    $this->actingAs($user)
        ->delete(route('reviews.destroy', $review))
        ->assertRedirect(route('services.show', $service));

    expect(Review::query()->find($review->id))->toBeNull();
});

test('a package has its own page and a buyer can review it', function () {
    $plan = Plan::query()->where('slug', 'mini')->firstOrFail();
    $user = User::factory()->create();
    $order = Order::query()->create([
        'number' => 'SR-PLAN1',
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'status' => 'placed',
        'discount' => 0,
        'gst' => 18,
        'total' => 118,
    ]);
    $order->items()->create([
        'plan_id' => $plan->id,
        'service_name' => $plan->name,
        'duration_label' => 'Yearly',
        'price' => 100,
        'discount' => 0,
        'gst' => 18,
    ]);

    $this->get(route('packages.show', $plan))
        ->assertOk()
        ->assertSee($plan->name);

    $this->actingAs($user)
        ->post(route('packages.reviews.store', $plan), [
            'rating' => 5,
            'body' => 'The package covered the launch.',
        ])
        ->assertRedirect(route('packages.show', $plan));

    $this->get(route('packages.show', $plan))
        ->assertOk()
        ->assertSee('The package covered the launch.');
});

test('a new support message is emailed to the business', function () {
    Mail::fake();

    $this->post(route('support.store'), [
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'body' => 'Where is order SR-1?',
    ])->assertRedirect(route('support'));

    Mail::assertSent(SupportReceivedMail::class, function (SupportReceivedMail $mail) {
        $mail->assertSeeInHtml('Where is order SR-1?');
        $mail->assertSeeInHtml(route('admin.support.show', $mail->supportMessage));

        return $mail->hasTo('support@sunrisedigital.co.in');
    });
});

test('a saved state is reused at checkout and a support message reaches admin', function () {
    $user = User::factory()->create([
        'billing_state' => 'Karnataka',
    ]);

    $service = Service::query()->where('slug', 'accelerated-website-launch')->firstOrFail();

    $this->actingAs($user)
        ->followingRedirects()
        ->post(route('checkout.buy', $service))
        ->assertOk()
        ->assertSee('Billed in Karnataka')
        ->assertDontSee('required with a GSTIN');

    $this->post(route('support.store'), [
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'body' => 'Where is order SR-1?',
    ])->assertRedirect(route('support'));

    expect(SupportMessage::query()->where('email', 'asha@example.com')->exists())->toBeTrue();

    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.support.index'))
        ->assertOk()
        ->assertSee('Where is order SR-1?');

    $message = SupportMessage::query()->where('email', 'asha@example.com')->firstOrFail();

    $this->get(route('admin.support.show', $message))
        ->assertOk()
        ->assertSee('Where is order SR-1?');

    expect($message->fresh()->status)->toBe('read');

    Mail::fake();

    $this->put(route('admin.support.update', $message), [
        'reply' => 'It is in progress and will be delivered this week.',
    ])->assertRedirect(route('admin.support.index'));

    expect($message->fresh()->status)->toBe('replied')
        ->and($message->fresh()->reply)->toBe('It is in progress and will be delivered this week.');

    Mail::assertSent(SupportReplyMail::class, fn (SupportReplyMail $mail) => $mail->hasTo('asha@example.com'));
});

test('business details and ended deals are filled in', function () {
    $business = Business::current();

    expect($business->gstin)->toBe('29FLQPS7503Q1ZU')
        ->and($business->address)->toContain('Bengaluru')
        ->and($business->email)->toBe('support@sunrisedigital.co.in')
        ->and($business->instagram)->toBe('https://www.instagram.com/sunrisedigitalofficial');

    $ended = Service::query()->where('is_deal', true)->whereNotNull('deal_ends_at')->get();

    expect($ended->every(fn (Service $service) => $service->deal_ends_at->isFuture()))->toBeTrue();
});
