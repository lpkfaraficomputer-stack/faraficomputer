@extends('layouts.app')

@section('title', 'Keranjang — Farafi Computer')

@section('content')
<div class="page-head page-head-sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>›</span> <span>Keranjang</span>
        </nav>
        <h1>Keranjang Belanja</h1>
    </div>
</div>

<div class="container page-body page-body-single">
    @if($items->isEmpty())
        <div class="empty-state">
            <span>🛒</span>
            <h3>Keranjang Anda masih kosong</h3>
            <p>Yuk pilih kursus komputer favoritmu dan mulai belajar hari ini!</p>
            <a href="{{ route('courses.index') }}" class="btn btn-primary btn-lg">Jelajahi Kursus</a>
        </div>
    @else
        <section class="cart-table-wrap">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Kursus</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td data-label="Kursus">
                                <div class="cart-item">
                                    <a href="{{ route('courses.show', $item['course']->slug) }}" class="cart-thumb" style="--cat-color: {{ $item['course']->category->color ?? '#4f46e5' }}">
                                        {{ $item['course']->category->icon ?? '💻' }}
                                    </a>
                                    <div>
                                        <a href="{{ route('courses.show', $item['course']->slug) }}" class="cart-item-title">{{ $item['course']->title }}</a>
                                        <span class="cart-item-meta">{{ $item['course']->category->name }} · {{ $item['course']->level }}</span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Harga">Rp{{ number_format($item['course']->final_price, 0, ',', '.') }}</td>
                            <td data-label="Jumlah">
                                <form action="{{ route('cart.update', $item['course']->id) }}" method="POST" class="qty-form">
                                    @csrf @method('PATCH')
                                    <div class="qty-control">
                                        <button type="submit" name="qty" value="{{ max(1, $item['qty'] - 1) }}">−</button>
                                        <span>{{ $item['qty'] }}</span>
                                        <button type="submit" name="qty" value="{{ $item['qty'] + 1 }}">+</button>
                                    </div>
                                </form>
                            </td>
                            <td data-label="Subtotal" class="cart-subtotal">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                            <td data-label="Aksi">
                                <form action="{{ route('cart.destroy', $item['course']->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="cart-remove" title="Hapus">✕</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="cart-actions">
                <a href="{{ route('courses.index') }}" class="btn btn-outline">← Lanjut Belanja</a>
                <form action="{{ route('cart.clear') }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-ghost">Kosongkan Keranjang</button>
                </form>
            </div>
        </section>

        <aside class="summary-box">
            <h3>Ringkasan Belanja</h3>
            <div class="summary-row"><span>Jumlah kursus</span><span>{{ $items->sum('qty') }}</span></div>
            <div class="summary-row"><span>Subtotal</span><span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div>
            <div class="summary-row"><span>Diskon promo</span><span>Rp0</span></div>
            <div class="summary-row summary-total"><span>Total</span><strong>Rp{{ number_format($subtotal, 0, ',', '.') }}</strong></div>
            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg btn-block">Lanjut ke Checkout →</a>
            <p class="summary-note">🔒 Pembayaran aman &amp; terenkripsi</p>
        </aside>
    @endif
</div>
@endsection
