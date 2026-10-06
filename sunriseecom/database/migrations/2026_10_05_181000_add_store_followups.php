<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('customer_note')->nullable()->after('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('billing_address')->nullable()->after('email');
            $table->string('billing_city')->nullable()->after('billing_address');
            $table->string('billing_state')->nullable()->after('billing_city');
            $table->string('billing_pin', 6)->nullable()->after('billing_state');
            $table->string('billing_gstin', 15)->nullable()->after('billing_pin');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('service_id')->constrained()->nullOnDelete();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->change();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->after('service_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_hidden')->default(false)->after('body');
            $table->unique(['user_id', 'plan_id']);
        });

        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->text('body');
            $table->string('status')->default('new');
            $table->timestamps();
        });

        DB::table('services')
            ->where('is_deal', true)
            ->whereNotNull('deal_ends_at')
            ->where('deal_ends_at', '<', now())
            ->update(['deal_ends_at' => now()->addDays(7)]);

        DB::table('order_items')
            ->whereNull('plan_id')
            ->whereNull('service_id')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $item): void {
                $planId = DB::table('plans')->where('name', $item->service_name)->value('id');

                if ($planId !== null) {
                    DB::table('order_items')->where('id', $item->id)->update(['plan_id' => $planId]);
                }
            });

        $business = DB::table('businesses')->orderBy('id')->first();

        if ($business !== null && $business->address === null) {
            DB::table('businesses')->where('id', $business->id)->update([
                'legal_name' => 'Sunrise Digital Media',
                'gstin' => '29FLQPS7503Q1ZU',
                'address' => 'MEHTA ARCADE, 2nd Floor, 15th Cross, Outer Ring Rd, near Sarakki Signal, Sarakki Kere, Bengaluru, Karnataka 560078',
                'email' => 'support@sunrisedigital.co.in',
                'instagram' => 'https://www.instagram.com/sunrisedigitalofficial',
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'plan_id']);
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn('is_hidden');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'billing_address',
                'billing_city',
                'billing_state',
                'billing_pin',
                'billing_gstin',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('customer_note');
        });
    }
};
