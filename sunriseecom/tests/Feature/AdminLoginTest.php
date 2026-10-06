<?php

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an admin who signs in on the store login is sent to the dashboard', function () {
    $admin = Admin::factory()->create();

    $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect('/admin');

    $this->assertAuthenticatedAs($admin, 'admin');
    $this->assertGuest();

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Signed in as '.$admin->name);
});

test('a guest who opens the admin area is sent to the admin login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('a customer account cannot sign in through the admin login', function () {
    $customer = User::factory()->create();

    $this->post('/admin/login', [
        'email' => $customer->email,
        'password' => 'password',
    ])->assertRedirect('/admin/login');

    $this->assertGuest();
    $this->assertGuest('admin');
});

test('an admin can sign in and sign out without a customer session', function () {
    $admin = Admin::factory()->create();

    $this->post('/admin/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect('/admin');

    $this->assertAuthenticatedAs($admin, 'admin');
    $this->assertGuest();

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Signed in as '.$admin->name);

    $this->post('/admin/logout')->assertRedirect('/admin/login');

    $this->assertGuest('admin');
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('a signed-in customer cannot open the admin area', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)
        ->get('/admin')
        ->assertRedirect('/admin/login');

    $this->assertAuthenticatedAs($customer);
});
