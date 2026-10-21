@extends('layouts.admin')

@section('title', 'Laporan Keuangan')

@section('content')
<div class="page-intro">
    <div>
        <h1>Laporan Keuangan</h1>
        <p>Rekap pendapatan, metode pembayaran, dan kursus terlaris · {{ $label }}</p>
    </div>
    <div class="page-intro-actions">
        <a href="{{ route('admin.reports.export', ['periode' => $period, 'dari' => $start->format('Y-m-d'), 'sampai' => $end->format('Y-m-d')]) }}"
           class="btn btn-outline">⬇️ Ekspor CSV</a>
        <button type="button" class="btn btn-ghost" onclick="window.print()">🖨️ Cetak</button>
    </div>
</div>

{{-- Filter periode --}}
<section class="panel report-filter">
    <form method="GET" class="report-tabs">
        <input type="hidden" name="periode" value="{{ $period }}">
        <div class="report-tab-row">
            <a href="{{ route('admin.reports.index', ['periode' => 'hari']) }}" class="report-tab {{ $period === 'hari' ? 'active' : '' }}">Harian</a>
            <a href="{{ route('admin.reports.index', ['periode' => 'bulan']) }}" class="report-tab {{ $period === 'bulan' ? 'active' : '' }}">Bulanan</a>
            <a href="{{ route('admin.reports.index', ['periode' => 'tahun']) }}" class="report-tab {{ $period === 'tahun' ? 'active' : '' }}">Tahunan</a>
            <a href="{{ route('admin.reports.index', ['periode' => 'kustom']) }}" class="report-tab {{ $period === 'kustom' ? 'active' : '' }}">Kustom</a>
        </div>

        @if($period === 'kustom')
            <div class="report-range">
                <label>Dari</label>
                <input type="date" name="dari" value="{{ $start->format('Y-m-d') }}">
                <label>Sampai</label>
                <input type="date" name="sampai" value="{{ $end->format('Y-m-d') }}">
                <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
            </div>
        @endif
    </form>
</section>

{{-- Ringkasan --}}
<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-icon">💰</span>
        <div><strong>Rp{{ number_format($summary['revenue'], 0, ',', '.') }}</strong><span>Pendapatan (dibayar + selesai)</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">🧾</span>
        <div><strong>{{ $summary['paid_count'] }}</strong><span>Pesanan Terbayar</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">📈</span>
        <div><strong>Rp{{ number_format($summary['avg'], 0, ',', '.') }}</strong><span>Rata-rata / Pesanan</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">🎬</span>
        <div><strong>{{ $summary['items'] }}</strong><span>Kursus Terjual</span></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">⏳</span>
        <div><strong>Rp{{ number_format($summary['outstanding'], 0, ',', '.') }}</strong><span>Belum Terbayar</span></div>
        @if($summary['pending'] > 0)<span class="stat-tag">{{ $summary['pending'] }} pesanan</span>@endif
    </div>
    <div class="stat-card">
        <span class="stat-icon">📊</span>
        <div><strong>{{ $summary['all_count'] }}</strong><span>Total Pesanan</span></div>
    </div>
</div>

{{-- Grafik tren --}}
<section class="panel">
    <div class="panel-head">
        <h2>Grafik Pendapatan</h2>
        <span class="panel-link">{{ $label }}</span>
    </div>
    <div class="report-chart">
        @php($max = $trend['max'])
        <div class="report-chart-bars">
            @foreach($trend['values'] as $i => $value)
                <div class="report-bar-wrap" title="{{ $trend['labels'][$i] }}: Rp{{ number_format($value, 0, ',', '.') }}">
                    <span class="report-bar-value">{{ $value > 0 ? number_format($value / 1000, 0, ',', '.') . 'rb' : '' }}</span>
                    <div class="report-bar" style="height: {{ max(2, (int) round($value / $max * 100)) }}%"></div>
                    <span class="report-bar-label">{{ $trend['labels'][$i] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

<div class="admin-grid-2">
    {{-- Metode pembayaran --}}
    <section class="panel">
        <div class="panel-head"><h2>Metode Pembayaran</h2></div>
        <div class="report-breakdown">
            @forelse($byMethod as $row)
                <div class="report-row">
                    <div class="report-row-top">
                        <span>{{ $row['label'] }}</span>
                        <span class="report-row-amount">Rp{{ number_format($row['revenue'], 0, ',', '.') }}</span>
                    </div>
                    <div class="report-progress"><span style="width: {{ $row['share'] }}%"></span></div>
                    <div class="report-row-sub">
                        <span>{{ $row['count'] }} pesanan</span>
                        <span>{{ $row['share'] }}%</span>
                    </div>
                </div>
            @empty
                <p class="cell-empty">Belum ada pembayaran pada periode ini.</p>
            @endforelse
        </div>
    </section>

    {{-- Status pesanan --}}
    <section class="panel">
        <div class="panel-head"><h2>Status Pesanan</h2></div>
        <div class="report-breakdown">
            @foreach($byStatus as $row)
                <div class="report-row">
                    <div class="report-row-top">
                        <span><span class="status status-{{ $row['status'] }}">{{ $row['label'] }}</span></span>
                        <span class="report-row-amount">Rp{{ number_format($row['revenue'], 0, ',', '.') }}</span>
                    </div>
                    <div class="report-row-sub">
                        <span>{{ $row['count'] }} pesanan</span>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>

{{-- Kursus terlaris --}}
<section class="panel">
    <div class="panel-head">
        <h2>Kursus Terlaris</h2>
        <span class="panel-link">{{ $label }}</span>
    </div>
    <table class="data-table">
        <thead>
            <tr><th>#</th><th>Kursus</th><th>Terjual</th><th>Pendapatan</th></tr>
        </thead>
        <tbody>
            @forelse($topCourses as $i => $row)
                <tr>
                    <td class="cell-strong">{{ $i + 1 }}</td>
                    <td>
                        @if($row['course_id'])
                            <a href="{{ route('admin.courses.edit', $row['course_id']) }}" class="cell-link">{{ $row['title'] }}</a>
                        @else
                            {{ $row['title'] }}
                        @endif
                    </td>
                    <td>{{ $row['sold'] }} item</td>
                    <td class="cell-strong">Rp{{ number_format($row['revenue'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="cell-empty">Belum ada penjualan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>

{{-- Rincian transaksi --}}
<section class="panel">
    <div class="panel-head">
        <h2>Rincian Transaksi Terbayar</h2>
        <span class="panel-link">{{ $orders->total() }} transaksi</span>
    </div>
    <table class="data-table">
        <thead>
            <tr><th>No. Pesanan</th><th>Pembeli</th><th>Metode</th><th>Total</th><th>Status</th><th>Tanggal</th></tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $order) }}" class="cell-link cell-strong">{{ $order->order_number }}</a></td>
                    <td>{{ $order->name }}<br><small class="muted">{{ $order->email }}</small></td>
                    <td>{{ ['transfer' => '🏦 Transfer', 'qris' => '📱 QRIS', 'ewallet' => '👛 E-Wallet'][$order->payment_method] ?? $order->payment_method }}</td>
                    <td class="cell-strong">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    <td><span class="status status-{{ $order->status }}">{{ $statuses[$order->status] ?? $order->status }}</span></td>
                    <td><small class="muted">{{ $order->created_at->translatedFormat('d M Y H:i') }}</small></td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty">Belum ada transaksi terbayar pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="panel-pagination">@include('partials.pagination', ['paginator' => $orders])</div>
</section>
@endsection
