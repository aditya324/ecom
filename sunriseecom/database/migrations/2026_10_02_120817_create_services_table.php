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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->string('short_description', 500);
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->boolean('is_deal')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('discount_label', 40)->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('deal_ends_at')->nullable();
            $table->timestamps();
        });

        $categoryIds = DB::table('categories')->pluck('id', 'slug');
        $endsAt = now()->addHours(8)->addMinutes(42)->addSeconds(16);

        DB::table('services')->insert([
            [
                'category_id' => $categoryIds['social-media'],
                'name' => 'Social Media Starter Package',
                'slug' => 'social-media-starter-package',
                'short_description' => 'Complete setup, branding, and 1 month of content calendar management for 3 platforms.',
                'description' => 'A starter social media engagement covering profile setup, branding, and one month of content calendar management across three platforms.',
                'price' => 899.00,
                'compare_price' => 1500.00,
                'rating' => 4.8,
                'review_count' => 124,
                'is_deal' => true,
                'is_active' => true,
                'discount_label' => 'SAVE 40%',
                'image' => 'deal-social-media.jpg',
                'sort_order' => 1,
                'deal_ends_at' => $endsAt,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryIds['development'],
                'name' => 'Accelerated Website Launch',
                'slug' => 'accelerated-website-launch',
                'short_description' => '5-page custom high-performance corporate website designed and deployed in 14 days.',
                'description' => 'A five-page custom corporate website, designed and deployed in 14 days.',
                'price' => 2400.00,
                'compare_price' => 3200.00,
                'rating' => 4.7,
                'review_count' => 89,
                'is_deal' => true,
                'is_active' => true,
                'discount_label' => 'SAVE 25%',
                'image' => 'deal-website-launch.jpg',
                'sort_order' => 2,
                'deal_ends_at' => $endsAt,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category_id' => $categoryIds['seo'],
                'name' => 'Comprehensive SEO Audit',
                'slug' => 'comprehensive-seo-audit',
                'short_description' => 'Deep-dive technical, on-page, and backlink analysis with a prioritized action plan.',
                'description' => 'Technical, on-page, and backlink analysis with a prioritized action plan.',
                'price' => 560.00,
                'compare_price' => 800.00,
                'rating' => 4.6,
                'review_count' => 45,
                'is_deal' => true,
                'is_active' => true,
                'discount_label' => 'SAVE 30%',
                'image' => 'deal-seo-audit.jpg',
                'sort_order' => 3,
                'deal_ends_at' => $endsAt,
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
        Schema::dropIfExists('services');
    }
};
