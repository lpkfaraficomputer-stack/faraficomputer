<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    /**
     * Apakah Midtrans sudah dikonfigurasi (server key tersedia)?
     */
    public function isConfigured(): bool
    {
        return filled(config('midtrans.server_key'));
    }

    /**
     * Bangun order_id Midtrans yang unik untuk sebuah transaksi.
     *
     * Midtrans hanya mengizinkan satu order_id dipakai sekali. Bila murid
     * membatalkan lalu membayar ulang (atau token lama kedaluwarsa), order_id
     * yang sama akan ditolak ("order_id sudah digunakan"). Karena itu kita
     * menambahkan suffix percobaan unik di belakang nomor resmi.
     *
     * Format: "INV-XXXX~<uniqid>" / "FRC-XXXX~<uniqid>". Simbol "~" dipakai
     * sebagai pemisah karena Midtrans mengizinkan huruf, angka, dan - _ : .
     * (tidak mengizinkan spasi).
     */
    public function buildUniqueOrderId(string $baseOrderId): string
    {
        return $baseOrderId.'~'.strtoupper(bin2hex(random_bytes(4)));
    }

    /**
     * Ambil kembali nomor resmi (invoice/order) dari order_id Midtrans yang
     * mungkin memiliki suffix percobaan.
     */
    public function baseOrderId(string $midtransOrderId): string
    {
        return explode('~', $midtransOrderId, 2)[0];
    }

    /**
     * Buat transaksi Snap dan kembalikan token + redirect URL.
     *
     * @return array{token: string, redirect_url: string|null}|null
     */
    public function createSnapToken(Order $order): ?array
    {
        if (! $this->isConfigured()) {
            Log::warning('Midtrans: server key belum diisi, transaksi dilewati.', [
                'order' => $order->order_number,
            ]);

            return null;
        }

        $midtransOrderId = $this->buildUniqueOrderId($order->order_number);

        $payload = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->name,
                'email' => $order->email,
                'phone' => $order->phone,
            ],
            'item_details' => $this->itemDetails($order),
            'expiry' => [
                'unit' => 'minutes',
                'duration' => config('midtrans.expiry_minutes', 1440),
            ],
        ];

        $enabled = config('midtrans.enabled_payments', []);
        if (! empty($enabled)) {
            $payload['enabled_payments'] = array_values($enabled);
        }

        if ($url = config('midtrans.notification_url')) {
            $payload['callbacks'] = ['finish' => route('midtrans.finish')];

            // notifikasi diatur di dashboard Midtrans, tapi tetap bisa ditegaskan
            $payload['custom_field1'] = 'notify:'.$url;
        }

        try {
            $response = Http::withBasicAuth(config('midtrans.server_key'), '')
                ->acceptJson()
                ->timeout(15)
                ->post(config('midtrans.snap_url'), $payload);

            if ($response->failed()) {
                Log::error('Midtrans Snap gagal.', [
                    'order' => $order->order_number,
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);

                return null;
            }

            return [
                'token' => $response->json('token'),
                'redirect_url' => $response->json('redirect_url'),
                'order_id' => $midtransOrderId,
            ];
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap exception: '.$e->getMessage(), [
                'order' => $order->order_number,
            ]);

            return null;
        }
    }

    /**
     * Buat transaksi Snap untuk sebuah tagihan (invoice) murid.
     *
     * @return array{token: string, redirect_url: string|null}|null
     */
    public function createSnapTokenForInvoice(Invoice $invoice): ?array
    {
        if (! $this->isConfigured()) {
            Log::warning('Midtrans: server key belum diisi, tagihan dilewati.', [
                'invoice' => $invoice->invoice_number,
            ]);

            return null;
        }

        $invoice->loadMissing('user');

        $midtransOrderId = $this->buildUniqueOrderId($invoice->invoice_number);

        $payload = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => (int) $invoice->amount,
            ],
            'customer_details' => [
                'first_name' => $invoice->user?->name ?? 'Murid',
                'email' => $invoice->user?->email,
                'phone' => $invoice->user?->phone,
            ],
            'item_details' => [[
                'id' => $invoice->invoice_number,
                'price' => (int) $invoice->amount,
                'quantity' => 1,
                'name' => mb_substr($invoice->title, 0, 50),
            ]],
            'expiry' => [
                'unit' => 'minutes',
                'duration' => config('midtrans.expiry_minutes', 1440),
            ],
        ];

        $enabled = config('midtrans.enabled_payments', []);
        if (! empty($enabled)) {
            $payload['enabled_payments'] = array_values($enabled);
        }

        if (config('midtrans.notification_url')) {
            $payload['callbacks'] = ['finish' => route('account.invoices.finish')];
        }

        try {
            $response = Http::withBasicAuth(config('midtrans.server_key'), '')
                ->acceptJson()
                ->timeout(15)
                ->post(config('midtrans.snap_url'), $payload);

            if ($response->failed()) {
                Log::error('Midtrans Snap tagihan gagal.', [
                    'invoice' => $invoice->invoice_number,
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);

                return null;
            }

            return [
                'token' => $response->json('token'),
                'redirect_url' => $response->json('redirect_url'),
                'order_id' => $midtransOrderId,
            ];
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap tagihan exception: '.$e->getMessage(), [
                'invoice' => $invoice->invoice_number,
            ]);

            return null;
        }
    }

    /**
     * Ambil status transaksi langsung dari Midtrans (sumber kebenaran webhook).
     *
     * @return array<string, mixed>|null
     */
    public function transactionStatus(string $midtransOrderId): ?array
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withBasicAuth(config('midtrans.server_key'), '')
                ->acceptJson()
                ->timeout(15)
                ->get(config('midtrans.api_status_url').$midtransOrderId.'/status');

            if ($response->failed()) {
                Log::warning('Midtrans status gagal.', [
                    'order' => $midtransOrderId,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Midtrans status exception: '.$e->getMessage(), ['order' => $midtransOrderId]);

            return null;
        }
    }

    /**
     * Cocokkan status Midtrans ke status pesanan internal.
     */
    public function mapOrderStatus(string $transactionStatus, ?string $fraudStatus = null): ?string
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'challenge' ? null : 'dibayar',
            'settlement' => 'dibayar',
            'pending' => 'menunggu_pembayaran',
            'deny', 'cancel', 'expire', 'failure' => 'dibatalkan',
            'refund', 'partial_refund' => 'dibatalkan',
            default => null,
        };
    }

    /**
     * Cocokkan status Midtrans ke status tagihan (invoice) internal.
     */
    public function mapInvoiceStatus(string $transactionStatus, ?string $fraudStatus = null): ?string
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'challenge' ? null : Invoice::STATUS_DIBAYAR,
            'settlement' => Invoice::STATUS_DIBAYAR,
            'pending' => Invoice::STATUS_BELUM_DIBAYAR,
            'deny', 'cancel', 'expire', 'failure' => Invoice::STATUS_DIBATALKAN,
            'refund', 'partial_refund' => Invoice::STATUS_DIBATALKAN,
            default => null,
        };
    }

    /**
     * Ambil status transaksi terkini dari Midtrans untuk sebuah tagihan dan
     * kembalikan bila transaksi sudah lunas.
     *
     * Dipakai sebagai jaring pengaman otomatis: bila webhook tidak sampai
     * (mis. server tidak dapat diakses publik), status tetap bisa ditarik saat
     * halaman dibuka sehingga tidak perlu konfirmasi manual.
     *
     * @return array<string, mixed>|null
     */
    public function fetchInvoiceStatus(Invoice $invoice): ?array
    {
        if (! $invoice->midtrans_order_id) {
            return null;
        }

        return $this->transactionStatus($invoice->midtrans_order_id);
    }

    /**
     * Ambil status transaksi terkini dari Midtrans untuk sebuah pesanan.
     *
     * @return array<string, mixed>|null
     */
    public function fetchOrderStatus(Order $order): ?array
    {
        if (! $order->midtrans_order_id) {
            return null;
        }

        return $this->transactionStatus($order->midtrans_order_id);
    }

    /**
     * Rincian item untuk payload Snap.
     *
     * @return array<int, array<string, mixed>>
     */
    private function itemDetails(Order $order): array
    {
        $items = $order->items->map(fn ($item) => [
            'id' => (string) ($item->course_id ?? $item->id),
            'price' => (int) $item->price,
            'quantity' => (int) $item->qty,
            'name' => mb_substr($item->title, 0, 50),
        ])->values()->all();

        // Jaga agar total item = total pesanan (Midtrans memvalidasi ini).
        $sum = array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $items));
        if ($sum !== (int) $order->total) {
            $items[] = [
                'id' => 'penyesuaian',
                'price' => (int) $order->total - $sum,
                'quantity' => 1,
                'name' => 'Penyesuaian',
            ];
        }

        return $items;
    }
}
