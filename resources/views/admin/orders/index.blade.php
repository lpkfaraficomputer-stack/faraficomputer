@extends('layouts.admin')

@section('title', 'Pesanan')

@section('content')
<div class="page-intro">
    <div>
        <h1>Pesanan</h1>
        <p>{{ $orders->total() }} pesanan masuk — kelola status pembayarannya.</p>
    </div>
</div>

<section class="panel">
    <div class="panel-filters">
        <form method="GET" class="filter-inline">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari no. pesanan / nama / email...">
            <select name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>
                        {{ $label }} ({{ $statusCounts[$value] ?? 0 }})
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr><th>No. Pesanan</th><th>Pembeli</th><th>Item</th><th>Total</th><th>Pembayaran</th><th>Status</th><th>Tanggal</th></tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $order) }}" class="cell-link cell-strong">{{ $order->order_number }}</a></td>
                    <td>{{ $order->name }}<br><small class="muted">{{ $order->email }}</small></td>
                    <td>{{ $order->items()->count() }} kursus</td>
                    <td class="cell-strong">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    <td>{{ ['transfer' => '🏦 Transfer', 'qris' => '📱 QRIS', 'ewallet' => '👛 E-Wallet'][$order->payment_method] ?? $order->payment_method }}</td>
                    <td><span class="status status-{{ $order->status }}">{{ $statuses[$order->status] ?? $order->status }}</span></td>
                    <td><small class="muted">{{ $order->created_at->translatedFormat('d M Y H:i') }}</small></td>
                </tr>
            @empty
                <tr><td colspan="7" class="cell-empty">Belum ada pesanan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="panel-pagination">@include('partials.pagination', ['paginator' => $orders])</div>
</section>
@endsection
