<?php

namespace App\Services;

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

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
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
            ];
        } catch (\Throwable $e) {
            Log::error('Midtrans Snap exception: '.$e->getMessage(), [
                'order' => $order->order_number,
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
