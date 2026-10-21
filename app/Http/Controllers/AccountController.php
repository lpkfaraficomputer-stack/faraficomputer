<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Dashboard murid: ringkasan tagihan, pesanan & kursus milik saya.
     */
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $invoices = $user->invoices()->latest()->get();
        $orders = Order::with('items')->where('user_id', $user->id)->latest()->get();

        return view('account.dashboard', [
            'orders' => $orders,
            'totalSpent' => $orders->sum('total'),
            'invoices' => $invoices,
            'tagihanBelumDibayar' => $invoices->where('status', Invoice::STATUS_BELUM_DIBAYAR)->sum('amount'),
            'jumlahTagihanAktif' => $invoices->where('status', Invoice::STATUS_BELUM_DIBAYAR)->count(),
        ]);
    }

    /**
     * Halaman profil murid.
     */
    public function profile(Request $request): View
    {
        return view('account.profile', ['user' => $request->user()]);
    }

    /**
     * Simpan perubahan profil murid.
     */
    public function updateProfile(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $data = collect($validated)->except('password')->all();
        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
