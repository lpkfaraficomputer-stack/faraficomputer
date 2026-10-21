@extends('layouts.app')

@section('title', 'Cek Status Permohonan — Farafi Computer')

@section('content')
<div class="page-head page-head-sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>›</span>
            <a href="{{ route('services.index') }}">Layanan</a> <span>›</span>
            <span>Cek Status</span>
        </nav>
        <h1>🔎 Cek Status Permohonan</h1>
        <p>Masukkan nomor tiket (format LYN-YYMMDD-XXXXX) untuk melihat status pengajuan Anda.</p>
    </div>
</div>

<div class="container page-body-full page-body-narrow">
    <section>
        <form method="GET" action="{{ route('services.track') }}" class="track-form">
            <input type="text" name="ticket" value="{{ $ticket }}" placeholder="LYN-261020-XXXXX" required>
            <button type="submit" class="btn btn-primary btn-lg">Cek Status</button>
        </form>

        @if($ticket !== '')
            @if($found)
                <div class="track-result">
                    <div class="track-head">
                        <div>
                            <span class="muted">Nomor Tiket</span>
                            <h2>{{ $found->ticket_number }}</h2>
                        </div>
                        <span class="status status-service-{{ $found->status }}">{{ $found->statusLabel() }}</span>
                    </div>

                    <dl class="detail-list">
                        <dt>Layanan</dt><dd>{{ $found->service_name }}</dd>
                        <dt>Nama Pemohon</dt><dd>{{ $found->name }}</dd>
                        <dt>Instansi</dt><dd>{{ $found->institution ?? '-' }}</dd>
                        <dt>Keperluan</dt><dd>{{ $found->subject ?? '-' }}</dd>
                        <dt>Diajukan</dt><dd>{{ $found->created_at->translatedFormat('d F Y, H:i') }} WIB</dd>
                        @if($found->processed_at)<dt>Diproses</dt><dd>{{ $found->processed_at->translatedFormat('d F Y, H:i') }} WIB</dd>@endif
                        @if($found->admin_note)<dt>Catatan Petugas</dt><dd>{{ $found->admin_note }}</dd>@endif
                    </dl>

                    <p class="track-help muted">Ada pertanyaan lanjutan? Hubungi kami di WhatsApp {{ config('services.agent.contact.whatsapp') }} dengan menyebutkan nomor tiket di atas.</p>
                </div>
            @else
                <div class="alert alert-error track-alert">
                    Nomor tiket <strong>{{ $ticket }}</strong> tidak ditemukan. Periksa kembali penulisannya ya.
                </div>
            @endif
        @endif

        <div class="track-foot">
            <a href="{{ route('services.index') }}" class="btn btn-outline">← Lihat Semua Layanan</a>
        </div>
    </section>
</div>
@endsection
