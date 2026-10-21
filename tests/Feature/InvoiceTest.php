<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function murid(): User
    {
        return User::factory()->create(['role' => User::ROLE_MURID, 'name' => 'Murid Uji']);
    }

    private function invoiceFor(User $murid, array $overrides = []): Invoice
    {
        return Invoice::create(array_merge([
            'invoice_number' => 'INV-TEST-00001',
            'user_id' => $murid->id,
            'title' => 'SPP Uji',
            'type' => 'spp',
            'amount' => 150000,
            'status' => Invoice::STATUS_BELUM_DIBAYAR,
            'due_date' => now()->addDays(7)->toDateString(),
        ], $overrides));
    }

    public function test_admin_can_create_invoice_for_murid(): void
    {
        $murid = $this->murid();

        $response = $this->actingAs($this->admin())
            ->post(route('admin.invoices.store'), [
                'user_id' => $murid->id,
                'title' => 'SPP November',
                'type' => 'spp',
                'amount' => 200000,
                'due_date' => now()->addDays(10)->toDateString(),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'user_id' => $murid->id,
            'title' => 'SPP November',
            'amount' => 200000,
            'status' => Invoice::STATUS_BELUM_DIBAYAR,
        ]);
    }

    public function test_invoice_cannot_be_created_for_non_murid(): void
    {
        $operator = User::factory()->create(['role' => User::ROLE_OPERATOR]);

        $this->actingAs($this->admin())
            ->post(route('admin.invoices.store'), [
                'user_id' => $operator->id,
                'title' => 'Salah sasaran',
                'type' => 'lainnya',
                'amount' => 50000,
            ])
            ->assertSessionHasErrors('user_id');
    }

    public function test_murid_can_only_see_own_invoices(): void
    {
        $murid = $this->murid();
        $lain = $this->murid();
        $milikSaya = $this->invoiceFor($murid, ['invoice_number' => 'INV-MILIK-1']);
        $milikOrang = $this->invoiceFor($lain, ['invoice_number' => 'INV-ORANG-1']);

        $this->actingAs($murid)
            ->get(route('account.invoices.index'))
            ->assertOk()
            ->assertSee('INV-MILIK-1')
            ->assertDontSee('INV-ORANG-1');

        $this->actingAs($murid)
            ->get(route('account.invoices.show', $milikOrang))
            ->assertRedirect(route('account.invoices.index'));
    }

    public function test_non_murid_cannot_access_account_area(): void
    {
        $this->actingAs($this->admin())
            ->get(route('account.invoices.index'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_can_mark_invoice_paid(): void
    {
        $invoice = $this->invoiceFor($this->murid());

        $this->actingAs($this->admin())
            ->patch(route('admin.invoices.status', $invoice), ['status' => Invoice::STATUS_DIBAYAR])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame(Invoice::STATUS_DIBAYAR, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_map_invoice_status_settlement_is_paid(): void
    {
        $service = app(\App\Services\MidtransService::class);

        $this->assertSame(Invoice::STATUS_DIBAYAR, $service->mapInvoiceStatus('settlement'));
        $this->assertSame(Invoice::STATUS_BELUM_DIBAYAR, $service->mapInvoiceStatus('pending'));
        $this->assertSame(Invoice::STATUS_DIBATALKAN, $service->mapInvoiceStatus('expire'));
        $this->assertNull($service->mapInvoiceStatus('capture', 'challenge'));
    }

    public function test_pay_endpoint_requires_configuration(): void
    {
        config()->set('midtrans.server_key', '');
        $murid = $this->murid();
        $invoice = $this->invoiceFor($murid);

        $this->actingAs($murid)
            ->postJson(route('account.invoices.pay', $invoice))
            ->assertStatus(503);
    }

    public function test_overdue_detection(): void
    {
        $invoice = $this->invoiceFor($this->murid(), ['due_date' => now()->subDay()->toDateString()]);

        $this->assertTrue($invoice->isOverdue());

        $invoice->update(['status' => Invoice::STATUS_DIBAYAR]);
        $this->assertFalse($invoice->fresh()->isOverdue());
    }

    public function test_unique_midtrans_order_id_can_be_mapped_back(): void
    {
        $service = app(\App\Services\MidtransService::class);

        $base = 'INV-261021-99539';
        $unique1 = $service->buildUniqueOrderId($base);
        $unique2 = $service->buildUniqueOrderId($base);

        // Dua percobaan menghasilkan order_id yang berbeda…
        $this->assertNotSame($unique1, $unique2);
        // …tetapi keduanya bisa dipetakan balik ke nomor invoice resmi.
        $this->assertSame($base, $service->baseOrderId($unique1));
        $this->assertSame($base, $service->baseOrderId($unique2));
        $this->assertStringStartsWith($base.'~', $unique1);

        // Tanpa suffix tetap aman (idempoten untuk ID lama).
        $this->assertSame($base, $service->baseOrderId($base));
    }

    public function test_webhook_maps_suffixed_order_id_to_invoice(): void
    {
        $invoice = $this->invoiceFor($this->murid());
        $midtransOrderId = $invoice->invoice_number.'~ABC12345';

        // Mock panggilan status Midtrans agar tidak menembak jaringan.
        $this->mock(\App\Services\MidtransService::class, function ($mock) use ($invoice, $midtransOrderId) {
            $mock->shouldReceive('baseOrderId')->andReturnUsing(fn ($id) => explode('~', $id, 2)[0]);
            $mock->shouldReceive('transactionStatus')->with($midtransOrderId)->andReturn([
                'order_id' => $midtransOrderId,
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'payment_type' => 'qris',
                'transaction_id' => 'TXN-1',
                'gross_amount' => (string) $invoice->amount,
            ]);
            $mock->shouldReceive('mapInvoiceStatus')->andReturn(Invoice::STATUS_DIBAYAR);
        });

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $midtransOrderId,
            'transaction_status' => 'settlement',
        ]);

        $response->assertOk();
        $invoice->refresh();
        $this->assertSame(Invoice::STATUS_DIBAYAR, $invoice->status);
        $this->assertSame($midtransOrderId, $invoice->midtrans_order_id);
    }

    public function test_admin_list_reconciles_paid_invoice_from_midtrans(): void
    {
        $invoice = $this->invoiceFor($this->murid(), [
            'midtrans_order_id' => 'INV-TEST-00001~DEADBEEF',
        ]);

        // Webhook tidak pernah datang: status di DB masih belum_dibayar.
        $this->assertSame(Invoice::STATUS_BELUM_DIBAYAR, $invoice->status);

        // Namun saat admin membuka daftar, status ditarik langsung dari Midtrans.
        $this->mock(\App\Services\MidtransService::class, function ($mock) use ($invoice) {
            $mock->shouldReceive('isConfigured')->andReturnTrue();
            $mock->shouldReceive('transactionStatus')->with($invoice->midtrans_order_id)->andReturn([
                'order_id' => $invoice->midtrans_order_id,
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'payment_type' => 'qris',
                'transaction_id' => 'TXN-9',
                'gross_amount' => (string) $invoice->amount,
            ]);
            $mock->shouldReceive('mapInvoiceStatus')->andReturn(Invoice::STATUS_DIBAYAR);
        });

        $this->actingAs($this->admin())
            ->get(route('admin.invoices.index'))
            ->assertOk();

        $invoice->refresh();
        $this->assertSame(Invoice::STATUS_DIBAYAR, $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_paid_invoice_is_not_downgraded_by_stale_status(): void
    {
        $invoice = $this->invoiceFor($this->murid(), [
            'status' => Invoice::STATUS_DIBAYAR,
            'paid_at' => now(),
            'midtrans_order_id' => 'INV-TEST-00001~OLD',
        ]);

        $controller = app(\App\Http\Controllers\AccountInvoiceController::class);

        // Notifikasi kedaluwarsa datang terlambat — tidak boleh menurunkan status.
        $controller->applyStatus($invoice, [
            'transaction_status' => 'expire',
            'gross_amount' => (string) $invoice->amount,
        ]);

        $this->assertSame(Invoice::STATUS_DIBAYAR, $invoice->fresh()->status);
        $this->assertNotNull($invoice->fresh()->paid_at);
    }

    public function test_murid_opening_invoice_reconciles_payment(): void
    {
        $murid = $this->murid();
        $invoice = $this->invoiceFor($murid, ['midtrans_order_id' => 'INV-TEST-00001~CAFE']);

        $this->mock(\App\Services\MidtransService::class, function ($mock) use ($invoice) {
            $mock->shouldReceive('isConfigured')->andReturnTrue();
            $mock->shouldReceive('fetchInvoiceStatus')->andReturn([
                'order_id' => $invoice->midtrans_order_id,
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
                'payment_type' => 'qris',
                'gross_amount' => (string) $invoice->amount,
            ]);
            $mock->shouldReceive('mapInvoiceStatus')->andReturn(Invoice::STATUS_DIBAYAR);
        });

        $this->actingAs($murid)
            ->get(route('account.invoices.show', $invoice))
            ->assertOk();

        $this->assertSame(Invoice::STATUS_DIBAYAR, $invoice->fresh()->status);
    }
}
