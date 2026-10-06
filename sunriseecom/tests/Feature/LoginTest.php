<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a wrong password does not log the user in', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'not-the-password',
    ])->assertRedirect('/login');

    $this->assertGuest();
});

test('a visitor can create an account and is signed in', function () {
    $this->post('/register', [
        'name' => 'Asha',
        'email' => 'new@sunrise.test',
        'password' => 'password',
    ])->assertRedirect('/');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'Asha',
        'email' => 'new@sunrise.test',
    ]);
});

test('sign up rejects an email that is already registered', function () {
    User::factory()->create(['email' => 'asha@sunrise.test']);

    $this->post('/register', [
        'name' => 'Asha',
        'email' => 'asha@sunrise.test',
        'password' => 'password',
    ])->assertRedirect('/register');

    $this->assertGuest();
});

test('a wrong password after a real login ends the session', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($user);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'not-the-password',
    ])->assertRedirect('/login');

    $this->assertGuest();
});
