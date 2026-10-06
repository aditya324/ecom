<?php

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('search lists a matching listed service and skips a hidden one', function () {
    $hidden = Service::query()->where('slug', 'whatsapp-marketing')->firstOrFail();
    $hidden->update(['is_active' => false]);

    $this->get(route('search', ['q' => 'instagram']))
        ->assertOk()
        ->assertSee('Instagram Marketing')
        ->assertSee(route('services.show', 'instagram-marketing'), false)
        ->assertDontSee('WhatsApp Marketing');

    $this->getJson(route('search', ['q' => 'instagram']))
        ->assertOk()
        ->assertJsonPath('services.0.name', 'Instagram Marketing')
        ->assertJsonPath('services.0.url', route('services.show', 'instagram-marketing'));

    $this->get(route('search', ['q' => 'Mini']))
        ->assertOk()
        ->assertSee(route('packages.show', 'mini'), false);
});

test('search ignores a service that is not in the catalog', function () {
    Service::query()->where('slug', 'instagram-marketing')->update(['is_listed' => false]);

    $this->get(route('search', ['q' => 'instagram']))
        ->assertOk()
        ->assertDontSee('Instagram Marketing');
});

test('an empty search asks for a service name', function () {
    $this->get(route('search'))
        ->assertOk()
        ->assertSee('Type a service name in the header.');
});
