<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

test('google sign-in creates an account and logs the customer in', function () {
    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-100',
        'name' => 'Meera Shah',
        'email' => 'meera.google@sunrise.test',
    ]));

    $this->get('/auth/google/callback')->assertRedirect('/');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'meera.google@sunrise.test',
        'google_id' => 'google-100',
        'name' => 'Meera Shah',
    ]);
});

test('google sign-in links an existing email account', function () {
    $user = User::factory()->create(['email' => 'asha@sunrise.test']);

    Socialite::fake('google', SocialiteUser::fake([
        'id' => 'google-200',
        'name' => 'Asha',
        'email' => 'asha@sunrise.test',
    ]));

    $this->get('/auth/google/callback')->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->google_id)->toBe('google-200');
});

test('google sign-in stays on login when it is not configured', function () {
    config([
        'services.google.client_id' => null,
        'services.google.client_secret' => null,
    ]);

    $this->get('/auth/google')->assertRedirect('/login');
});
