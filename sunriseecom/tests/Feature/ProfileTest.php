<?php

use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a guest is sent to the login page', function () {
    $this->get(route('profile.edit'))
        ->assertRedirect(route('login'));

    $this->get(route('profile.orders'))
        ->assertRedirect(route('login'));
});

test('a customer can update their name and email', function () {
    $user = User::factory()->create([
        'name' => 'Asha Menon',
        'email' => 'asha@example.com',
    ]);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'name' => 'Asha K',
            'email' => 'asha.k@example.com',
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status');

    expect($user->fresh()->name)->toBe('Asha K')
        ->and($user->fresh()->email)->toBe('asha.k@example.com');
});

test('a customer cannot take an email that is already used', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'asha@example.com']);

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->put(route('profile.update'), [
            'name' => 'Asha Menon',
            'email' => 'taken@example.com',
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrors('email');
});

test('a customer can change their password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('profile.edit'));

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

test('a wrong current password is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('profile.edit'))
        ->put(route('profile.password'), [
            'current_password' => 'nope',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHasErrors('current_password');
});

test('a customer sees only their own orders', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $service = Service::query()->where('slug', 'instagram-marketing')->firstOrFail();

    $mine = Order::query()->create([
        'number' => 'SR-MINE01',
        'user_id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'status' => 'placed',
        'total' => 1000,
    ]);
    $mine->items()->create([
        'service_id' => $service->id,
        'service_name' => $service->name,
        'duration_label' => '1 Month',
        'months' => 1,
        'price' => 1000,
    ]);

    Order::query()->create([
        'number' => 'SR-OTHER',
        'user_id' => $other->id,
        'name' => $other->name,
        'email' => $other->email,
        'status' => 'placed',
        'total' => 500,
    ]);

    $this->actingAs($user)
        ->get(route('profile.orders'))
        ->assertOk()
        ->assertSee('SR-MINE01')
        ->assertSee('Instagram Marketing')
        ->assertDontSee('SR-OTHER');
});
