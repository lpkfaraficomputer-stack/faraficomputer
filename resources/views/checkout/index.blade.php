@extends('layouts.app')

@section('title', 'Checkout — Farafi Computer')

@section('content')
<div class="page-head page-head-sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>›</span>
            <a href="{{ route('cart.index') }}">Keranjang</a> <span>›</span> <span>Checkout</span>
        </nav>
        <h1>Checkout</h1>
    </div>
</div>

<div class="container page-body page-body-single">
    <section class="course-panel">
        <form action="{{ route('checkout.store') }}" method="POST" class="checkout-form">
            @csrf

            <h2>📝 Data Pembeli</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Nama Lengkap *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso">
                    @error('name')<small class="form-error">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com">
                    @error('email')<small class="form-error">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="phone">No. WhatsApp *</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx">
                    @error('phone')<small class="form-error">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="notes">Catatan (opsional)</label>
                    <input type="text" id="notes" name="notes" value="{{ old('notes') }}" placeholder="Kode promo / permintaan khusus">
                </div>
            </div>

            <h2>💳 Metode Pembayaran</h2>
            <div class="payment-grid">
                @php
                    $payments = [
                        'transfer' => ['icon' => '🏦', 'title' => 'Transfer Bank', 'desc' => 'BCA / Mandiri / BNI / BRI'],
                        'qris' => ['icon' => '📱', 'title' => 'QRIS', 'desc' => 'Semua e-wallet & m-banking'],
                        'ewallet' => ['icon' => '👛', 'title' => 'E-Wallet', 'desc' => 'GoPay / OVO / DANA / ShopeePay'],
                    ];
                @endphp
                @foreach($payments as $value => $meta)
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="{{ $value }}" @checked(old('payment_method', 'transfer') === $value)>
                        <span class="payment-card">
                            <span class="payment-icon">{{ $meta['icon'] }}</span>
                            <strong>{{ $meta['title'] }}</strong>
                            <small>{{ $meta['desc'] }}</small>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('payment_method')<small class="form-error">{{ $message }}</small>@enderror

            <button type="submit" class="btn btn-primary btn-lg btn-block">Buat Pesanan &amp; Bayar</button>
            <p class="summary-note">Dengan menekan tombol di atas, Anda menyetujui syarat &amp; ketentuan Farafi Computer.</p>
        </form>
    </section>

    <aside class="summary-box">
        <h3>Ringkasan Pesanan</h3>
        @foreach($items as $item)
            <div class="order-line">
                <span class="order-line-thumb" style="--cat-color: {{ $item['course']->category->color ?? '#4f46e5' }}">{{ $item['course']->category->icon ?? '💻' }}</span>
                <div>
                    <strong>{{ $item['course']->title }}</strong>
                    <small>{{ $item['qty'] }} × Rp{{ number_format($item['course']->final_price, 0, ',', '.') }}</small>
                </div>
                <span>Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
            </div>
        @endforeach
        <div class="summary-row"><span>Subtotal</span><span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div>
        <div class="summary-row"><span>Biaya layanan</span><span>Rp0</span></div>
        <div class="summary-row summary-total"><span>Total</span><strong>Rp{{ number_format($subtotal, 0, ',', '.') }}</strong></div>
        <p class="summary-note">🔒 Data Anda aman dan terlindungi.</p>
    </aside>
</div>
@endsection
