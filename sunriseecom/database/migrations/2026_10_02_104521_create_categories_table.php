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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->string('image')->nullable();
            $table->boolean('show_in_nav')->default(false);
            $table->boolean('show_on_home')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
        });

        $images = [
            'development' => 'category-development.jpg',
            'digital-marketing' => 'category-digital-marketing.jpg',
            'branding' => 'category-branding.jpg',
            'performance' => 'category-performance.jpg',
            'ooh' => 'category-ooh.jpg',
            'video' => 'category-video.jpg',
            'business-growth' => 'category-business-growth.jpg',
            'earned-growth' => 'category-earned-growth.jpg',
        ];

        $rows = [
            ['Development', 'development', true, true, 1],
            ['Branding', 'branding', true, true, 2],
            ['Digital Marketing', 'digital-marketing', true, true, 3],
            ['SEO', 'seo', true, false, 4],
            ['Social Media', 'social-media', true, false, 5],
            ['Business Growth', 'business-growth', true, true, 6],
            ['Performance', 'performance', false, true, 7],
            ['OOH', 'ooh', false, true, 8],
            ['Video', 'video', false, true, 9],
            ['Earned Growth', 'earned-growth', false, true, 10],
            ['Packages', 'packages', true, false, 11],
        ];

        DB::table('categories')->insert(array_map(fn (array $row) => [
            'name' => $row[0],
            'slug' => $row[1],
            'image' => $images[$row[1]] ?? null,
            'show_in_nav' => $row[2],
            'show_on_home' => $row[3],
            'sort_order' => $row[4],
        ], $rows));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
