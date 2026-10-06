<?php

use App\Models\Category;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('view all opens the filter page with every listed service', function () {
    $services = Service::query()
        ->where('is_listed', true)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->orderBy('id')
        ->get();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('categories.index'), false);

    $pages = max(1, (int) ceil($services->count() / 9));
    $html = '';

    for ($page = 1; $page <= $pages; $page++) {
        $html .= $this->get(route('categories.index', ['page' => $page]))
            ->assertOk()
            ->assertSee('All Services')
            ->assertSee('Filters')
            ->assertSee(number_format($services->count()).' Services Available')
            ->getContent();
    }

    foreach ($services as $service) {
        expect($html)->toContain(e($service->name));
    }

    $this->get(route('categories.index', ['categories' => ['development']]))
        ->assertOk()
        ->assertSee('E-commerce Website Setup & Development')
        ->assertDontSee('Instagram Marketing');
});

test('a category page lists its services and filters them', function () {
    $category = Category::query()->where('slug', 'digital-marketing')->firstOrFail();

    $this->get(route('categories.show', $category))
        ->assertOk()
        ->assertSee('Digital Marketing Services')
        ->assertSee('Elevate your brand with industrial-scale marketing solutions.')
        ->assertSee('Social Media Marketing')
        ->assertSee('Instagram Marketing')
        ->assertSee('WhatsApp Marketing')
        ->assertSee('High-Conversion Google Ads Management')
        ->assertSee('Filters');

    $this->get(route('categories.show', ['category' => $category, 'categories' => ['catalog-social-media'], 'rating' => '4.5']))
        ->assertOk()
        ->assertSee('Instagram Marketing')
        ->assertDontSee('High-Conversion Google Ads Management')
        ->assertDontSee('WhatsApp Marketing');

    $this->get(route('categories.show', 'catalog-social-media'))
        ->assertOk()
        ->assertSee('Social Media Marketing Services')
        ->assertSee('Instagram Marketing')
        ->assertSee('WhatsApp Marketing')
        ->assertDontSee('High-Conversion Google Ads Management');
});

test('the category filter lists every category in a view more section', function () {
    $category = Category::query()->where('slug', 'digital-marketing')->firstOrFail();

    $this->get(route('categories.show', $category))
        ->assertOk()
        ->assertSee('View more')
        ->assertSee('Development')
        ->assertSee('Branding')
        ->assertSee('Social Media Marketing');

    $this->get(route('categories.show', ['category' => $category, 'categories' => ['development']]))
        ->assertOk()
        ->assertSee('E-commerce Website Setup & Development')
        ->assertDontSee('Instagram Marketing');
});

test('a new child category shows in the parent filter and filters its services', function () {
    $parent = Category::query()->where('slug', 'digital-marketing')->firstOrFail();

    $child = Category::query()->create([
        'parent_id' => $parent->id,
        'name' => 'Test Cat',
        'slug' => 'test-cat',
        'is_active' => true,
        'show_in_nav' => false,
        'show_on_home' => false,
        'sort_order' => 0,
    ]);

    Service::query()->create([
        'category_id' => $child->id,
        'name' => 'Test Service',
        'slug' => 'test-service',
        'short_description' => 'A service filed under the new category.',
        'description' => 'A service filed under the new category.',
        'price' => 5000,
        'billing_type' => 'one-time',
        'delivery_label' => '7 Days Delivery',
        'rating' => 4.6,
        'review_count' => 4,
        'is_listed' => true,
        'is_active' => true,
        'image' => 'catalog-social.jpg',
        'sort_order' => 1,
    ]);

    $this->get(route('categories.show', $parent))
        ->assertOk()
        ->assertSee('Test Cat')
        ->assertSee('Test Service');

    $this->get(route('categories.show', ['category' => $parent, 'categories' => ['test-cat']]))
        ->assertOk()
        ->assertSee('Test Service')
        ->assertDontSee('Instagram Marketing');
});

test('a category page paginates services and keeps the active filters', function () {
    $child = Category::query()->where('slug', 'catalog-social-media')->firstOrFail();

    foreach (range(1, 10) as $number) {
        Service::query()->create([
            'category_id' => $child->id,
            'name' => $number === 10 ? 'Paged Social Tail' : sprintf('Paged Social %02d', $number),
            'slug' => 'paged-social-'.$number,
            'short_description' => 'Extra catalog service for pagination.',
            'description' => 'Extra catalog service for pagination.',
            'price' => 1000 + $number,
            'billing_type' => 'one-time',
            'delivery_label' => '7 Days Delivery',
            'rating' => 4.2,
            'review_count' => 10,
            'is_listed' => true,
            'is_active' => true,
            'image' => 'catalog-social.jpg',
            'sort_order' => 100 + $number,
        ]);
    }

    $category = Category::query()->where('slug', 'digital-marketing')->firstOrFail();

    $this->get(route('categories.show', ['category' => $category, 'categories' => ['catalog-social-media']]))
        ->assertOk()
        ->assertSee('12 Services Available')
        ->assertSee('Paged Social 01')
        ->assertDontSee('Paged Social Tail')
        ->assertSee('page=2', false);

    $this->get(route('categories.show', ['category' => $category, 'categories' => ['catalog-social-media'], 'page' => 2]))
        ->assertOk()
        ->assertSee('12 Services Available')
        ->assertSee('Paged Social Tail')
        ->assertDontSee('Paged Social 01')
        ->assertDontSee('High-Conversion Google Ads Management')
        ->assertSee('categories%5B0%5D=catalog-social-media', false);
});
