@extends('layouts.admin')

@section('title', 'Detail Pesanan')

@section('content')
<div class="page-intro">
    <div>
        <h1>{{ $order->order_number }}</h1>
        <p>Dibuat {{ $order->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">← Kembali</a>
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
                            {{ $item->title }}
                            @if($item->course)
                                <br><small class="muted"><a class="cell-link" href="{{ route('courses.show', $item->course->slug) }}" target="_blank">Lihat di toko →</a></small>
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
        <div class="panel-head"><h2>Pembeli &amp; Status</h2></div>

        <dl class="detail-list">
            <dt>Nama</dt><dd>{{ $order->name }}</dd>
            <dt>Email</dt><dd>{{ $order->email }}</dd>
            <dt>No. WhatsApp</dt><dd>{{ $order->phone }}</dd>
            <dt>Pembayaran</dt><dd>{{ ['transfer' => '🏦 Transfer Bank', 'qris' => '📱 QRIS', 'ewallet' => '👛 E-Wallet'][$order->payment_method] ?? $order->payment_method }}</dd>
            @if($order->user_id)
                <dt>Akun</dt><dd>{{ $order->user?->name ?? 'Akun terhapus' }} <span class="role-badge role-{{ $order->user?->role }}">{{ $order->user?->roleLabel() }}</span></dd>
            @else
                <dt>Akun</dt><dd><span class="muted">Pesanan tamu (tanpa akun)</span></dd>
            @endif
            @if($order->notes)
                <dt>Catatan</dt><dd>{{ $order->notes }}</dd>
            @endif
            <dt>Status</dt><dd><span class="status status-{{ $order->status }}">{{ $statuses[$order->status] ?? $order->status }}</span></dd>
        </dl>

        <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="status-form">
            @csrf @method('PATCH')
            <div class="form-group">
                <label for="status">Ubah Status</label>
                <select id="status" name="status">
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Simpan Status</button>
        </form>
    </section>
</div>
@endsection
