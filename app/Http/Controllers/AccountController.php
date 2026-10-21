<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Dashboard murid: pesanan & kursus milik saya.
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        return view('account.dashboard', [
            'orders' => Order::with('items')
                ->where('user_id', $user->id)
                ->latest()
                ->get(),
            'totalSpent' => Order::where('user_id', $user->id)->sum('total'),
        ]);
    }
}
