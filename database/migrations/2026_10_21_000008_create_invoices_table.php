<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tagihan (invoice) yang dibuat admin/operator untuk murid.
     * Murid membayarnya melalui Midtrans Snap (QRIS) di area akun.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('lainnya');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('status')->default('belum_dibayar')->index();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();

            // Jejak pembayaran Midtrans (mirip kolom orders)
            $table->string('payment_type')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_snap_token')->nullable();
            $table->json('payment_payload')->nullable();

            // Siapa admin/operator yang membuat tagihan ini
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
