<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('pay_token')->nullable()->unique()->after('reply');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('quote_id')->nullable()->after('subscription_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quote_id');
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropUnique(['pay_token']);
            $table->dropColumn('pay_token');
        });
    }
};
