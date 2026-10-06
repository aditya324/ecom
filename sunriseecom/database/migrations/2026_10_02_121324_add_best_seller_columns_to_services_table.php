<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_best_seller')->default(false)->after('is_deal');
            $table->string('price_suffix', 12)->nullable()->after('price');
        });

        $categoryIds = DB::table('categories')->pluck('id', 'slug');

        DB::table('services')->insert([
            [
                'category_id' => $categoryIds['development'],
                'name' => 'E-commerce Website Setup & Development',
                'slug' => 'ecommerce-website-setup-development',
                'short_description' => 'A complete storefront setup, from theme and catalog to checkout, ready to take orders.',
                'description' => 'Storefront setup covering theme, catalog structure, and checkout so the shop can start taking orders.',
                'price' => 4500.00,
                'price_suffix' => null,
                'compare_price' => null,
                'rating' => 4.9,
                'review_count' => 342,
                'is_deal' => false,
                'is_best_seller' => true,
                'is_active' => true,
                'discount_label' => null,
                'image' => 'bestseller-ecommerce.jpg',
                'sort_order' => 1,
                'deal_ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryIds['digital-marketing'],
                'name' => 'Managed Monthly SEO Growth Accelerator',
                'slug' => 'managed-monthly-seo-growth-accelerator',
                'short_description' => 'Ongoing technical and content SEO aimed at steady organic growth.',
                'description' => 'A monthly SEO engagement covering technical fixes, content, and reporting aimed at steady organic growth.',
                'price' => 1200.00,
                'price_suffix' => '/mo',
                'compare_price' => null,
                'rating' => 4.8,
                'review_count' => 512,
                'is_deal' => false,
                'is_best_seller' => true,
                'is_active' => true,
                'discount_label' => null,
                'image' => 'bestseller-seo.jpg',
                'sort_order' => 2,
                'deal_ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryIds['branding'],
                'name' => 'Complete Corporate Brand Identity Design',
                'slug' => 'complete-corporate-brand-identity-design',
                'short_description' => 'Logo, color, and brand system for a company that needs a consistent identity.',
                'description' => 'A full brand identity covering logo, color, typography, and the core assets a company uses day to day.',
                'price' => 2800.00,
                'price_suffix' => null,
                'compare_price' => null,
                'rating' => 5.0,
                'review_count' => 128,
                'is_deal' => false,
                'is_best_seller' => true,
                'is_active' => true,
                'discount_label' => null,
                'image' => 'bestseller-branding.jpg',
                'sort_order' => 3,
                'deal_ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryIds['performance'],
                'name' => 'PPC Campaign Setup & Management',
                'slug' => 'ppc-campaign-setup-management',
                'short_description' => 'Paid search setup and ongoing management focused on qualified clicks.',
                'description' => 'Campaign setup plus ongoing management for paid search, with reporting focused on qualified clicks.',
                'price' => 1500.00,
                'price_suffix' => '/mo',
                'compare_price' => null,
                'rating' => 4.7,
                'review_count' => 215,
                'is_deal' => false,
                'is_best_seller' => true,
                'is_active' => true,
                'discount_label' => null,
                'image' => 'bestseller-ppc.jpg',
                'sort_order' => 4,
                'deal_ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('services')->whereIn('slug', [
            'ecommerce-website-setup-development',
            'managed-monthly-seo-growth-accelerator',
            'complete-corporate-brand-identity-design',
            'ppc-campaign-setup-management',
        ])->delete();

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['is_best_seller', 'price_suffix']);
        });
    }
};
