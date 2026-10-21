<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountOrderController extends Controller
{
    /**
     * Daftar pesanan/kursus milik murid.
     */
    public function index(Request $request): View
    {
        $orders = Order::with('items.course')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('account.orders.index', ['orders' => $orders]);
    }

    /**
     * Detail satu pesanan milik murid.
     */
    public function show(Request $request, string $orderNumber): View|\Illuminate\Http\RedirectResponse
    {
        $order = Order::with('items.course')
            ->where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $order) {
            return redirect()->route('account.orders.index')
                ->with('error', 'Pesanan tidak ditemukan.');
        }

        return view('account.orders.show', ['order' => $order]);
    }
}
