@extends('layouts.app')

@section('title', 'Akun Saya — Farafi Computer')

@section('content')
<div class="page-head page-head-sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>›</span> <span>Akun Saya</span>
        </nav>
        <h1>Halo, {{ auth()->user()->name }} 👋</h1>
        <p>Ini ringkasan pembelian kursus Anda.</p>
    </div>
</div>

<div class="container page-body page-body-single">
    <section>
        <div class="stat-grid stat-grid-3">
            <div class="stat-card"><span class="stat-icon">🧾</span><div><strong>{{ $orders->count() }}</strong><span>Total Pesanan</span></div></div>
            <div class="stat-card"><span class="stat-icon">📚</span><div><strong>{{ $orders->sum(fn ($o) => $o->items->sum('qty')) }}</strong><span>Kursus Dibeli</span></div></div>
            <div class="stat-card"><span class="stat-icon">💰</span><div><strong>Rp{{ number_format($totalSpent, 0, ',', '.') }}</strong><span>Total Transaksi</span></div></div>
        </div>

        <section class="panel" style="margin-top: 1.5rem">
            <div class="panel-head">
                <h2>Pesanan Saya</h2>
                <a href="{{ route('courses.index') }}" class="panel-link">Belanja lagi →</a>
            </div>

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
                            @if($order->status === 'selesai')
                                <a href="{{ route('orders.success', $order->order_number) }}" class="btn btn-outline btn-xs">📜 Lihat Sertifikat</a>
                            @endif
                            <span>Total: <strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong></span>
                        </div>
                    </div>
                @endforeach
            @endif
        </section>
    </section>

    <aside class="summary-box">
        <h3>Profil Saya</h3>
        <div class="summary-row"><span>Nama</span><span>{{ auth()->user()->name }}</span></div>
        <div class="summary-row"><span>Email</span><span>{{ auth()->user()->email }}</span></div>
        <div class="summary-row"><span>WhatsApp</span><span>{{ auth()->user()->phone ?? '-' }}</span></div>
        <div class="summary-row"><span>Role</span><span class="role-badge role-murid">Murid</span></div>
        <div class="summary-row"><span>Bergabung</span><span>{{ auth()->user()->created_at->translatedFormat('d M Y') }}</span></div>
        <form action="{{ route('logout') }}" method="POST" style="margin-top: 1rem">
            @csrf
            <button type="submit" class="btn btn-outline btn-block">Keluar dari Akun</button>
        </form>
    </aside>
</div>
@endsection
