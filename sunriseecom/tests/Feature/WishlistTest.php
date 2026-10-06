<?php

use App\Models\Admin;
use App\Models\Service;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a visitor can save a service and remove it', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee(route('wishlist.toggle', $service), false);

    $this->from(route('services.show', $service))
        ->post(route('wishlist.toggle', $service))
        ->assertRedirect(route('services.show', $service));

    $this->get(route('wishlist.index'))
        ->assertOk()
        ->assertSee('Instagram Marketing')
        ->assertSee('saved to your wishlist');

    $this->from(route('wishlist.index'))
        ->post(route('wishlist.toggle', $service))
        ->assertRedirect(route('wishlist.index'));

    $this->get(route('wishlist.index'))
        ->assertSee('Your wishlist is empty.');
});

test('the filter card can save a service', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $this->get(route('categories.show', $service->category))
        ->assertOk()
        ->assertSee(route('wishlist.toggle', $service), false);
});

test('an admin can see the service a signed-in customer saved', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();
    $user = User::factory()->create([
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
    ]);

    $this->actingAs($user)->post(route('wishlist.toggle', $service));

    expect(WishlistItem::query()->where('user_id', $user->id)->count())->toBe(1);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.wishlists.index'))
        ->assertOk()
        ->assertSee('Asha Menon')
        ->assertSee('asha@example.com')
        ->assertSee('Instagram Marketing');

    $this->actingAs($user)->post(route('wishlist.toggle', $service));

    expect(WishlistItem::query()->count())->toBe(0);
});

test('a guest wishlist stays off the admin list', function () {
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $this->post(route('wishlist.toggle', $service));

    expect(WishlistItem::query()->count())->toBe(0);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.wishlists.index'))
        ->assertOk()
        ->assertSee('No wishlists yet.');
});

test('a guest cannot open the admin wishlists', function () {
    $this->get(route('admin.wishlists.index'))
        ->assertRedirect(route('admin.login'));
});
