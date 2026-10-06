<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Shop by Category');
    $response->assertSee('Digital Marketing');
    $response->assertSee('Best Sellers');
    $response->assertSee('Marketplace Best Sellers');
    $response->assertSee(route('best-sellers'), false);
    $response->assertSee('E-commerce Website Setup & Development');
    $response->assertSee('Mini');
    $response->assertSee('UI/UX Design');
    $response->assertSee('get a plan');
    $response->assertSee('Build Your Own Digital Growth Package');
    $response->assertSee('Quick Links');
    $response->assertSee('Precise Engineering for the Digital Era.');
});
