<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('categories')->where('slug', 'catalog-social-media')->update([
            'name' => 'Social Media Marketing',
        ]);

        DB::table('services')->where('slug', 'enterprise-social-media-management')->update([
            'name' => 'Instagram Marketing',
            'slug' => 'instagram-marketing',
            'short_description' => 'Instagram content, reels, and campaign management for a brand account.',
            'description' => 'Monthly Instagram marketing covering content, reels, and campaign management for a brand account.',
        ]);

        DB::table('services')->where('slug', 'social-media-content-calendar')->update([
            'name' => 'WhatsApp Marketing',
            'slug' => 'whatsapp-marketing',
            'short_description' => 'WhatsApp broadcast setup, templates, and a first campaign for an owned list.',
            'description' => 'WhatsApp marketing covering broadcast setup, message templates, and a first campaign.',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('categories')->where('slug', 'catalog-social-media')->update([
            'name' => 'Social Media',
        ]);

        DB::table('services')->where('slug', 'instagram-marketing')->update([
            'name' => 'Enterprise Social Media Management',
            'slug' => 'enterprise-social-media-management',
            'short_description' => 'Full-service social management across the brand channels that drive demand.',
            'description' => 'Monthly social media management covering content, publishing, and reporting for a brand that needs a consistent presence.',
        ]);

        DB::table('services')->where('slug', 'whatsapp-marketing')->update([
            'name' => 'Social Media Content Calendar',
            'slug' => 'social-media-content-calendar',
            'short_description' => 'A one-month content calendar with captions and posting guidance.',
            'description' => 'One month of social content planned, written, and handed over ready to publish.',
        ]);
    }
};
