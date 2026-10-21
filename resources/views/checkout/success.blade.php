@extends('layouts.app')

@section('title', 'Pesanan Berhasil — Farafi Computer')

@section('content')
<div class="container">
    <div class="success-wrap">
        <div class="success-icon">✅</div>
        <h1>Pesanan Berhasil Dibuat!</h1>
        <p>Terima kasih, <strong>{{ $order->name }}</strong>. Pesanan Anda sudah kami terima.<br>
        Instruksi pembayaran telah dikirim ke <strong>{{ $order->email }}</strong>.</p>

        <div class="success-order-no">
            Nomor Pesanan: <strong>{{ $order->order_number }}</strong>
        </div>

        <div class="summary-box success-summary">
            <div class="summary-row">
                <span>Status</span>
                <span class="status-badge">{{ str_replace('_', ' ', ucfirst($order->status)) }}</span>
            </div>
            <div class="summary-row"><span>Metode Pembayaran</span>
                <span>
                    @switch($order->payment_method)
                        @case('transfer') 🏦 Transfer Bank @break
                        @case('qris') 📱 QRIS @break
                        @default 👛 E-Wallet
                    @endswitch
                </span>
            </div>
            @if($order->payment_type)
                <div class="summary-row"><span>Channel Midtrans</span><span>{{ strtoupper(str_replace('_', ' ', $order->payment_type)) }}</span></div>
            @endif
            @if($order->paid_at)
                <div class="summary-row"><span>Dibayar pada</span><span>{{ $order->paid_at->translatedFormat('d M Y, H:i') }} WIB</span></div>
            @endif

            @foreach($order->items as $item)
                <div class="order-line">
                    <div>
                        <strong>{{ $item->title }}</strong>
                        <small>{{ $item->qty }} × Rp{{ number_format($item->price, 0, ',', '.') }}</small>
                    </div>
                    <span>Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
            @endforeach

            <div class="summary-row summary-total"><span>Total</span><strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong></div>
        </div>

        <div class="success-actions">
            @if($order->status === 'menunggu_pembayaran' && config('midtrans.server_key'))
                <a href="{{ route('midtrans.pay', $order->order_number) }}" class="btn btn-primary btn-lg">💳 Bayar Sekarang</a>
            @elseif($order->status === 'menunggu_pembayaran')
                <a href="https://wa.me/{{ config('services.whatsapp', '6281234567890') }}?text={{ urlencode('Konfirmasi pembayaran pesanan '.$order->order_number) }}"
                   target="_blank" class="btn btn-primary btn-lg">💬 Konfirmasi via WhatsApp</a>
            @endif
            <a href="{{ route('courses.index') }}" class="btn btn-outline btn-lg">Lanjut Belanja Kursus</a>
            <a href="{{ route('home') }}" class="btn btn-outline btn-lg">Kembali ke Beranda</a>
        </div>
    </div>
</div>
@endsection
