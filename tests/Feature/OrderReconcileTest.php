<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderReconcileTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function order(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'FRC-TEST-00001',
            'name' => 'Murid Uji',
            'email' => 'murid@example.test',
            'phone' => '081100000000',
            'total' => 175000,
            'subtotal' => 175000,
            'payment_method' => 'transfer',
            'status' => 'menunggu_pembayaran',
            'midtrans_order_id' => 'FRC-TEST-00001~DEADBEEF',
        ], $overrides));
    }

    public function test_admin_order_list_reconciles_paid_order_from_midtrans(): void
    {
        $order = $this->order();

        // Webhook tidak pernah datang: status di DB masih menunggu_pembayaran.
        $this->assertSame('menunggu_pembayaran', $order->status);

        $this->mock(MidtransService::class, function ($mock) use ($order) {
            $mock->shouldReceive('isConfigured')->andReturnTrue();
            $mock->shouldReceive('transactionStatus')->with($order->midtrans_order_id)->andReturn([
                'order_id' => $order->midtrans_order_id,
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'payment_type' => 'qris',
                'transaction_id' => 'TXN-ORD-1',
                'gross_amount' => (string) $order->total,
            ]);
            $mock->shouldReceive('mapOrderStatus')->andReturn('dibayar');
        });

        $this->actingAs($this->admin())
            ->get(route('admin.orders.index'))
            ->assertOk();

        $order->refresh();
        $this->assertSame('dibayar', $order->status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_order_detail_reconciles_paid_order_from_midtrans(): void
    {
        $order = $this->order();

        $this->mock(MidtransService::class, function ($mock) use ($order) {
            $mock->shouldReceive('isConfigured')->andReturnTrue();
            $mock->shouldReceive('transactionStatus')->with($order->midtrans_order_id)->andReturn([
                'order_id' => $order->midtrans_order_id,
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'payment_type' => 'qris',
                'gross_amount' => (string) $order->total,
            ]);
            $mock->shouldReceive('mapOrderStatus')->andReturn('dibayar');
        });

        $this->actingAs($this->admin())
            ->get(route('admin.orders.show', $order))
            ->assertOk();

        $this->assertSame('dibayar', $order->fresh()->status);
    }

    public function test_paid_order_is_not_downgraded_by_stale_status(): void
    {
        $order = $this->order([
            'status' => 'dibayar',
            'paid_at' => now(),
        ]);

        $controller = app(\App\Http\Controllers\MidtransController::class);

        $controller->applyStatus($order, [
            'transaction_status' => 'expire',
            'gross_amount' => (string) $order->total,
        ]);

        $this->assertSame('dibayar', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->paid_at);
    }

    public function test_completed_order_is_not_downgraded(): void
    {
        $order = $this->order([
            'status' => 'selesai',
            'paid_at' => now()->subDay(),
        ]);

        $controller = app(\App\Http\Controllers\MidtransController::class);

        $controller->applyStatus($order, [
            'transaction_status' => 'expire',
            'gross_amount' => (string) $order->total,
        ]);

        $this->assertSame('selesai', $order->fresh()->status);
    }
}
