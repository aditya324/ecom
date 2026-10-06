<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('forgot password sends a reset link without revealing unknown emails', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', [
        'email' => $user->email,
    ])->assertRedirect('/forgot-password')
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);

    $this->post('/forgot-password', [
        'email' => 'missing@sunrise.test',
    ])->assertRedirect('/forgot-password')
        ->assertSessionHas('status');

    Notification::assertSentTimes(ResetPassword::class, 1);
});

test('a reset link lets the user choose a new password', function () {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect('/login');

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/login');

    $this->assertGuest();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'new-password',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

test('an invalid reset link is rejected', function () {
    $user = User::factory()->create();

    $this->post('/reset-password', [
        'token' => 'not-a-real-token',
        'email' => $user->email,
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ])->assertRedirect(route('password.reset', [
        'token' => 'not-a-real-token',
        'email' => $user->email,
    ]));

    $this->assertGuest();
});
