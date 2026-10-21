@extends('layouts.account')

@section('title', 'Detail Pesanan')

@section('content')
<div class="page-intro">
    <div>
        <h1>{{ $order->order_number }}</h1>
        <p>Dibuat {{ $order->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
    </div>
    <a href="{{ route('account.orders.index') }}" class="btn btn-ghost">← Kembali</a>
</div>

<div class="admin-grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Item Pesanan</h2></div>
        <table class="data-table">
            <thead><tr><th>Kursus</th><th>Harga</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td class="cell-strong">
                            @if($item->course)
                                <a href="{{ route('courses.show', $item->course->slug) }}" class="cell-link">{{ $item->title }}</a>
                            @else
                                {{ $item->title }}
                            @endif
                        </td>
                        <td>Rp{{ number_format($item->price, 0, ',', '.') }}</td>
                        <td>{{ $item->qty }}</td>
                        <td class="cell-strong">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="panel-foot panel-total">
            <span>Total</span><strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Status Pembayaran</h2></div>
        <dl class="detail-list">
            <dt>Status</dt><dd><span class="status status-{{ $order->status }}">{{ \App\Http\Controllers\Admin\AdminOrderController::STATUSES[$order->status] ?? $order->status }}</span></dd>
            <dt>Metode</dt><dd>
                @switch($order->payment_method)
                    @case('transfer') 🏦 Transfer Bank @break
                    @case('qris') 📱 QRIS @break
                    @default 👛 E-Wallet
                @endswitch
            </dd>
            @if($order->payment_type)
                <dt>Channel Midtrans</dt><dd>{{ strtoupper(str_replace('_', ' ', $order->payment_type)) }}</dd>
            @endif
            @if($order->paid_at)
                <dt>Dibayar pada</dt><dd>{{ $order->paid_at->translatedFormat('d M Y, H:i') }} WIB</dd>
            @endif
        </dl>

        @if($order->status === 'menunggu_pembayaran')
            <div class="status-form">
                <a href="{{ route('midtrans.pay', $order->order_number) }}" class="btn btn-primary btn-block">💳 Bayar Sekarang</a>
            </div>
        @endif
    </section>
</div>
@endsection
