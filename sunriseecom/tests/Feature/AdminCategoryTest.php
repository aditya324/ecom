<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('a guest cannot open the category catalog', function () {
    $this->get('/admin/categories')->assertRedirect('/admin/login');
});

test('a customer cannot open the category catalog', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->get('/admin/categories')->assertRedirect('/admin/login');
});

test('an admin can add a category and hide it from the store', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->post('/admin/categories', [
        'name' => 'Harbor Lighting',
        'show_in_nav' => '1',
        'is_active' => '1',
    ])->assertRedirect('/admin/categories');

    $category = Category::query()->where('slug', 'harbor-lighting')->firstOrFail();

    expect($category->show_in_nav)->toBeTrue();

    $this->get('/categories')->assertOk()->assertSee('Harbor Lighting');

    $this->actingAs($admin, 'admin')
        ->post(route('admin.categories.hide', $category))
        ->assertRedirect('/admin/categories');

    expect($category->fresh()->is_active)->toBeFalse();
    expect(Category::query()->active()->where('slug', 'harbor-lighting')->exists())->toBeFalse();

    $this->get(route('categories.show', 'harbor-lighting'))->assertNotFound();
});

test('an admin can upload a category image', function () {
    $admin = Admin::factory()->create();
    $upload = UploadedFile::fake()->image('harbor.jpg', 80, 40);

    $this->actingAs($admin, 'admin')->post('/admin/categories', [
        'name' => 'Harbor Lighting',
        'is_active' => '1',
        'image' => $upload,
    ])->assertRedirect('/admin/categories')->assertSessionHasNoErrors();

    $category = Category::query()->where('slug', 'harbor-lighting')->firstOrFail();
    $path = public_path('assets/categories/'.$category->image);

    expect($category->image)->toStartWith('harbor-lighting-')
        ->and(File::exists($path))->toBeTrue();

    File::delete($path);
});

test('a category image must be an image file', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->post('/admin/categories', [
        'name' => 'Harbor Lighting',
        'image' => UploadedFile::fake()->create('notes.pdf', 20, 'application/pdf'),
    ])->assertSessionHasErrors('image');
});

test('an admin can delete a category and keep its subcategories', function () {
    $admin = Admin::factory()->create();
    $parent = Category::query()->where('slug', 'digital-marketing')->firstOrFail();
    $category = Category::query()->create([
        'parent_id' => $parent->id,
        'name' => 'Harbor Lighting',
        'slug' => 'harbor-lighting',
        'is_active' => true,
        'show_in_nav' => false,
        'show_on_home' => false,
        'sort_order' => 0,
    ]);
    $child = Category::query()->create([
        'parent_id' => $category->id,
        'name' => 'Harbor Lamps',
        'slug' => 'harbor-lamps',
        'is_active' => true,
        'show_in_nav' => false,
        'show_on_home' => false,
        'sort_order' => 0,
    ]);
    $service = Service::query()->create([
        'category_id' => $category->id,
        'name' => 'Harbor Audit',
        'slug' => 'harbor-audit',
        'short_description' => 'Gone with the category.',
        'description' => 'Gone with the category.',
        'price' => 1000,
        'billing_type' => 'one-time',
        'is_listed' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    $this->actingAs($admin, 'admin')
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect('/admin/categories');

    expect(Category::query()->where('slug', 'harbor-lighting')->exists())->toBeFalse()
        ->and($child->fresh()->parent_id)->toBe($parent->id)
        ->and(Service::query()->whereKey($service->id)->exists())->toBeFalse();
});

test('a guest cannot delete a category', function () {
    $category = Category::query()->firstOrFail();

    $this->delete(route('admin.categories.destroy', $category))->assertRedirect('/admin/login');
});

test('a category slug is created from the name', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->post('/admin/categories', [
        'name' => 'Test Slug',
        'is_active' => '1',
    ])->assertRedirect('/admin/categories');

    expect(Category::query()->where('slug', 'test-slug')->exists())->toBeTrue();
});

test('a category name is required', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->post('/admin/categories', ['name' => ''])
        ->assertSessionHasErrors('name');
});
