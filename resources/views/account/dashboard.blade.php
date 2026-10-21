@extends('layouts.account')

@section('title', 'Dashboard')

@section('content')
<div class="page-intro">
    <div>
        <h1>Halo, {{ auth()->user()->name }} 👋</h1>
        <p>Selamat datang di area murid Farafi Computer.</p>
    </div>
    <div class="page-intro-actions">
        @if($jumlahTagihanAktif > 0)
            <a href="{{ route('account.invoices.index') }}" class="btn btn-primary">🧾 Bayar Tagihan</a>
        @endif
        <a href="{{ route('courses.index') }}" class="btn btn-outline">+ Beli Kursus</a>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-icon">🧾</span>
        <div><strong>{{ $jumlahTagihanAktif }}</strong><span>Tagihan Belum Dibayar</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">💰</span>
        <div><strong>Rp{{ number_format($tagihanBelumDibayar, 0, ',', '.') }}</strong><span>Total Tagihan Aktif</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">📚</span>
        <div><strong>{{ $orders->sum(fn ($o) => $o->items->sum('qty')) }}</strong><span>Kursus Dibeli</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">💳</span>
        <div><strong>Rp{{ number_format($totalSpent, 0, ',', '.') }}</strong><span>Total Transaksi Kursus</span></div>
    </div>
</div>

@if($invoices->where('status', \App\Models\Invoice::STATUS_BELUM_DIBAYAR)->isNotEmpty())
    <section class="panel">
        <div class="panel-head">
            <h2>Tagihan yang Perlu Dibayar</h2>
            <a href="{{ route('account.invoices.index') }}" class="panel-link">Lihat semua tagihan →</a>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>No. Tagihan</th><th>Jenis</th><th>Jatuh Tempo</th><th>Nominal</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($invoices->where('status', \App\Models\Invoice::STATUS_BELUM_DIBAYAR)->take(4) as $invoice)
                    <tr>
                        <td class="cell-strong">{{ $invoice->invoice_number }}<br><small class="muted">{{ $invoice->title }}</small></td>
                        <td><span class="chip">{{ $invoice->typeLabel() }}</span></td>
                        <td>
                            @if($invoice->due_date)
                                {{ $invoice->due_date->translatedFormat('d M Y') }}
                                @if($invoice->isOverdue())<br><small class="text-danger">Terlambat</small>@endif
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>
                        <td class="cell-strong">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
                        <td class="cell-actions">
                            <a href="{{ route('account.invoices.show', $invoice) }}" class="btn btn-primary btn-xs">Bayar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endif

<section class="panel">
    <div class="panel-head">
        <h2>Pesanan Kursus Terbaru</h2>
        <a href="{{ route('account.orders.index') }}" class="panel-link">Lihat semua →</a>
    </div>

    @if($orders->isEmpty())
        <div class="empty-state">
            <span>🧺</span>
            <h3>Belum ada pesanan kursus</h3>
            <p>Yuk mulai belajar komputer hari ini!</p>
            <a href="{{ route('courses.index') }}" class="btn btn-primary">Jelajahi Kursus</a>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr><th>No. Pesanan</th><th>Item</th><th>Total</th><th>Status</th><th>Tanggal</th></tr>
            </thead>
            <tbody>
                @foreach($orders->take(5) as $order)
                    <tr>
                        <td><a href="{{ route('account.orders.show', $order->order_number) }}" class="cell-link cell-strong">{{ $order->order_number }}</a></td>
                        <td>{{ $order->items->sum('qty') }} kursus</td>
                        <td class="cell-strong">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                        <td><span class="status status-{{ $order->status }}">{{ \App\Http\Controllers\Admin\AdminOrderController::STATUSES[$order->status] ?? $order->status }}</span></td>
                        <td><small class="muted">{{ $order->created_at->translatedFormat('d M Y H:i') }}</small></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
