<?php

use App\Models\Admin;
use App\Models\Plan;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a guest cannot open the package catalog', function () {
    $this->get('/admin/packages')->assertRedirect('/admin/login');
});

test('a customer cannot open the package catalog', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->get('/admin/packages')->assertRedirect('/admin/login');
});

test('an admin can add a package and hide it from the store', function () {
    $admin = Admin::factory()->create();
    $service = Service::query()->where('slug', 'ui-ux-design')->firstOrFail();

    $this->actingAs($admin, 'admin')->post('/admin/packages', [
        'name' => 'Harbor Bundle',
        'monthly_price' => '30000',
        'yearly_price' => '300000',
        'services' => [$service->id],
        'is_active' => '1',
    ])->assertRedirect('/admin/packages')->assertSessionHasNoErrors();

    $package = Plan::query()->where('slug', 'harbor-bundle')->firstOrFail();

    expect($package->items)->toHaveCount(1)
        ->and($package->monthly_price)->toBe('30000.00')
        ->and($package->yearly_price)->toBe('300000.00');

    $this->get('/')->assertOk()->assertSee('Harbor Bundle');

    $this->actingAs($admin, 'admin')
        ->post(route('admin.packages.hide', $package))
        ->assertRedirect('/admin/packages');

    expect($package->fresh()->is_active)->toBeFalse();
    $this->get('/')->assertDontSee('Harbor Bundle');
});

test('an admin can edit a package price and its services', function () {
    $admin = Admin::factory()->create();
    $package = Plan::query()->where('slug', 'mini')->firstOrFail();
    $service = Service::query()->where('slug', 'marketing')->firstOrFail();

    $this->actingAs($admin, 'admin')->put(route('admin.packages.update', $package), [
        'name' => 'Mini',
        'monthly_price' => '25000',
        'yearly_price' => '250000',
        'services' => [$service->id],
        'is_active' => '1',
    ])->assertRedirect('/admin/packages')->assertSessionHasNoErrors();

    $package->refresh()->load('items.service');

    expect($package->monthly_price)->toBe('25000.00')
        ->and($package->items)->toHaveCount(1)
        ->and($package->items->first()->service->slug)->toBe('marketing');

    $this->get('/')
        ->assertOk()
        ->assertSee('data-monthly="25000"', false)
        ->assertSee('data-yearly="20833"', false);
});

test('a package needs a name and a service', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->post('/admin/packages', ['name' => ''])
        ->assertSessionHasErrors(['name', 'services']);
});

test('a guest cannot delete a package', function () {
    $package = Plan::query()->firstOrFail();

    $this->delete(route('admin.packages.destroy', $package))->assertRedirect('/admin/login');
});
