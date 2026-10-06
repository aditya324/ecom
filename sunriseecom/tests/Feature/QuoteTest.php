<?php

use App\Mail\QuoteReceivedMail;
use App\Mail\QuoteReplyMail;
use App\Models\Admin;
use App\Models\Business;
use App\Models\Order;
use App\Models\Quote;
use App\Models\Service;
use App\Models\User;
use App\Support\Bill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('a visitor can request a custom quote for a service', function () {
    Mail::fake();

    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee(route('quotes.create', $service), false);

    $this->get(route('quotes.create', $service))
        ->assertOk()
        ->assertSee('Request a custom quote')
        ->assertSee('Instagram Marketing');

    $this->post(route('quotes.store', $service), [
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'message' => 'We need three months of reels for a product launch.',
    ])->assertRedirect(route('services.show', $service));

    $quote = Quote::query()->where('email', 'asha@example.com')->firstOrFail();

    expect($quote->service_id)->toBe($service->id)
        ->and($quote->status)->toBe('new')
        ->and($quote->message)->toBe('We need three months of reels for a product launch.');

    Mail::assertSent(QuoteReceivedMail::class, function (QuoteReceivedMail $mail) use ($quote) {
        $mail->assertSeeInHtml(route('admin.quotes.edit', $quote));

        return $mail->hasTo('support@sunrisedigital.co.in')
            && $mail->quote->is($quote);
    });

    $this->get(route('services.show', $service))
        ->assertSee('Quote request sent. Sunrise will reply with a price.');
});

test('a signed in customer is attached to the quote', function () {
    $customer = User::factory()->create();
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $this->actingAs($customer)->post(route('quotes.store', $service), [
        'name' => $customer->name,
        'email' => $customer->email,
        'message' => 'Please price a smaller monthly plan.',
    ])->assertRedirect(route('services.show', $service));

    expect(Quote::query()->first()->user_id)->toBe($customer->id);
});

test('a quote needs a name email and message', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $this->post(route('quotes.store', $service), [])
        ->assertSessionHasErrors(['name', 'email', 'message']);
});

test('a hidden service cannot take a quote', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $service->update(['is_active' => false]);

    $this->get(route('quotes.create', $service))->assertNotFound();
});

test('an admin can reply to a quote with a price', function () {
    Mail::fake();

    $admin = Admin::factory()->create();
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $quote = Quote::query()->create([
        'service_id' => $service->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'message' => 'We need three months of reels for a product launch.',
        'status' => 'new',
    ]);

    $this->actingAs($admin, 'admin')
        ->put(route('admin.quotes.update', $quote), [
            'quoted_price' => '18000',
            'reply' => 'This covers three months of reels and a monthly report.',
        ])
        ->assertRedirect(route('admin.quotes.index'))
        ->assertSessionHasNoErrors();

    Mail::assertSent(QuoteReplyMail::class, function (QuoteReplyMail $mail) use ($quote) {
        $mail->assertSeeInHtml('Pay this quote');
        $mail->assertSeeInHtml(route('quotes.pay', $mail->quote->pay_token));

        return $mail->hasTo('asha@example.com')
            && $mail->quote->id === $quote->id
            && $mail->quote->reply === 'This covers three months of reels and a monthly report.';
    });

    $quote->refresh();

    expect($quote->status)->toBe('replied')
        ->and($quote->quoted_price)->toBe('18000.00')
        ->and($quote->reply)->toBe('This covers three months of reels and a monthly report.');

    $this->actingAs($admin, 'admin')
        ->get(route('admin.quotes.index'))
        ->assertOk()
        ->assertSee('Asha Menon')
        ->assertSee('₹18,000')
        ->assertSee('Replied');
});

test('a reply and a price are required before an email is sent', function () {
    Mail::fake();

    $admin = Admin::factory()->create();
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $quote = Quote::query()->create([
        'service_id' => $service->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'message' => 'We need three months of reels for a product launch.',
        'status' => 'new',
    ]);

    $this->actingAs($admin, 'admin')
        ->put(route('admin.quotes.update', $quote), [
            'quoted_price' => '',
            'reply' => '',
        ])
        ->assertSessionHasErrors(['quoted_price', 'reply']);

    Mail::assertNothingSent();
    expect($quote->fresh()->status)->toBe('new');
});

test('a quote is saved when the business has no email', function () {
    Mail::fake();
    Config::set('mail.support.address', null);

    Business::current()->update(['email' => null]);
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $this->post(route('quotes.store', $service), [
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'message' => 'Please price a smaller monthly plan.',
    ])->assertRedirect(route('services.show', $service));

    Mail::assertNothingSent();
    expect(Quote::query()->where('email', 'asha@example.com')->exists())->toBeTrue();
});

test('a quote link opens checkout for that price', function () {
    $user = User::factory()->create();
    $service = Service::query()->where('slug', 'comprehensive-technical-seo')->firstOrFail();
    $quote = Quote::query()->create([
        'service_id' => $service->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'message' => 'We need the audit for two sites.',
        'status' => 'replied',
        'quoted_price' => 18000,
        'reply' => 'This covers both sites.',
        'pay_token' => 'quote-pay-token',
    ]);
    $gst = Bill::gstOn(18000);

    $this->actingAs($user)->post(route('cart.store', $service));

    $this->get(route('quotes.pay', $quote->pay_token))
        ->assertRedirect(route('checkout.create'));

    $this->get(route('checkout.create'))
        ->assertOk()
        ->assertSee('Custom quote')
        ->assertSee('Comprehensive Technical SEO Audit')
        ->assertSee(number_format($gst, 2, '.', ','));

    payForCheckout()->assertRedirect(route('orders.show', Order::query()->where('quote_id', $quote->id)->firstOrFail()));

    $order = Order::query()->where('quote_id', $quote->id)->firstOrFail();

    expect($order->status)->toBe('placed')
        ->and((int) $order->total)->toBe(18000 + $gst);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/orders')
        && $request['amount'] === (18000 + $gst) * 100);

    $this->get(route('cart.index'))->assertSee('Comprehensive Technical SEO Audit');

    $this->get(route('quotes.pay', $quote->pay_token))
        ->assertRedirect(route('orders.show', $order));
});

test('a guest quote link waits at login', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $quote = Quote::query()->create([
        'service_id' => $service->id,
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
        'message' => 'Please price a smaller monthly plan.',
        'status' => 'replied',
        'quoted_price' => 9000,
        'reply' => 'A smaller plan is 9000.',
        'pay_token' => 'guest-quote-token',
    ]);

    $this->get(route('quotes.pay', $quote->pay_token))
        ->assertRedirect(route('login'));

    expect(session('url.intended'))->toBe(route('checkout.create'))
        ->and(session('checkout.source'))->toBe('quote')
        ->and(session('checkout.quote_id'))->toBe($quote->id);
});

test('an unknown quote link is not found', function () {
    $this->get(route('quotes.pay', 'missing-token'))->assertNotFound();
});

test('a guest cannot open admin quotes', function () {
    $this->get('/admin/quotes')->assertRedirect('/admin/login');
});
