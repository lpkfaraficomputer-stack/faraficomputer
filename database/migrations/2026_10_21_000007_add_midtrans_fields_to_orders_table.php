<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom tambahan untuk integrasi payment gateway Midtrans.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_provider')->nullable()->after('payment_method');
            $table->string('midtrans_order_id')->nullable()->unique()->after('payment_provider');
            $table->string('midtrans_transaction_id')->nullable()->after('midtrans_order_id');
            $table->string('midtrans_snap_token')->nullable()->after('midtrans_transaction_id');
            $table->string('payment_type')->nullable()->after('midtrans_snap_token');
            $table->string('payment_status')->nullable()->after('payment_type');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->json('payment_payload')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['midtrans_order_id']);
            $table->dropColumn([
                'payment_provider',
                'midtrans_order_id',
                'midtrans_transaction_id',
                'midtrans_snap_token',
                'payment_type',
                'payment_status',
                'paid_at',
                'payment_payload',
            ]);
        });
    }
};
