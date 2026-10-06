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
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
        });

        $parentId = DB::table('categories')->where('slug', 'digital-marketing')->value('id');

        $children = [
            ['Social Media', 'catalog-social-media', 1],
            ['SEO & SEM', 'catalog-seo-sem', 2],
            ['Content Marketing', 'catalog-content-marketing', 3],
            ['Email Marketing', 'catalog-email-marketing', 4],
            ['PPC Advertising', 'catalog-ppc', 5],
        ];

        foreach ($children as [$name, $slug, $sort]) {
            DB::table('categories')->insert([
                'parent_id' => $parentId,
                'name' => $name,
                'slug' => $slug,
                'image' => null,
                'tagline' => null,
                'show_in_nav' => false,
                'show_on_home' => false,
                'sort_order' => $sort,
            ]);
        }

        $childIds = DB::table('categories')->where('parent_id', $parentId)->pluck('id', 'slug');

        $groups = [
            'Social Media' => 'catalog-social-media',
            'SEO & SEM' => 'catalog-seo-sem',
            'Content Marketing' => 'catalog-content-marketing',
            'Email Marketing' => 'catalog-email-marketing',
            'PPC Advertising' => 'catalog-ppc',
        ];

        foreach ($groups as $group => $slug) {
            DB::table('services')
                ->where('category_id', $parentId)
                ->where('group_name', $group)
                ->update(['category_id' => $childIds[$slug]]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $parentId = DB::table('categories')->where('slug', 'digital-marketing')->value('id');
        $childIds = DB::table('categories')->where('parent_id', $parentId)->pluck('id');

        DB::table('services')->whereIn('category_id', $childIds)->update([
            'category_id' => $parentId,
        ]);

        DB::table('categories')->whereIn('id', $childIds)->delete();

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
        });
    }
};
