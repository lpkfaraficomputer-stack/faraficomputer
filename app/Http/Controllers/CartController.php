<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function cart(): array
    {
        return session()->get('cart', []);
    }

    public function index()
    {
        $cart = $this->cart();
        $ids = array_keys($cart);

        $courses = Course::with('category')->whereIn('id', $ids)->get()->keyBy('id');

        $items = collect($cart)->map(function ($entry, $id) use ($courses) {
            $course = $courses->get($id);

            return $course ? [
                'course' => $course,
                'qty' => $entry['qty'],
                'subtotal' => $course->final_price * $entry['qty'],
            ] : null;
        })->filter()->values();

        return view('cart.index', [
            'items' => $items,
            'subtotal' => $items->sum('subtotal'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $course = Course::findOrFail($request->input('course_id'));

        $cart = $this->cart();
        $cart[$course->id] = ['qty' => ($cart[$course->id]['qty'] ?? 0) + 1];
        session()->put('cart', $cart);

        if ($request->input('redirect') === 'checkout') {
            return redirect()->route('checkout.index');
        }

        return back()->with('success', "\"{$course->title}\" ditambahkan ke keranjang.");
    }

    public function update(Request $request, int $courseId): RedirectResponse
    {
        $qty = max(1, (int) $request->input('qty', 1));

        $cart = $this->cart();
        if (isset($cart[$courseId])) {
            $cart[$courseId]['qty'] = $qty;
            session()->put('cart', $cart);
        }

        return back()->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(int $courseId): RedirectResponse
    {
        $cart = $this->cart();
        unset($cart[$courseId]);
        session()->put('cart', $cart);

        return back()->with('success', 'Kursus dihapus dari keranjang.');
    }

    public function clear(): RedirectResponse
    {
        session()->forget('cart');

        return redirect()->route('cart.index')->with('success', 'Keranjang dikosongkan.');
    }
}
