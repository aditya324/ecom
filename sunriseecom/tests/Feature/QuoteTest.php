<?php

use App\Mail\QuoteReplyMail;
use App\Models\Admin;
use App\Models\Quote;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('a visitor can request a custom quote for a service', function () {
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

test('a guest cannot open admin quotes', function () {
    $this->get('/admin/quotes')->assertRedirect('/admin/login');
});
