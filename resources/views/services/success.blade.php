@extends('layouts.app')

@section('title', 'Permohonan Terkirim — Farafi Computer')

@section('content')
<div class="page-head page-head-sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>›</span>
            <a href="{{ route('services.index') }}">Layanan</a> <span>›</span>
            <span>Permohonan Terkirim</span>
        </nav>
    </div>
</div>

<div class="container page-body-full page-body-narrow">
    <section class="success-card">
        <div class="success-icon">✅</div>
        <h1>Permohonan Berhasil Dikirim!</h1>
        <p class="success-lead">
            Terima kasih, <strong>{{ $request->name }}</strong>. Permohonan <strong>{{ $service['name'] }}</strong>
            sudah kami terima dan akan diverifikasi pada hari kerja.
        </p>

        <div class="ticket-box">
            <span>Nomor Tiket Anda</span>
            <strong>{{ $request->ticket_number }}</strong>
            <small>Simpan nomor ini untuk memantau status permohonan.</small>
        </div>

        <div class="success-details">
            <h3>Ringkasan Permohonan</h3>
            <dl class="detail-list">
                <dt>Layanan</dt><dd>{{ $service['icon'] }} {{ $service['name'] }}</dd>
                <dt>Nama</dt><dd>{{ $request->name }}</dd>
                <dt>Email</dt><dd>{{ $request->email }}</dd>
                <dt>WhatsApp</dt><dd>{{ $request->phone }}</dd>
                @if($request->institution)<dt>Instansi</dt><dd>{{ $request->institution }}</dd>@endif
                @if($request->subject)<dt>Keperluan</dt><dd>{{ $request->subject }}</dd>@endif
                @if($request->start_date)<dt>Tanggal Mulai</dt><dd>{{ $request->start_date->translatedFormat('d F Y') }}</dd>@endif
                @if($request->end_date)<dt>Tanggal Selesai</dt><dd>{{ $request->end_date->translatedFormat('d F Y') }}</dd>@endif
                <dt>Status</dt><dd><span class="status status-service-{{ $request->status }}">{{ $request->statusLabel() }}</span></dd>
            </dl>
        </div>

        <div class="success-actions">
            <a href="{{ route('services.track', ['ticket' => $request->ticket_number]) }}" class="btn btn-primary btn-lg">Pantau Status</a>
            <a href="{{ route('services.index') }}" class="btn btn-outline btn-lg">Layanan Lain</a>
            <a href="https://wa.me/{{ config('services.agent.contact.whatsapp') }}?text={{ urlencode('Halo, saya mengajukan ' . $service['name'] . ' dengan nomor tiket ' . $request->ticket_number) }}" class="btn btn-ghost btn-lg" target="_blank">Konfirmasi via WhatsApp</a>
        </div>
    </section>
</div>
@endsection
