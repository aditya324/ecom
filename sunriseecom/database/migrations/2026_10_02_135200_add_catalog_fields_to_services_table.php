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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('tagline', 180)->nullable();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('group_name', 80)->nullable();
            $table->string('billing_type', 20)->nullable();
            $table->string('delivery_label', 40)->nullable();
            $table->string('badge', 20)->nullable();
            $table->boolean('is_listed')->default(true);
        });

        DB::table('categories')->where('slug', 'digital-marketing')->update([
            'tagline' => 'Elevate your brand with industrial-scale marketing solutions.',
        ]);

        DB::table('services')->whereIn('slug', [
            'ui-ux-design',
            'website-development-basic',
            'basic-seo',
            'social-media',
            'marketing',
        ])->update(['is_listed' => false]);

        DB::table('services')->where('slug', 'managed-monthly-seo-growth-accelerator')->update([
            'group_name' => 'SEO & SEM',
            'billing_type' => 'monthly',
            'delivery_label' => 'Ongoing Monthly',
            'sort_order' => 7,
        ]);

        $categoryId = DB::table('categories')->where('slug', 'digital-marketing')->value('id');

        DB::table('services')->insert([
            [
                'category_id' => $categoryId,
                'name' => 'Enterprise Social Media Management',
                'slug' => 'enterprise-social-media-management',
                'group_name' => 'Social Media',
                'short_description' => 'Full-service social management across the brand channels that drive demand.',
                'description' => 'Monthly social media management covering content, publishing, and reporting for a brand that needs a consistent presence.',
                'price' => 24999.00,
                'price_suffix' => null,
                'compare_price' => 30000.00,
                'billing_type' => 'monthly',
                'delivery_label' => '30 Days Delivery',
                'rating' => 4.9,
                'review_count' => 1200,
                'is_deal' => false,
                'is_best_seller' => false,
                'is_listed' => true,
                'is_active' => true,
                'badge' => 'bestseller',
                'discount_label' => null,
                'image' => 'catalog-social.jpg',
                'sort_order' => 1,
                'deal_ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryId,
                'name' => 'Comprehensive Technical SEO Audit',
                'slug' => 'comprehensive-technical-seo',
                'group_name' => 'SEO & SEM',
                'short_description' => 'A technical and on-page SEO audit with a prioritized fix list.',
                'description' => 'Technical, on-page, and content SEO review with a prioritized plan the team can execute.',
                'price' => 15000.00,
                'price_suffix' => null,
                'compare_price' => null,
                'billing_type' => 'one-time',
                'delivery_label' => '14 Days Delivery',
                'rating' => 4.7,
                'review_count' => 845,
                'is_deal' => false,
                'is_best_seller' => false,
                'is_listed' => true,
                'is_active' => true,
                'badge' => null,
                'discount_label' => null,
                'image' => 'catalog-seo.jpg',
                'sort_order' => 2,
                'deal_ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryId,
                'name' => 'High-Conversion Google Ads Management',
                'slug' => 'high-conversion-google-ads',
                'group_name' => 'PPC Advertising',
                'short_description' => 'Search and display campaigns managed for qualified leads.',
                'description' => 'Ongoing Google Ads management covering search structure, creative, and monthly optimization.',
                'price' => 45000.00,
                'price_suffix' => null,
                'compare_price' => null,
                'billing_type' => 'monthly',
                'delivery_label' => 'Ongoing Monthly',
                'rating' => 4.8,
                'review_count' => 2100,
                'is_deal' => false,
                'is_best_seller' => false,
                'is_listed' => true,
                'is_active' => true,
                'badge' => 'verified',
                'discount_label' => null,
                'image' => 'catalog-ads.jpg',
                'sort_order' => 3,
                'deal_ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryId,
                'name' => 'Content Marketing Engine',
                'slug' => 'content-marketing-engine',
                'group_name' => 'Content Marketing',
                'short_description' => 'A content system of articles and landing pages aimed at organic demand.',
                'description' => 'Editorial planning plus a first set of articles and landing pages for organic acquisition.',
                'price' => 18000.00,
                'price_suffix' => null,
                'compare_price' => null,
                'billing_type' => 'one-time',
                'delivery_label' => '21 Days Delivery',
                'rating' => 4.6,
                'review_count' => 410,
                'is_deal' => false,
                'is_best_seller' => false,
                'is_listed' => true,
                'is_active' => true,
                'badge' => null,
                'discount_label' => null,
                'image' => 'catalog-seo.jpg',
                'sort_order' => 4,
                'deal_ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryId,
                'name' => 'Email Marketing Retainer',
                'slug' => 'email-marketing-retainer',
                'group_name' => 'Email Marketing',
                'short_description' => 'Monthly campaigns, flows, and reporting for an owned audience.',
                'description' => 'A monthly email program covering campaign sends, automation flows, and performance reporting.',
                'price' => 12000.00,
                'price_suffix' => null,
                'compare_price' => null,
                'billing_type' => 'monthly',
                'delivery_label' => 'Ongoing Monthly',
                'rating' => 4.5,
                'review_count' => 260,
                'is_deal' => false,
                'is_best_seller' => false,
                'is_listed' => true,
                'is_active' => true,
                'badge' => 'bestseller',
                'discount_label' => null,
                'image' => 'catalog-social.jpg',
                'sort_order' => 5,
                'deal_ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryId,
                'name' => 'Social Media Content Calendar',
                'slug' => 'social-media-content-calendar',
                'group_name' => 'Social Media',
                'short_description' => 'A one-month content calendar with captions and posting guidance.',
                'description' => 'One month of social content planned, written, and handed over ready to publish.',
                'price' => 8000.00,
                'price_suffix' => null,
                'compare_price' => null,
                'billing_type' => 'one-time',
                'delivery_label' => '7 Days Delivery',
                'rating' => 4.4,
                'review_count' => 96,
                'is_deal' => false,
                'is_best_seller' => false,
                'is_listed' => true,
                'is_active' => true,
                'badge' => null,
                'discount_label' => null,
                'image' => 'catalog-ads.jpg',
                'sort_order' => 6,
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
            'enterprise-social-media-management',
            'comprehensive-technical-seo',
            'high-conversion-google-ads',
            'content-marketing-engine',
            'email-marketing-retainer',
            'social-media-content-calendar',
        ])->delete();

        DB::table('services')->where('slug', 'managed-monthly-seo-growth-accelerator')->update([
            'group_name' => null,
            'billing_type' => null,
            'delivery_label' => null,
            'sort_order' => 2,
        ]);

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['group_name', 'billing_type', 'delivery_label', 'badge', 'is_listed']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('tagline');
        });
    }
};
