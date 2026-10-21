@extends('layouts.app')

@section('title', 'Layanan — Farafi Computer')

@section('content')
<div class="page-head">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>›</span> <span>Layanan</span>
        </nav>
        <h1>Layanan Farafi Computer</h1>
        <p>Berbagai layanan pendidikan dan kerja sama untuk siswa, sekolah, instansi, maupun perusahaan.</p>
    </div>
</div>

<div class="container page-body-full">
    <section>
        <div class="tp-section-head">
            <div class="section-label">🧰 Layanan Kami</div>
            <h2 class="section-title">Pilih Layanan yang Anda Butuhkan</h2>
            <div class="section-divider"></div>
            <p class="section-subtitle">Semua pengajuan diproses tim kami pada hari kerja, Senin–Sabtu 08.00–17.00 WIB. Anda akan menerima nomor tiket untuk memantau statusnya.</p>
        </div>

        <div class="service-grid">
            @foreach($services as $type => $service)
                <a href="{{ route('services.show', $type) }}" class="service-tile" style="--svc-color: {{ $service['color'] }}">
                    <span class="service-tile-icon">{{ $service['icon'] }}</span>
                    <div class="service-tile-body">
                        <h3>{{ $service['name'] }}</h3>
                        <p>{{ $service['tagline'] }}</p>
                        <span class="service-tile-cta">Ajukan Sekarang <span>→</span></span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="service-track-box">
            <div>
                <h3>🔎 Sudah mengajukan permohonan?</h3>
                <p>Cek status pengajuan Anda dengan nomor tiket yang kami kirimkan.</p>
            </div>
            <a href="{{ route('services.track') }}" class="btn btn-primary btn-lg">Cek Status Permohonan</a>
        </div>

        <p class="services-help muted">Butuh bantuan memilih layanan? Tanya <strong>Farafi Assistant</strong> di pojok kanan bawah 💬 atau hubungi WhatsApp {{ config('services.agent.contact.whatsapp') }}.</p>
    </section>
</div>
@endsection
