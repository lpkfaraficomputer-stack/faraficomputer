<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function __construct(private readonly MidtransService $midtrans) {}

    /**
     * Halaman pembayaran: memuat Snap lalu menampilkan popup/redirect.
     */
    public function pay(string $orderNumber)
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->first();

        if (! $order) {
            return redirect()->route('home')->with('error', 'Pesanan tidak ditemukan.');
        }

        // Pesanan milik murid tertentu hanya boleh dibayar pemiliknya.
        if ($order->user_id && (! auth()->check() || auth()->id() !== $order->user_id)) {
            return redirect()->route('home')->with('error', 'Anda tidak punya akses ke pesanan tersebut.');
        }

        if ($order->status !== 'menunggu_pembayaran') {
            return redirect()->route('orders.success', $order->order_number);
        }

        if (! $this->midtrans->isConfigured()) {
            return redirect()->route('orders.success', $order->order_number)
                ->with('error', 'Payment gateway belum dikonfigurasi. Hubungi admin Farafi Computer.');
        }

        // Buat token bila belum ada (atau sudah kedaluwarsa).
        if (! $order->midtrans_snap_token) {
            $snap = $this->midtrans->createSnapToken($order->load('items'));

            if (! $snap) {
                return redirect()->route('orders.success', $order->order_number)
                    ->with('error', 'Gagal memulai pembayaran. Silakan coba lagi.');
            }

            $order->update([
                'payment_provider' => 'midtrans',
                'midtrans_order_id' => $order->order_number,
                'midtrans_snap_token' => $snap['token'],
            ]);
        }

        return view('checkout.pay', [
            'order' => $order->fresh('items'),
            'clientKey' => config('midtrans.client_key'),
            'snapJsUrl' => config('midtrans.snap_js_url'),
        ]);
    }

    /**
     * Halaman setelah pembayaran selesai di Snap (callback).
     */
    public function finish(Request $request)
    {
        $orderNumber = $request->query('order_id') ?? $request->query('order_number');

        if ($orderNumber && $order = Order::where('order_number', $orderNumber)->first()) {
            // Segarkan status langsung dari Midtrans (jaga-jaga bila webhook belum tiba).
            $this->syncFromMidtrans($order);

            return redirect()->route('orders.success', $order->order_number);
        }

        return redirect()->route('home')->with('success', 'Pembayaran selesai.');
    }

    /**
     * Webhook notifikasi dari Midtrans.
     */
    public function notification(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans notification diterima.', $payload);

        $orderNumber = $payload['order_id'] ?? null;
        $order = $orderNumber ? Order::where('order_number', $orderNumber)->first() : null;

        if (! $order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // Jangan percaya payload mentah — verifikasi ke API Midtrans.
        $status = $this->midtrans->transactionStatus($orderNumber);

        if (! $status) {
            return response()->json(['message' => 'Gagal verifikasi status.'], 502);
        }

        // Pastikan nominal cocok untuk mencegah manipulasi.
        $gross = (int) ($status['gross_amount'] ?? 0);
        if ($gross > 0 && $gross !== (int) $order->total) {
            Log::warning('Midtrans nominal tidak cocok.', [
                'order' => $order->order_number,
                'expected' => $order->total,
                'received' => $gross,
            ]);

            return response()->json(['message' => 'Nominal tidak cocok.'], 422);
        }

        $this->applyStatus($order, $status);

        return response()->json(['message' => 'OK']);
    }

    /**
     * Tarik status terkini dari Midtrans lalu simpan.
     */
    private function syncFromMidtrans(Order $order): void
    {
        if (! $order->midtrans_order_id || ! $this->midtrans->isConfigured()) {
            return;
        }

        if ($status = $this->midtrans->transactionStatus($order->midtrans_order_id)) {
            $this->applyStatus($order, $status);
        }
    }

    /**
     * @param  array<string, mixed>  $status
     */
    private function applyStatus(Order $order, array $status): void
    {
        $transactionStatus = $status['transaction_status'] ?? '';
        $fraudStatus = $status['fraud_status'] ?? null;
        $mapped = $this->midtrans->mapOrderStatus($transactionStatus, $fraudStatus);

        $updates = [
            'payment_type' => $status['payment_type'] ?? $order->payment_type,
            'payment_status' => $transactionStatus ?: $order->payment_status,
            'midtrans_transaction_id' => $status['transaction_id'] ?? $order->midtrans_transaction_id,
            'payment_payload' => $status,
        ];

        if ($mapped === 'dibayar' && ! $order->paid_at) {
            $updates['paid_at'] = now();
        }

        if ($mapped) {
            $updates['status'] = $mapped;
        }

        $order->update($updates);
    }
}
