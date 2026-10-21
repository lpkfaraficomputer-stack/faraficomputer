@extends('layouts.app')

@section('title', 'Pembayaran — Farafi Computer')

@section('content')
<div class="container">
    <div class="success-wrap pay-wrap">
        <div class="success-icon">💳</div>
        <h1>Selesaikan Pembayaran</h1>
        <p>Pesanan <strong>{{ $order->order_number }}</strong> menunggu pembayaran.
        Anda akan diarahkan ke halaman pembayaran Midtrans yang aman.</p>

        <div class="summary-box success-summary">
            <div class="summary-row"><span>Nama</span><span>{{ $order->name }}</span></div>
            <div class="summary-row"><span>Email</span><span>{{ $order->email }}</span></div>
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
            <button type="button" id="payButton" class="btn btn-primary btn-lg">Bayar Sekarang</button>
            <a href="{{ route('orders.success', $order->order_number) }}" class="btn btn-outline btn-lg">Bayar Nanti</a>
        </div>
        <p class="summary-note">🔒 Transaksi diproses oleh Midtrans. Farafi Computer tidak menyimpan data kartu Anda.</p>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
<script>
    const payButton = document.getElementById('payButton');

    payButton?.addEventListener('click', function () {
        payButton.disabled = true;
        payButton.textContent = 'Memuat…';

        window.snap.pay('{{ $order->midtrans_snap_token }}', {
            onSuccess: function () { window.location.href = '{{ route('midtrans.finish', ['order_id' => $order->order_number]) }}'; },
            onPending: function () { window.location.href = '{{ route('orders.success', $order->order_number) }}'; },
            onError: function () {
                payButton.disabled = false;
                payButton.textContent = 'Bayar Sekarang';
                alert('Pembayaran gagal. Silakan coba lagi.');
            },
            onClose: function () {
                payButton.disabled = false;
                payButton.textContent = 'Bayar Sekarang';
            }
        });
    });
</script>
@endpush
