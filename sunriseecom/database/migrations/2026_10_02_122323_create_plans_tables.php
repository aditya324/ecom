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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40);
            $table->string('slug', 40)->unique();
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('yearly_price', 10, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
        });

        $categoryIds = DB::table('categories')->pluck('id', 'slug');

        $services = [
            'ui-ux-design' => ['UI/UX Design', 'branding', 'Interface and experience design for the pages in a package.'],
            'website-development-basic' => ['Website Development (Basic)', 'development', 'A basic marketing website built and launched with the package.'],
            'basic-seo' => ['Basic SEO', 'seo', 'Foundational on-page SEO for the pages included in the package.'],
            'social-media' => ['Social Media', 'social-media', 'Social media setup and posting included with the package.'],
            'marketing' => ['Marketing', 'digital-marketing', 'Campaign and content marketing included with the package.'],
        ];

        $serviceIds = [];

        foreach ($services as $slug => [$name, $category, $summary]) {
            $serviceIds[$slug] = DB::table('services')->insertGetId([
                'category_id' => $categoryIds[$category],
                'name' => $name,
                'slug' => $slug,
                'short_description' => $summary,
                'description' => $summary,
                'price' => 0,
                'price_suffix' => null,
                'compare_price' => null,
                'rating' => 0,
                'review_count' => 0,
                'is_deal' => false,
                'is_best_seller' => false,
                'is_active' => true,
                'discount_label' => null,
                'image' => null,
                'sort_order' => 0,
                'deal_ends_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $plans = [
            'mini' => ['Mini', 20000, ['ui-ux-design', 'website-development-basic', 'basic-seo']],
            'basic' => ['basic', 50000, ['ui-ux-design', 'website-development-basic', 'basic-seo', 'social-media', 'marketing']],
            'pro' => ['pro', 75000, ['ui-ux-design', 'website-development-basic', 'basic-seo', 'social-media', 'marketing', 'ui-ux-design', 'website-development-basic', 'basic-seo']],
        ];

        $sort = 1;

        foreach ($plans as $slug => [$name, $monthly, $included]) {
            $planId = DB::table('plans')->insertGetId([
                'name' => $name,
                'slug' => $slug,
                'monthly_price' => $monthly,
                'yearly_price' => $monthly * 10,
                'sort_order' => $sort,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('plan_items')->insert(array_map(
                fn (string $serviceSlug, int $index) => [
                    'plan_id' => $planId,
                    'service_id' => $serviceIds[$serviceSlug],
                    'sort_order' => $index + 1,
                ],
                $included,
                array_keys($included),
            ));

            $sort++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_items');
        Schema::dropIfExists('plans');

        DB::table('services')->whereIn('slug', [
            'ui-ux-design',
            'website-development-basic',
            'basic-seo',
            'social-media',
            'marketing',
        ])->delete();
    }
};
