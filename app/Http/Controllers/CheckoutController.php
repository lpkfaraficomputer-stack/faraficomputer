<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $items = $this->resolveItems($cart);

        return view('checkout.index', [
            'items' => $items,
            'subtotal' => $items->sum('subtotal'),
            'buyer' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'payment_method' => ['required', 'in:transfer,qris,ewallet'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $items = $this->resolveItems($cart);
        $subtotal = $items->sum('subtotal');

        $order = DB::transaction(function () use ($validated, $items, $subtotal, $request) {
            $order = Order::create([
                ...$validated,
                'user_id' => $request->user()?->id,
                'order_number' => 'FRC-'.now()->format('ymd').'-'.strtoupper(substr(uniqid(), -5)),
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'status' => 'menunggu_pembayaran',
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'course_id' => $item['course']->id,
                    'title' => $item['course']->title,
                    'price' => $item['course']->final_price,
                    'qty' => $item['qty'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            return $order;
        });

        session()->forget('cart');

        // Arahkan ke halaman pembayaran Midtrans bila gateway aktif.
        if (app(\App\Services\MidtransService::class)->isConfigured()) {
            return redirect()->route('midtrans.pay', $order->order_number)
                ->with('success', 'Pesanan berhasil dibuat! Silakan selesaikan pembayaran.');
        }

        return redirect()->route('orders.success', $order->order_number)
            ->with('success', 'Pesanan berhasil dibuat!');
    }

    public function success(Request $request, string $orderNumber): View|RedirectResponse
    {
        $order = Order::with('items')->where('order_number', $orderNumber)->first();

        if (! $order) {
            return redirect()->route('home')->with('error', 'Pesanan tidak ditemukan.');
        }

        // Pesanan milik murid tertentu hanya boleh dilihat pemiliknya
        if ($order->user_id && (! $request->user() || $request->user()->id !== $order->user_id)) {
            return redirect()->route('home')->with('error', 'Anda tidak punya akses ke pesanan tersebut.');
        }

        return view('checkout.success', ['order' => $order]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{course: Course, qty: int, subtotal: int}>
     */
    private function resolveItems(array $cart): \Illuminate\Support\Collection
    {
        $courses = Course::with('category')->whereIn('id', array_keys($cart))->get()->keyBy('id');

        return $courses->map(function (Course $course, $id) use ($cart) {
            $qty = $cart[$id]['qty'] ?? 1;

            return [
                'course' => $course,
                'qty' => $qty,
                'subtotal' => $course->final_price * $qty,
            ];
        })->values();
    }
}
