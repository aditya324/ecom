<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name');
            $table->string('gstin', 15)->nullable();
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->string('instagram')->nullable();
            $table->timestamps();
        });

        DB::table('businesses')->insert([
            'legal_name' => 'Sunrise Digital',
            'address' => null,
            'email' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->string('billing_address')->nullable()->after('email');
            $table->string('billing_city')->nullable()->after('billing_address');
            $table->string('billing_state')->nullable()->after('billing_city');
            $table->string('billing_pin', 6)->nullable()->after('billing_state');
            $table->string('billing_gstin', 15)->nullable()->after('billing_pin');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body');
            $table->timestamps();

            $table->unique(['user_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'billing_address',
                'billing_city',
                'billing_state',
                'billing_pin',
                'billing_gstin',
            ]);
        });

        Schema::dropIfExists('businesses');
    }
};
