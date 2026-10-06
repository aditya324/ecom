<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->text('reply')->nullable()->after('body');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('subscription_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        $renewals = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('order_items.duration_label', 'like', '%renewal%')
            ->whereNull('orders.subscription_id')
            ->whereNotNull('orders.user_id')
            ->select('orders.id as order_id', 'orders.user_id', 'order_items.service_name')
            ->get();

        foreach ($renewals as $renewal) {
            $subscriptionId = DB::table('subscriptions')
                ->where('user_id', $renewal->user_id)
                ->where('name', $renewal->service_name)
                ->where('order_id', '!=', $renewal->order_id)
                ->value('id');

            if ($subscriptionId === null) {
                continue;
            }

            DB::table('orders')->where('id', $renewal->order_id)->update([
                'subscription_id' => $subscriptionId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscription_id');
        });

        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropColumn('reply');
        });
    }
};
