@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="page-intro">
    <div>
        <h1>Halo, {{ auth()->user()->name }} 👋</h1>
        <p>Ringkasan aktivitas Farafi Computer hari ini.</p>
    </div>
    <div class="page-intro-actions">
        @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.users.create') }}" class="btn btn-outline">+ Pengguna</a>
        @endif
        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">+ Kursus Baru</a>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card"><span class="stat-icon">💰</span><div><strong>Rp{{ number_format($stats['revenue'], 0, ',', '.') }}</strong><span>Total Pendapatan</span></div></div>
    <div class="stat-card"><span class="stat-icon">🧾</span><div><strong>{{ $stats['orders'] }}</strong><span>Total Pesanan</span></div></div>
    <div class="stat-card"><span class="stat-icon">⏳</span><div><strong>{{ $stats['pending'] }}</strong><span>Menunggu Bayar</span></div></div>
    <div class="stat-card"><span class="stat-icon">📚</span><div><strong>{{ $stats['courses'] }}</strong><span>Kursus Aktif</span></div></div>
    <div class="stat-card"><span class="stat-icon">🗂️</span><div><strong>{{ $stats['categories'] }}</strong><span>Kategori</span></div></div>
    <div class="stat-card">
        <span class="stat-icon">🧰</span>
        <div><strong>{{ $stats['service_requests'] }}</strong><span>Permohonan Layanan</span></div>
        @if($stats['service_new'] > 0)<span class="stat-tag">{{ $stats['service_new'] }} baru</span>@endif
    </div>
</div>

<section class="panel">
    <div class="panel-head">
        <h2>Ringkasan Pengguna</h2>
        <a href="{{ route('admin.users.index') }}" class="panel-link">Kelola pengguna →</a>
    </div>
    <div class="role-summary">
        <div class="role-sum-cell">
            <span class="role-badge role-super_admin">Super Admin</span>
            <strong>{{ $roleCounts['super_admin'] ?? 0 }}</strong>
        </div>
        <div class="role-sum-cell">
            <span class="role-badge role-operator">Operator</span>
            <strong>{{ $roleCounts['operator'] ?? 0 }}</strong>
        </div>
        <div class="role-sum-cell">
            <span class="role-badge role-murid">Murid</span>
            <strong>{{ $roleCounts['murid'] ?? 0 }}</strong>
        </div>
    </div>
</section>

<div class="admin-grid-2">
    <section class="panel">
        <div class="panel-head">
            <h2>Permohonan Layanan Terbaru</h2>
            <a href="{{ route('admin.services.index') }}" class="panel-link">Lihat semua →</a>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>No. Tiket</th><th>Layanan</th><th>Pemohon</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($recentServices as $req)
                    <tr>
                        <td><a href="{{ route('admin.services.show', $req) }}" class="cell-link">{{ $req->ticket_number }}</a></td>
                        <td>{{ \App\Models\ServiceRequest::services()[$req->service_type]['icon'] ?? '🧰' }} {{ $req->service_name }}</td>
                        <td>{{ $req->name }}<br><small class="muted">{{ $req->institution ?? $req->email }}</small></td>
                        <td><span class="status status-service-{{ $req->status }}">{{ \App\Models\ServiceRequest::STATUSES[$req->status] ?? $req->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="cell-empty">Belum ada permohonan layanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>

<div class="admin-grid-2">
    <section class="panel">
        <div class="panel-head">
            <h2>Pesanan Terbaru</h2>
            <a href="{{ route('admin.orders.index') }}" class="panel-link">Lihat semua →</a>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>No. Pesanan</th><th>Pembeli</th><th>Total</th><th>Status</th></tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="cell-link">{{ $order->order_number }}</a></td>
                        <td>{{ $order->name }}<br><small class="muted">{{ $order->email }}</small></td>
                        <td class="cell-strong">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                        <td><span class="status status-{{ $order->status }}">{{ \App\Http\Controllers\Admin\AdminOrderController::STATUSES[$order->status] ?? $order->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="cell-empty">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h2>Kursus Paling Laris</h2>
            <a href="{{ route('admin.courses.index') }}" class="panel-link">Kelola kursus →</a>
        </div>
        <table class="data-table">
            <thead>
                <tr><th>Kursus</th><th>Kategori</th><th>Terjual</th></tr>
            </thead>
            <tbody>
                @forelse($popularCourses as $course)
                    <tr>
                        <td><a href="{{ route('admin.courses.edit', $course) }}" class="cell-link">{{ $course->title }}</a></td>
                        <td><span class="chip" style="--chip-color: {{ $course->category->color }}">{{ $course->category->name }}</span></td>
                        <td class="cell-strong">{{ $course->items_count }} item</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="cell-empty">Belum ada penjualan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
