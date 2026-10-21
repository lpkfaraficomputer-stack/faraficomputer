@extends('layouts.account')

@section('title', 'Tagihan Saya')

@section('content')
<div class="page-intro">
    <div>
        <h1>Tagihan Saya</h1>
        <p>Daftar tagihan yang ditujukan untuk Anda. Bayar langsung di aplikasi via QRIS.</p>
    </div>
</div>

<div class="stat-grid stat-grid-3">
    <div class="stat-card"><span class="stat-icon">🧾</span><div><strong>{{ $tagihanAktif }}</strong><span>Tagihan Aktif</span></div></div>
    <div class="stat-card"><span class="stat-icon">⏳</span><div><strong>Rp{{ number_format($totalTagihan, 0, ',', '.') }}</strong><span>Belum Dibayar</span></div></div>
    <div class="stat-card"><span class="stat-icon">✅</span><div><strong>Rp{{ number_format($totalDibayar, 0, ',', '.') }}</strong><span>Sudah Dibayar</span></div></div>
</div>

<section class="panel">
    <div class="panel-head"><h2>Semua Tagihan</h2></div>

    @if($invoices->isEmpty())
        <div class="empty-state">
            <span>🎉</span>
            <h3>Tidak ada tagihan</h3>
            <p>Anda tidak memiliki tagihan saat ini.</p>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr><th>No. Tagihan</th><th>Jenis</th><th>Jatuh Tempo</th><th>Nominal</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                    <tr>
                        <td class="cell-strong">
                            <a href="{{ route('account.invoices.show', $invoice) }}" class="cell-link">{{ $invoice->invoice_number }}</a>
                            <br><small class="muted">{{ $invoice->title }}</small>
                        </td>
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
                        <td>
                            <span class="status status-{{ $invoice->status }}">{{ $invoice->statusLabel() }}</span>
                            @if($invoice->isPaid() && $invoice->paid_at)
                                <br><small class="muted">{{ $invoice->paid_at->translatedFormat('d M Y H:i') }}</small>
                            @endif
                        </td>
                        <td class="cell-actions">
                            <a href="{{ route('account.invoices.show', $invoice) }}" class="btn btn-outline btn-xs">Detail</a>
                            @if($invoice->isPayable())
                                <a href="{{ route('account.invoices.show', $invoice) }}" class="btn btn-primary btn-xs">Bayar</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
