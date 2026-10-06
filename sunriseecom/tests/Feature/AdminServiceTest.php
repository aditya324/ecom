<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('a guest cannot open the service catalog', function () {
    $this->get('/admin/services')->assertRedirect('/admin/login');
});

test('a customer cannot open the service catalog', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->get('/admin/services')->assertRedirect('/admin/login');
});

test('an admin can add a service and hide it from the store', function () {
    $admin = Admin::factory()->create();
    $category = Category::query()->where('slug', 'catalog-social-media')->firstOrFail();

    $this->actingAs($admin, 'admin')->post('/admin/services', [
        'name' => 'Test Service',
        'category_id' => $category->id,
        'short_description' => 'A short offer line.',
        'points' => [
            ['title' => 'What is included', 'body' => 'The full offer for this service.'],
            ['title' => 'A clear handover', 'body' => 'Your team gets the files and a short guide.'],
        ],
        'price' => '4500',
        'billing_type' => 'one-time',
        'delivery_label' => '7 Days Delivery',
        'is_listed' => '1',
        'is_active' => '1',
    ])->assertRedirect('/admin/services')->assertSessionHasNoErrors();

    $service = Service::query()->where('slug', 'test-service')->firstOrFail();

    expect($service->category_id)->toBe($category->id);

    $this->get(route('categories.show', $category))->assertOk()->assertSee('Test Service');

    $this->actingAs($admin, 'admin')
        ->post(route('admin.services.hide', $service))
        ->assertRedirect('/admin/services');

    expect($service->fresh()->is_active)->toBeFalse();
    $this->get(route('services.show', 'test-service'))->assertNotFound();
    $this->get(route('categories.show', $category))->assertDontSee('Test Service');
});

test('an admin can upload a service image and delete the service', function () {
    $admin = Admin::factory()->create();
    $category = Category::query()->where('slug', 'development')->firstOrFail();

    $this->actingAs($admin, 'admin')->post('/admin/services', [
        'name' => 'Harbor Audit',
        'category_id' => $category->id,
        'short_description' => 'A short offer line.',
        'points' => [
            ['title' => 'What is included', 'body' => 'The full offer for this service.'],
        ],
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'price' => '1200',
        'billing_type' => 'monthly',
        'is_listed' => '1',
        'is_active' => '1',
        'image' => UploadedFile::fake()->image('harbor.jpg', 80, 40),
    ])->assertRedirect('/admin/services');

    $service = Service::query()->where('slug', 'harbor-audit')->firstOrFail();
    $path = public_path('assets/services/'.$service->image);

    expect($service->image)->toStartWith('harbor-audit-')
        ->and($service->video_url)->toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ')
        ->and($service->reasons())->toBe([
            ['title' => 'What is included', 'body' => 'The full offer for this service.'],
        ])
        ->and(File::exists($path))->toBeTrue();

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee('assets/services/'.$service->image, false)
        ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
        ->assertSee('What is included:')
        ->assertSee('The full offer for this service.');

    $this->actingAs($admin, 'admin')
        ->delete(route('admin.services.destroy', $service))
        ->assertRedirect('/admin/services');

    expect(Service::query()->where('slug', 'harbor-audit')->exists())->toBeFalse()
        ->and(File::exists($path))->toBeFalse();
});

test('an admin can set the price for each monthly plan', function () {
    $admin = Admin::factory()->create();
    $category = Category::query()->where('slug', 'development')->firstOrFail();

    $this->actingAs($admin, 'admin')->post('/admin/services', [
        'name' => 'Plan Priced Service',
        'category_id' => $category->id,
        'short_description' => 'A short offer line.',
        'points' => [
            ['title' => 'Included', 'body' => 'The work for this plan.'],
        ],
        'price' => '10000',
        'billing_type' => 'monthly',
        'is_listed' => '1',
        'is_active' => '1',
        'duration_prices' => [
            3 => '25000',
            6 => '45000',
            12 => '80000',
        ],
    ])->assertRedirect('/admin/services')->assertSessionHasNoErrors();

    $service = Service::query()->where('slug', 'plan-priced-service')->firstOrFail();

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee('₹10,000')
        ->assertSee('data-price="25000"', false)
        ->assertSee('data-price="45000"', false)
        ->assertSee('data-price="80000"', false);
});

test('a service video must be a youtube link', function () {
    $admin = Admin::factory()->create();
    $category = Category::query()->where('slug', 'development')->firstOrFail();

    $this->actingAs($admin, 'admin')->post('/admin/services', [
        'name' => 'Bad Video',
        'category_id' => $category->id,
        'short_description' => 'A short offer line.',
        'points' => [
            ['title' => 'One point', 'body' => 'A short reason.'],
        ],
        'price' => '100',
        'video_url' => 'https://vimeo.com/123',
    ])->assertSessionHasErrors('video_url');
});

test('a service name is required', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->post('/admin/services', ['name' => ''])
        ->assertSessionHasErrors('name');
});

test('an admin can edit a service and the store shows the new price', function () {
    $admin = Admin::factory()->create();
    $category = Category::query()->where('slug', 'development')->firstOrFail();
    $service = Service::query()->where('slug', 'comprehensive-technical-seo')->firstOrFail();

    $this->actingAs($admin, 'admin')->put(route('admin.services.update', $service), [
        'name' => 'Comprehensive Technical SEO',
        'category_id' => $category->id,
        'short_description' => 'A revised short offer line.',
        'points' => [
            ['title' => 'What is included', 'body' => 'The revised offer for this service.'],
        ],
        'price' => '18000',
        'compare_price' => '24000',
        'billing_type' => 'one-time',
        'is_listed' => '1',
        'is_active' => '1',
    ])->assertRedirect('/admin/services')->assertSessionHasNoErrors();

    $service->refresh();

    expect($service->price)->toBe('18000.00')
        ->and($service->compare_price)->toBe('24000.00')
        ->and($service->short_description)->toBe('A revised short offer line.');

    $this->get(route('services.show', $service))
        ->assertOk()
        ->assertSee('₹18,000')
        ->assertSee('₹24,000')
        ->assertSee('The revised offer for this service.');
});

test('an admin can filter the service list', function () {
    $admin = Admin::factory()->create();
    $social = Category::query()->where('slug', 'catalog-social-media')->firstOrFail();
    $development = Category::query()->where('slug', 'development')->firstOrFail();

    Service::query()->create([
        'category_id' => $social->id,
        'name' => 'Filter Instagram',
        'slug' => 'filter-instagram',
        'short_description' => 'A monthly social offer.',
        'description' => 'A monthly social offer.',
        'price' => 8000,
        'billing_type' => 'monthly',
        'is_best_seller' => true,
        'is_listed' => true,
        'is_active' => true,
    ]);
    Service::query()->create([
        'category_id' => $development->id,
        'name' => 'Filter Website',
        'slug' => 'filter-website',
        'short_description' => 'A one-time build.',
        'description' => 'A one-time build.',
        'price' => 2000,
        'billing_type' => 'one-time',
        'is_deal' => true,
        'is_listed' => false,
        'is_active' => false,
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.services.index', [
            'billing' => 'monthly',
            'category' => $social->id,
        ]))
        ->assertOk()
        ->assertSee('Filter Instagram')
        ->assertDontSee('Filter Website');

    $this->get(route('admin.services.index', [
        'q' => 'Filter Website',
        'visibility' => 'hidden',
        'listed' => 'unlisted',
        'highlight' => 'deal',
        'max' => 2500,
    ]))
        ->assertOk()
        ->assertSee('Filter Website')
        ->assertDontSee('Filter Instagram');

    $this->get(route('admin.services.index', [
        'min' => 5000,
        'highlight' => 'best-seller',
        'sort' => 'price_desc',
    ]))
        ->assertOk()
        ->assertSee('Filter Instagram')
        ->assertDontSee('Filter Website');
});

test('a guest cannot delete a service', function () {
    $service = Service::query()->firstOrFail();

    $this->delete(route('admin.services.destroy', $service))->assertRedirect('/admin/login');
});
