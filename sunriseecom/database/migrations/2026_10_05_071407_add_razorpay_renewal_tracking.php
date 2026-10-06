<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('refunded_amount', 10, 2)->default(0)->after('total');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedSmallInteger('paid_count')->default(0)->after('total_count');
            $table->string('short_url')->nullable()->after('razorpay_payment_id');
        });

        Schema::create('cart_drops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('cart_key');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_drops');

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['paid_count', 'short_url']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('refunded_amount');
        });
    }
};
