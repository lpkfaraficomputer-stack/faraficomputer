@extends('layouts.app')

@section('title', $course->title . ' — Farafi Computer')

@section('content')
<div class="page-head page-head-sm">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>›</span>
            <a href="{{ route('courses.index') }}">Kursus</a> <span>›</span>
            <a href="{{ route('categories.show', $course->category->slug) }}">{{ $course->category->name }}</a> <span>›</span>
            <span>{{ $course->title }}</span>
        </nav>
    </div>
</div>

<div class="container page-body page-body-single">
    <section class="course-detail">
        <div class="course-detail-head" style="--cat-color: {{ $course->category->color }}">
            <span class="course-detail-icon">{{ $course->category->icon }}</span>
            <span class="course-category on-dark">{{ $course->category->name }}</span>
            <h1>{{ $course->title }}</h1>
            <p>{{ $course->short_description }}</p>
            <div class="course-stats on-dark">
                <span>⭐ {{ number_format($course->rating, 1) }} rating</span>
                <span>👥 {{ number_format($course->students) }} siswa</span>
                <span>🎬 {{ $course->lessons }} materi</span>
                <span>⏱ {{ $course->duration_hours }} jam</span>
                <span>📶 {{ $course->level }}</span>
            </div>
            <p class="course-instructor on-dark">Diajarkan oleh <strong>{{ $course->instructor ?? 'Instruktur Farafi' }}</strong></p>
        </div>

        <div class="course-panel">
            <h2>🎯 Tentang Kursus Ini</h2>
            <p>{!! nl2br(e($course->description ?? $course->short_description)) !!}</p>

            <h2>✅ Apa yang Akan Anda Pelajari</h2>
            <ul class="check-list">
                <li>Menguasai materi {{ $course->title }} dari dasar hingga mahir</li>
                <li>Mengerjakan proyek nyata sebagai portofolio</li>
                <li>Teknik dan best practice yang dipakai di dunia kerja</li>
                <li>Bersiap mengikuti ujian sertifikasi kompetensi</li>
            </ul>

            <h2>📋 Struktur Materi</h2>
            <div class="curriculum">
                @for($i = 1; $i <= min(6, max(3, $course->lessons)); $i++)
                    <div class="curriculum-item reveal reveal-delay-{{ min($i, 5) }}" style="cursor: pointer">
                        <span class="curriculum-no">{{ $i }}</span>
                        <div>
                            <strong>Pertemuan {{ $i }} — Pengenalan &amp; Praktik</strong>
                            <span>{{ $i === 1 ? 'Pengenalan dasar dan lingkungan kerja' : 'Latihan praktis dan studi kasus' }}</span>
                        </div>
                        <span class="curriculum-len">{{ max(10, 30 - $i * 2) }} menit</span>
                    </div>
                @endfor
            </div>
        </div>

        @if($related->isNotEmpty())
        <div class="course-panel">
            <h2>Kursus Terkait</h2>
            <div class="course-grid course-grid-3">
                @foreach($related as $rel)
                    @include('partials.course-card', ['course' => $rel, 'compact' => true])
                @endforeach
            </div>
        </div>
        @endif
    </section>

    <aside class="buy-box">
        <div class="buy-price">
            @if($course->discount_price)
                <span class="price-old">Rp{{ number_format($course->price, 0, ',', '.') }}</span>
                @if($course->discount_percent)
                    <span class="course-thumb-badge static">Hemat {{ $course->discount_percent }}%</span>
                @endif
            @endif
            <div class="price-now price-xl">Rp{{ number_format($course->final_price, 0, ',', '.') }}</div>
            <span class="price-note">Sekali beli, akses selamanya</span>
        </div>

        <form action="{{ route('cart.store') }}" method="POST" class="buy-actions">
            @csrf
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            <button type="submit" name="redirect" value="cart" class="btn btn-primary btn-lg btn-block">Tambah ke Keranjang</button>
            <button type="submit" name="redirect" value="checkout" class="btn btn-outline btn-lg btn-block">Beli Sekarang</button>
        </form>

        <ul class="buy-guarantees">
            <li>✔ Sertifikat kelulusan resmi</li>
            <li>✔ Akses materi seumur hidup</li>
            <li>✔ Garansi 7 hari uang kembali</li>
            <li>✔ Support mentor di forum kelas</li>
            <li>✔ Materi bisa diunduh &amp; ditonton offline</li>
        </ul>

        <div class="buy-share">
            <strong>Bagikan:</strong>
            <span class="share-links">
                <a href="#">WhatsApp</a> <a href="#">Instagram</a> <a href="#">Facebook</a>
            </span>
        </div>
    </aside>
</div>
@endsection
