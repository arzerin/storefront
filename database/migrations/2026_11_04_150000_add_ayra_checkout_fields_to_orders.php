<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->change();
            if (! Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->default('card');
                $table->string('payment_status')->default('paid');
                $table->string('order_status')->default('pending');
            }
        });

        if (! Schema::hasIndex('orders', 'orders_stripe_checkout_session_id_unique')) {
            Schema::table('orders', fn (Blueprint $table) => $table->unique('stripe_checkout_session_id'));
        }
    }

    public function down(): void
    {
        // Existing shops may already have COD orders and payment columns.
    }
};
