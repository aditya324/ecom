<?php

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the home page shows three deals and the sale price', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee("Today's Deals", false)
        ->assertSee('Social Media Starter Package')
        ->assertSee('Accelerated Website Launch')
        ->assertSee('Comprehensive SEO Audit')
        ->assertSee('Save 40%')
        ->assertSee('Ends in');
});

test('see all deals lists every deal including ones past the home strip', function () {
    $source = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $extra = $source->replicate();
    $extra->fill([
        'name' => 'Extra Deal Service',
        'slug' => 'extra-deal-service',
        'is_deal' => true,
        'is_active' => true,
        'sort_order' => 99,
        'price' => 100,
        'compare_price' => 200,
    ]);
    $extra->save();

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Extra Deal Service');

    $this->get(route('deals'))
        ->assertOk()
        ->assertSee("Today's Deals", false)
        ->assertSee('Extra Deal Service')
        ->assertSee('Save 50%')
        ->assertSee('Social Media Starter Package')
        ->assertSee(route('cart.store', $extra), false);
});
