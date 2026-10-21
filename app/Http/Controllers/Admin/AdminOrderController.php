<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MidtransController;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminOrderController extends Controller
{
    public function __construct(private readonly MidtransService $midtrans) {}

    public const STATUSES = [
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'dibayar' => 'Dibayar',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
    ];

    public function index(Request $request): View
    {
        $query = Order::latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(fn ($sub) => $sub
                ->where('order_number', 'like', "%{$q}%")
                ->orWhere('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%"));
        }

        // Jaring pengaman: segarkan status pesanan yang belum dibayar dari
        // Midtrans agar status berubah otomatis tanpa konfirmasi manual admin.
        $orders = $query->paginate(10)->withQueryString();
        $this->reconcile($orders->getCollection());

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => self::STATUSES,
            'statusCounts' => Order::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Order $order): View
    {
        $this->reconcile(collect([$order]));

        return view('admin.orders.show', [
            'order' => $order->load('items.course', 'user'),
            'statuses' => self::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);

        $order->update($validated);

        $label = self::STATUSES[$validated['status']];

        return back()->with('success', "Status pesanan {$order->order_number} diubah menjadi \"{$label}\".");
    }

    /**
     * Segarkan status pesanan yang belum dibayar langsung dari Midtrans.
     *
     * Jaring pengaman bila webhook tidak sampai ke server. Dengan begitu, begitu
     * murid menyelesaikan pembayaran, status di sisi admin/operator otomatis
     * menjadi "Dibayar" tanpa perlu konfirmasi manual.
     *
     * @param  \Illuminate\Support\Collection<int, Order>  $orders
     */
    private function reconcile(\Illuminate\Support\Collection $orders): void
    {
        if (! $this->midtrans->isConfigured()) {
            return;
        }

        $midtransController = app(MidtransController::class);

        foreach ($orders as $order) {
            // Hanya pesanan yang masih menunggu pembayaran yang perlu dicek.
            if ($order->status !== 'menunggu_pembayaran' || ! $order->midtrans_order_id) {
                continue;
            }

            $status = $this->midtrans->transactionStatus($order->midtrans_order_id);

            if (! $status) {
                continue;
            }

            try {
                $midtransController->applyStatus($order, $status);
            } catch (\Throwable $e) {
                Log::warning('Gagal menyegarkan status pesanan dari Midtrans.', [
                    'order' => $order->order_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
