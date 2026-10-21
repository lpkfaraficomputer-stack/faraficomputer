@extends('layouts.account')

@section('title', 'Kursus Saya')

@section('content')
<div class="page-intro">
    <div>
        <h1>Kursus Saya</h1>
        <p>Semua pesanan kursus yang pernah Anda buat.</p>
    </div>
    <a href="{{ route('courses.index') }}" class="btn btn-primary">+ Beli Kursus</a>
</div>

<section class="panel">
    <div class="panel-head"><h2>Riwayat Pesanan</h2></div>

    @if($orders->isEmpty())
        <div class="empty-state">
            <span>🧺</span>
            <h3>Belum ada pesanan</h3>
            <p>Yuk mulai belajar komputer hari ini!</p>
            <a href="{{ route('courses.index') }}" class="btn btn-primary">Jelajahi Kursus</a>
        </div>
    @else
        @foreach($orders as $order)
            <div class="my-order">
                <div class="my-order-head">
                    <div>
                        <strong>{{ $order->order_number }}</strong>
                        <small class="muted">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</small>
                    </div>
                    <span class="status status-{{ $order->status }}">{{ \App\Http\Controllers\Admin\AdminOrderController::STATUSES[$order->status] ?? $order->status }}</span>
                </div>
                <ul class="my-order-items">
                    @foreach($order->items as $item)
                        <li>
                            @if($item->course)
                                <a href="{{ route('courses.show', $item->course->slug) }}">{{ $item->title }}</a>
                            @else
                                {{ $item->title }}
                            @endif
                            <span>× {{ $item->qty }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="my-order-foot">
                    <a href="{{ route('account.orders.show', $order->order_number) }}" class="btn btn-outline btn-xs">Detail</a>
                    <span>Total: <strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong></span>
                </div>
            </div>
        @endforeach
    @endif
</section>
@endsection
