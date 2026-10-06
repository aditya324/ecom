<?php

use App\Models\Category;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a service page shows the offer and catalog cards link to it', function () {
    $service = Service::query()->where('slug', 'high-conversion-google-ads')->firstOrFail();

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee('High-Conversion Google Ads Management')
        ->assertSee('PPC Advertising')
        ->assertSee('₹45,000')
        ->assertSee('1 Mo')
        ->assertSee('6 Mo')
        ->assertSee('12 Mo')
        ->assertSee('Add to Cart')
        ->assertSee('Buy Now')
        ->assertSee('B2B Leads')
        ->assertSee('Dedicated Campaign Manager')
        ->assertSee('3 Months')
        ->assertSee('SUNRISE GUARANTEE')
        ->assertSee('Ad spend budget separate.');

    $category = Category::query()->where('slug', 'digital-marketing')->firstOrFail();

    $this->get(route('categories.show', $category))
        ->assertSee(route('services.show', $service), false);
});

test('a one-time service shows its delivery window instead of month options', function () {
    $service = Service::query()->where('slug', 'comprehensive-technical-seo')->firstOrFail();

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee('Comprehensive Technical SEO Audit')
        ->assertSee('14 Days Delivery')
        ->assertSee('Technical site review')
        ->assertDontSee('1 Mo');
});

test('an inactive service page is not available', function () {
    $service = Service::query()->where('slug', 'whatsapp-marketing')->firstOrFail();
    $service->update(['is_active' => false]);

    $this->get(route('services.show', $service))->assertNotFound();
});
