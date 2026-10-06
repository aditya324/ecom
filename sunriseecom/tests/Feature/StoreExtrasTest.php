<?php

use App\Mail\InvoiceMail;
use App\Models\Admin;
use App\Models\Business;
use App\Models\Category;
use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('the header menu holds wishlist cart and account', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Menu', false)
        ->assertSee(route('wishlist.index'), false)
        ->assertSee(route('cart.index'), false)
        ->assertSee(route('login'), false)
        ->assertDontSee(route('profile.edit'), false);

    $user = User::factory()->create(['name' => 'Asha Rao']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Asha Rao')
        ->assertSee('Profile')
        ->assertSee('Orders')
        ->assertSee('Log out')
        ->assertSee(route('profile.edit'), false)
        ->assertSee(route('profile.orders'), false);
});

test('the footer pages and home links are real', function () {
    $this->get(route('about'))->assertOk()->assertSee('About Us');
    $this->get(route('privacy'))->assertOk()->assertSee('Privacy Policy');
    $this->get(route('refund'))->assertOk()->assertSee('Refund Policy');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('categories.index'), false)
        ->assertSee(route('home').'#packages', false)
        ->assertSee(asset('assets/logo/favicon.png'), false)
        ->assertSee('https://sunrisedigital.co.in/about.php', false)
        ->assertSee('https://sunrisedigital.co.in/career.php', false)
        ->assertSee('https://sunrisedigital.co.in/contact-us.php', false)
        ->assertSee('https://x.com/SdmBangalore', false)
        ->assertDontSee(route('press'), false)
        ->assertDontSee('href="#"', false);
});

test('search includes a package name', function () {
    $this->get(route('search', ['q' => 'Mini']))
        ->assertOk()
        ->assertSee('Package')
        ->assertSee(route('packages.show', 'mini'), false);

    $this->getJson(route('search', ['q' => 'Mini']))
        ->assertOk()
        ->assertJsonFragment([
            'name' => 'Mini',
            'category' => 'Package',
        ]);
});

test('a buyer can leave one review on a service they bought', function () {
    $user = User::factory()->create();
    $service = Service::query()->where('slug', 'comprehensive-technical-seo')->firstOrFail();
    $order = Order::query()->create([
        'number' => 'SR-REV001',
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'status' => 'placed',
        'discount' => 0,
        'gst' => 18,
        'total' => 118,
    ]);
    $order->items()->create([
        'service_id' => $service->id,
        'service_name' => $service->name,
        'duration_label' => 'One-time',
        'price' => 100,
        'discount' => 0,
        'gst' => 18,
    ]);

    $service->update(['rating' => 0, 'review_count' => 0]);

    $this->actingAs($user)
        ->post(route('reviews.store', $service), [
            'rating' => 5,
            'body' => 'The audit was clear and useful.',
        ])
        ->assertRedirect(route('services.show', $service));

    $service->refresh();
    expect((float) $service->rating)->toBe(5.0)
        ->and($service->review_count)->toBe(1);

    $this->actingAs($user)
        ->get(route('services.show', $service))
        ->assertOk()
        ->assertSee('The audit was clear and useful.')
        ->assertSee('5.0')
        ->assertSee('5 of 5')
        ->assertSee('Verified purchase')
        ->assertSee('Update your review');

    $this->actingAs(User::factory()->create())
        ->post(route('reviews.store', $service), [
            'rating' => 1,
            'body' => 'I did not buy this.',
        ])
        ->assertForbidden();
});

test('an admin can save the gstin and a deal end', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->put(route('admin.business.update'), [
            'legal_name' => 'Sunrise Digital Agency',
            'gstin' => '29ABCDE1234F1Z5',
            'address' => "12 Market Road\nBengaluru",
            'email' => 'hello@sunrise.test',
        ])
        ->assertRedirect(route('admin.business.edit'))
        ->assertSessionHasNoErrors();

    expect(Business::current()->gstin)->toBe('29ABCDE1234F1Z5');

    $category = Category::query()->where('slug', 'catalog-social-media')->firstOrFail();

    $this->actingAs($admin, 'admin')->post('/admin/services', [
        'name' => 'Timed Deal',
        'category_id' => $category->id,
        'short_description' => 'A short offer line.',
        'points' => [
            ['title' => 'What is included', 'body' => 'The full offer for this service.'],
        ],
        'price' => '900',
        'compare_price' => '1500',
        'billing_type' => 'one-time',
        'is_listed' => '1',
        'is_active' => '1',
        'is_deal' => '1',
        'deal_ends_at' => now()->addDay()->format('Y-m-d\TH:i'),
    ])->assertRedirect('/admin/services')->assertSessionHasNoErrors();

    $service = Service::query()->where('slug', 'timed-deal')->firstOrFail();

    expect($service->deal_ends_at)->not->toBeNull()
        ->and($service->dealEndsLabel())->toStartWith('Ends in');
});

test('a placed payment emails the invoice pdf', function () {
    Mail::fake();

    $this->actingAs(User::factory()->create());
    $service = Service::query()->where('slug', 'comprehensive-technical-seo')->firstOrFail();

    $this->post(route('cart.store', $service));
    payForCheckout();

    Mail::assertSent(InvoiceMail::class, function (InvoiceMail $mail) {
        $mail->assertSeeInHtml('attached as a PDF');
        $attached = $mail->attachments()[0];
        [$pdf] = $attached->attachWith(
            fn () => null,
            fn ($data) => [$data(), ['as' => $attached->as]],
        );

        return str_starts_with($pdf, '%PDF') && str_ends_with($attached->as, '.pdf');
    });
});
