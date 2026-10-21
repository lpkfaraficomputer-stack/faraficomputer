@extends('layouts.account')

@section('title', 'Detail Tagihan')

@section('content')
<div class="page-intro">
    <div>
        <h1>{{ $invoice->invoice_number }}</h1>
        <p>Dibuat {{ $invoice->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
    </div>
    <a href="{{ route('account.invoices.index') }}" class="btn btn-ghost">← Kembali</a>
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
            <dt>Jatuh Tempo</dt>
            <dd>
                @if($invoice->due_date)
                    {{ $invoice->due_date->translatedFormat('d M Y') }}
                    @if($invoice->isOverdue())<span class="status status-dibatalkan" style="margin-left:.4rem">Terlambat</span>@endif
                @else
                    <span class="muted">Tanpa jatuh tempo</span>
                @endif
            </dd>
            <dt>Nominal</dt><dd class="cell-strong">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</dd>
            <dt>Status</dt><dd><span class="status status-{{ $invoice->status }}">{{ $invoice->statusLabel() }}</span></dd>
            @if($invoice->isPaid() && $invoice->paid_at)
                <dt>Dibayar pada</dt><dd>{{ $invoice->paid_at->translatedFormat('d M Y, H:i') }} WIB</dd>
                @if($invoice->payment_type)
                    <dt>Metode</dt><dd>{{ strtoupper(str_replace('_', ' ', $invoice->payment_type)) }}</dd>
                @endif
            @endif
        </dl>

        <div class="panel-foot panel-total">
            <span>Total Tagihan</span><strong>Rp{{ number_format($invoice->amount, 0, ',', '.') }}</strong>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Pembayaran</h2></div>

        @if($invoice->isPaid())
            <div class="empty-state">
                <span>✅</span>
                <h3>Tagihan Lunas</h3>
                <p>Terima kasih, pembayaran Anda sudah kami terima.</p>
            </div>
        @elseif($invoice->status === \App\Models\Invoice::STATUS_DIBATALKAN)
            <div class="empty-state">
                <span>🚫</span>
                <h3>Tagihan Dibatalkan</h3>
                <p>Tagihan ini sudah dibatalkan. Hubungi admin bila ada pertanyaan.</p>
            </div>
        @elseif(! $gatewayReady)
            <div class="empty-state">
                <span>⚠️</span>
                <h3>Pembayaran belum tersedia</h3>
                <p>Payment gateway belum dikonfigurasi. Hubungi admin Farafi Computer.</p>
            </div>
        @else
            <div class="pay-panel">
                <div class="pay-illustration">📱</div>
                <h3>Bayar dengan QRIS</h3>
                <p class="muted">Klik tombol di bawah, lalu pindai QR yang muncul dengan aplikasi e-wallet atau m-banking Anda.</p>
                <button type="button" id="payButton" class="btn btn-primary btn-lg btn-block">Bayar Sekarang — Rp{{ number_format($invoice->amount, 0, ',', '.') }}</button>
                <p class="summary-note">🔒 Transaksi diproses aman oleh Midtrans. Farafi Computer tidak menyimpan data pembayaran Anda.</p>
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
@if($invoice->isPayable() && $gatewayReady)
    <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
    <script>
        const payButton = document.getElementById('payButton');
        let snapToken = '{{ $invoice->midtrans_snap_token }}';

        async function startPayment() {
            if (!snapToken) {
                // Minta server membuat token Snap terlebih dahulu.
                const res = await fetch('{{ route('account.invoices.pay', $invoice) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                });

                if (! res.ok) {
                    alert('Gagal memulai pembayaran. Silakan coba lagi.');
                    return;
                }

                const data = await res.json();
                snapToken = data.token;
            }

            if (! snapToken) {
                alert('Gagal memulai pembayaran. Silakan coba lagi.');
                return;
            }

            window.snap.pay(snapToken, {
                onSuccess: function () { window.location.href = '{{ route('account.invoices.finish', ['order_id' => $invoice->invoice_number]) }}'; },
                onPending: function () { window.location.href = '{{ route('account.invoices.finish', ['order_id' => $invoice->invoice_number]) }}'; },
                onError: function () {
                    payButton.disabled = false;
                    payButton.textContent = 'Bayar Sekarang — Rp{{ number_format($invoice->amount, 0, ',', '.') }}';
                    alert('Pembayaran gagal. Silakan coba lagi.');
                },
                onClose: function () {
                    payButton.disabled = false;
                    payButton.textContent = 'Bayar Sekarang — Rp{{ number_format($invoice->amount, 0, ',', '.') }}';
                }
            });
        }

        payButton?.addEventListener('click', function () {
            payButton.disabled = true;
            payButton.textContent = 'Memuat…';
            startPayment();
        });
    </script>
@endif
@endpush
