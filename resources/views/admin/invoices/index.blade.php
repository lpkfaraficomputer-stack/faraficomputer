@extends('layouts.admin')

@section('title', 'Tagihan Murid')

@section('content')
<div class="page-intro">
    <div>
        <h1>Tagihan Murid</h1>
        <p>{{ $invoices->total() }} tagihan — kelola dan pantau pembayaran murid.</p>
    </div>
    <div class="page-intro-actions">
        <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">+ Buat Tagihan</a>
    </div>
</div>

<section class="panel">
    <div class="panel-filters">
        <form method="GET" class="filter-inline">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari no. tagihan / judul / nama murid...">
            <select name="type" onchange="this.form.submit()">
                <option value="">Semua Jenis</option>
                @foreach($types as $value => $label)
                    <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
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
            <tr><th>No. Tagihan</th><th>Murid</th><th>Jenis</th><th>Nominal</th><th>Jatuh Tempo</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td>
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="cell-link cell-strong">{{ $invoice->invoice_number }}</a>
                        <br><small class="muted">{{ $invoice->title }}</small>
                    </td>
                    <td>{{ $invoice->user?->name ?? 'Murid terhapus' }}<br><small class="muted">{{ $invoice->user?->email }}</small></td>
                    <td><span class="chip">{{ $invoice->typeLabel() }}</span></td>
                    <td class="cell-strong">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
                    <td>
                        @if($invoice->due_date)
                            {{ $invoice->due_date->translatedFormat('d M Y') }}
                            @if($invoice->isOverdue())<br><small class="text-danger">Terlambat</small>@endif
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td><span class="status status-{{ $invoice->status }}">{{ $invoice->statusLabel() }}</span></td>
                    <td class="cell-actions">
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-outline btn-xs">Detail</a>
                        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-outline btn-xs">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="cell-empty">Belum ada tagihan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="panel-pagination">@include('partials.pagination', ['paginator' => $invoices])</div>
</section>
@endsection
