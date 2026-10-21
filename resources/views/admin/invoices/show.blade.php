@extends('layouts.admin')

@section('title', 'Detail Tagihan')

@section('content')
<div class="page-intro">
    <div>
        <h1>{{ $invoice->invoice_number }}</h1>
        <p>Dibuat {{ $invoice->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
    </div>
    <div class="page-intro-actions">
        <a href="{{ route('admin.invoices.edit', $invoice) }}" class="btn btn-outline">Edit</a>
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-ghost">← Kembali</a>
    </div>
</div>

<div class="admin-grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Rincian Tagihan</h2></div>
        <dl class="detail-list">
            <dt>Judul</dt><dd>{{ $invoice->title }}</dd>
            <dt>Jenis</dt><dd>{{ $invoice->typeLabel() }}</dd>
            @if($invoice->description)
                <dt>Keterangan</dt><dd>{{ $invoice->description }}</dd>
            @endif
            <dt>Nominal</dt><dd class="cell-strong">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</dd>
            <dt>Jatuh Tempo</dt>
            <dd>
                @if($invoice->due_date)
                    {{ $invoice->due_date->translatedFormat('d M Y') }}
                    @if($invoice->isOverdue())<span class="status status-dibatalkan" style="margin-left:.4rem">Terlambat</span>@endif
                @else
                    <span class="muted">Tanpa jatuh tempo</span>
                @endif
            </dd>
            <dt>Status</dt><dd><span class="status status-{{ $invoice->status }}">{{ $invoice->statusLabel() }}</span></dd>
            <dt>Dibuat oleh</dt><dd>{{ $invoice->creator?->name ?? 'Sistem' }}</dd>

            @if($invoice->isPaid())
                <dt>Dibayar pada</dt><dd>{{ $invoice->paid_at?->translatedFormat('d M Y, H:i') }} WIB</dd>
                @if($invoice->payment_type)
                    <dt>Metode</dt><dd>{{ strtoupper(str_replace('_', ' ', $invoice->payment_type)) }}</dd>
                @endif
                @if($invoice->midtrans_transaction_id)
                    <dt>ID Transaksi</dt><dd>{{ $invoice->midtrans_transaction_id }}</dd>
                @endif
            @endif
        </dl>

        <div class="panel-foot panel-total">
            <span>Total Tagihan</span><strong>Rp{{ number_format($invoice->amount, 0, ',', '.') }}</strong>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Murid &amp; Aksi</h2></div>

        <dl class="detail-list">
            <dt>Nama</dt><dd>{{ $invoice->user?->name ?? '—' }}</dd>
            <dt>Email</dt><dd>{{ $invoice->user?->email ?? '—' }}</dd>
            <dt>WhatsApp</dt><dd>{{ $invoice->user?->phone ?? '—' }}</dd>
        </dl>

        <div class="status-form">
            <form action="{{ route('admin.invoices.status', $invoice) }}" method="POST">
                @csrf @method('PATCH')
                <div class="form-group">
                    <label for="status">Ubah Status</label>
                    <select id="status" name="status">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($invoice->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Simpan Status</button>
            </form>

            <form action="{{ route('admin.invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Hapus tagihan ini?')" style="margin-top:.8rem">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-block">Hapus Tagihan</button>
            </form>
        </div>
    </section>
</div>
@endsection
