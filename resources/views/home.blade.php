@extends('layouts.app')

@section('title', 'Farafi Computer — Kursus Komputer Terpercaya')

@section('content')
{{-- ============ HERO ============ --}}
<section class="hero-section" id="home">
    <div class="hero-data-streams">
        @for($i = 0; $i < 8; $i++)<div class="hero-data-stream"></div>@endfor
    </div>
    <div class="hero-particles">
        <div class="hero-particle">💻</div>
        <div class="hero-particle">🧑‍💻</div>
        <div class="hero-particle">🎨</div>
        <div class="hero-particle">🌐</div>
        <div class="hero-particle">📊</div>
        <div class="hero-particle">🎬</div>
    </div>
    <div class="hero-tech-circle"></div>
    <div class="hero-tech-circle"></div>

    <div class="container hero-content">
        <div class="hero-grid">
            <div class="hero-text">
                <div class="hero-badge">
                    <span class="badge-dot"></span>
                    Website Resmi Farafi Computer — Kursus Komputer
                </div>
                <h1>Selamat Datang di<br><span class="tech-gradient">Farafi Computer</span> — Kursus Komputer Terpercaya</h1>
                <p class="hero-description">
                    Lembaga kursus komputer terpercaya sejak 2010. Kami menyediakan kelas
                    Office, Pemrograman, Desain Grafis, Jaringan, dan Multimedia untuk
                    meningkatkan kompetensi digital masyarakat Indonesia.
                </p>

                <div class="hero-buttons">
                    <a href="{{ route('courses.index') }}" class="hero-btn-primary">
                        📚 Jelajahi Kursus
                        <span class="hero-btn-icon">→</span>
                    </a>
                    <a href="{{ route('categories.show', 'pemrograman') }}" class="hero-btn-green">
                        🧑‍💻 Kelas Pemrograman
                    </a>
                    <a href="{{ route('courses.index', ['sort' => 'harga_termurah']) }}" class="hero-btn-promo">
                        <span class="promo-pulse-ring"></span>🔥 Promo s/d 40%
                    </a>
                </div>

                <div class="hero-stats-row">
                    <div class="hero-stat-item"><h3>50+</h3><p>Kelas Tersedia</p></div>
                    <div class="hero-stat-item"><h3>12.000+</h3><p>Siswa Terlatih</p></div>
                    <div class="hero-stat-item"><h3>4,8/5</h3><p>Rating Siswa</p></div>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-visual-main">💻</div>
                <div class="hero-float-card hero-float-1">📊 <span>Excel Master Class</span></div>
                <div class="hero-float-card hero-float-2">🧑‍💻 <span>Full-Stack Web Dev</span></div>
                <div class="hero-float-card hero-float-3">🎓 <span>Sertifikat Resmi</span></div>
            </div>
        </div>
    </div>

    <div class="hero-wave">
        <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 80L48 73.3C96 66.7 192 53.3 288 48C384 42.7 480 45.3 576 53.3C672 61.3 768 74.7 864 77.3C960 80 1056 72 1152 64C1248 56 1344 48 1392 44L1440 40V100H1392C1344 100 1248 100 1152 100C1056 100 960 100 864 100C768 100 672 100 576 100C480 100 384 100 288 100C192 100 96 100 48 100H0V80Z" fill="#f8fafc"/>
        </svg>
    </div>
</section>

{{-- ============ AGENDA + INFO TERKINI ============ --}}
<section class="agenda-info-section">
    <div class="container agenda-info-grid">
        <div>
            <div class="section-head-row" style="margin-bottom: 1.5rem">
                <div class="heading-inline">
                    <span class="heading-icon">🗓️</span>
                    <h3>Agenda Kelas</h3>
                </div>
                <a href="{{ route('courses.index') }}" class="btn-pill-outline">Lihat Semua →</a>
            </div>

            <div class="agenda-list">
                @php
                    $agenda = [
                        ['day' => 'Senin, 20 Oktober 2026', 'items' => [['19.00 WIB', 'Ms. Excel Bisnis — Batch 15', 'Online via Zoom'], ['20.00 WIB', 'Kelas Bimbingan Sertifikasi', 'Lab Komputer 1']]],
                        ['day' => 'Sabtu, 25 Oktober 2026', 'items' => [['09.00 WIB', 'Desain Grafis CorelDRAW — Tatap Muka', 'Lab Komputer 2'], ['13.00 WIB', 'Workshop Jaringan MikroTik', 'Ruang Kelas B']]],
                        ['day' => 'Minggu, 26 Oktober 2026', 'items' => [['10.00 WIB', 'Mock Ujian Sertifikasi Komputer', 'Lab Komputer 1']]],
                    ];
                @endphp
                @foreach($agenda as $group)
                    <div class="agenda-day">
                        <div class="agenda-day-label">{{ $group['day'] }}</div>
                        @foreach($group['items'] as $item)
                            <div class="agenda-item">
                                <span class="agenda-dot"></span>
                                <div class="agenda-item-body">
                                    <strong>{{ $item[1] }}</strong>
                                    <span>{{ $item[2] }}</span>
                                </div>
                                <span class="agenda-time">🕐 {{ $item[0] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <div class="section-head-row" style="margin-bottom: 1.5rem">
                <div class="heading-inline">
                    <span class="heading-icon heading-icon-sky">📰</span>
                    <h3>Info Terkini</h3>
                </div>
            </div>

            <a href="{{ route('courses.index', ['sort' => 'harga_termurah']) }}" class="news-featured">
                <div class="news-featured-media">🔥</div>
                <span class="news-tag news-tag-promo">Promo</span>
                <h4>Diskon hingga 40% untuk Semua Kelas Pilihan</h4>
                <p>Rayakan HUT Farafi Computer dengan potongan harga spesial untuk 22 kelas unggulan. Berlaku sampai akhir bulan!</p>
                <span class="news-date">📅 18 Oktober 2026</span>
            </a>

            <div class="news-list">
                <a href="{{ route('courses.index') }}" class="news-item">
                    <span class="news-thumb">🎓</span>
                    <div>
                        <span class="news-tag">Akademik</span>
                        <strong>Pendaftaran Sertifikasi Komputer Gelombang 4 Dibuka</strong>
                        <span class="news-date">📅 15 Oktober 2026</span>
                    </div>
                </a>
                <a href="{{ route('categories.show', 'komputer-office') }}" class="news-item">
                    <span class="news-thumb">🏫</span>
                    <div>
                        <span class="news-tag">Info</span>
                        <strong>Kelas Inspirasi Gratis untuk SMK se-Kota</strong>
                        <span class="news-date">📅 10 Oktober 2026</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ============ KATEGORI ============ --}}
<section class="tp-section" id="kategori">
    <div class="container">
        <div class="tp-section-head">
            <div class="section-label">🗂️ Kategori Kami</div>
            <h2 class="section-title">Kategori Kursus Komputer</h2>
            <div class="section-divider"></div>
            <p class="section-subtitle">Pilih bidang yang ingin Anda kuasai — semua diajarkan oleh instruktur berpengalaman dan bersertifikat.</p>
        </div>

        <div class="tp-grid tp-grid-3">
            @foreach($categories as $i => $category)
                <a href="{{ route('categories.show', $category->slug) }}" class="tp-card">
                    <div class="tp-card-media" style="--cat-color: {{ $category->color }}">
                        <span class="tp-card-emoji">{{ $category->icon }}</span>
                        <span class="card-number">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="tp-card-body">
                        <h5>{{ $category->name }}</h5>
                        <p>{{ $category->description }}</p>
                        <span class="tp-card-count">{{ $category->courses_count }} kursus tersedia</span>
                        <span class="tp-card-btn">Selengkapnya <span>➜</span></span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ KURSUS UNGGULAN ============ --}}
<section class="tp-section tp-section-alt" id="kursus">
    <div class="container">
        <div class="tp-section-head">
            <div class="section-label">⭐ Kelas Pilihan</div>
            <h2 class="section-title">Kursus Unggulan &amp; Terpopuler</h2>
            <div class="section-divider"></div>
            <p class="section-subtitle">Kelas yang paling banyak dipilih siswa kami minggu ini.</p>
        </div>

        <div class="tp-grid tp-grid-4">
            @foreach($pilihan as $i => $course)
                <a href="{{ route('courses.show', $course->slug) }}" class="tp-card">
                    <div class="tp-card-media" style="--cat-color: {{ $course->category->color ?? '#4f46e5' }}">
                        @if($course->image_url)
                            <img src="{{ $course->image_url }}" alt="{{ $course->title }}" class="tp-card-img" loading="lazy">
                        @else
                            <span class="tp-card-emoji">{{ $course->category->icon ?? '💻' }}</span>
                        @endif
                        @if($course->discount_percent)<span class="tp-card-badge">-{{ $course->discount_percent }}%</span>@endif
                        <span class="card-number">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="tp-card-body">
                        <div class="tp-card-meta">
                            <span>{{ $course->category->name ?? 'Kursus' }}</span>
                            <span>⭐ {{ number_format($course->rating, 1) }}</span>
                        </div>
                        <h5>{{ $course->title }}</h5>
                        <p>{{ $course->short_description }}</p>
                        <div class="tp-card-price">
                            @if($course->discount_price)<s>Rp{{ number_format($course->price, 0, ',', '.') }}</s>@endif
                            <strong>Rp{{ number_format($course->final_price, 0, ',', '.') }}</strong>
                        </div>
                        <span class="tp-card-btn">Selengkapnya <span>➜</span></span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="tp-section-foot">
            <a href="{{ route('courses.index') }}" class="btn-view-all">
                <span>Lihat Semua Kursus</span>
                <span class="btn-view-all-icon">→</span>
            </a>
        </div>
    </div>
</section>

{{-- ============ STATISTIK (counter) ============ --}}
<section class="stats-section" id="statistik">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item"><span class="stat-icon">📚</span><h2 class="counter" data-target="50">0</h2><p>Kelas Tersedia</p></div>
            <div class="stat-item"><span class="stat-icon">🎓</span><h2 class="counter" data-target="12000">0</h2><p>Siswa Terlatih</p></div>
            <div class="stat-item"><span class="stat-icon">🗂️</span><h2 class="counter" data-target="6">0</h2><p>Bidang Keahlian</p></div>
            <div class="stat-item"><span class="stat-icon">✅</span><h2 class="counter" data-target="90">0</h2><p>Lulus Sertifikasi (%)</p></div>
        </div>
    </div>
</section>

{{-- ============ PROGRAM BELAJAR ============ --}}
<section class="tp-section" id="program">
    <div class="container">
        <div class="tp-section-head">
            <div class="section-label">🛠️ Program Belajar</div>
            <h2 class="section-title">Kenapa Belajar di Farafi Computer?</h2>
            <div class="section-divider"></div>
        </div>

        <div class="tp-grid tp-grid-4">
            <div class="app-card">
                <span class="app-card-icon">💻</span>
                <h6>Kelas Online</h6>
                <p>Belajar dari mana saja melalui video kelas dan kelas live via Zoom.</p>
                <span class="app-tag">flexibel</span>
            </div>
            <div class="app-card">
                <span class="app-card-icon">🏫</span>
                <h6>Kelas Tatap Muka</h6>
                <p>Sesi langsung di lab komputer kami dengan peralatan lengkap.</p>
                <span class="app-tag">praktis</span>
            </div>
            <div class="app-card">
                <span class="app-card-icon">🎓</span>
                <h6>Sertifikat Resmi</h6>
                <p>Sertifikat kelulusan berstandar kompetensi setelah menyelesaikan materi.</p>
                <span class="app-tag">tersertifikasi</span>
            </div>
            <div class="app-card">
                <span class="app-card-icon">💬</span>
                <h6>Mentor 1-on-1</h6>
                <p>Tanya jawab langsung dengan instruktur praktisi di forum kelas.</p>
                <span class="app-tag">pendampingan</span>
            </div>
        </div>
    </div>
</section>

{{-- ============ CTA ============ --}}
<section class="cta-section" id="kontak">
    <div class="container cta-inner">
        <h2>Butuh Bantuan atau Informasi?</h2>
        <p>Hubungi kami untuk konsultasi pemilihan kelas, jadwal, maupun program beasiswa kursus.</p>
        <div class="cta-buttons">
            <a href="tel:+62215550123" class="cta-btn-light">📞 Hubungi Kami <span>→</span></a>
            <a href="mailto:halo@faraficomputer.id" class="cta-btn-outline">✉ Kirim Email</a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Counter animasi statistik
    const counters = document.querySelectorAll('.counter');
    const animateCounter = (el) => {
        const target = parseInt(el.dataset.target, 10);
        const duration = 1600;
        const start = performance.now();
        const tick = (now) => {
            const p = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(target * eased).toLocaleString('id-ID') + (p === 1 && target >= 50 ? '+' : '');
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    };

    const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) { animateCounter(e.target); io.unobserve(e.target); }
        });
    }, { threshold: 0.4 });

    counters.forEach((c) => io.observe(c));

    // Tombol kembali ke atas
    const backBtn = document.getElementById('backToTop');
    window.addEventListener('scroll', () => {
        backBtn?.classList.toggle('show', window.scrollY > 500);
    });

    backBtn?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Kategori & kartu kursus muncul berurutan saat di-scroll
    document.querySelectorAll('#kategori .tp-grid > *, #kursus .tp-grid > *').forEach((el, i) => {
        el.classList.add('reveal', 'reveal-delay-' + (i % 4 === 0 ? 1 : i % 4));
    });

    // Agenda & info slide dari samping
    document.querySelector('.agenda-info-grid > div:nth-child(1)')?.classList.add('reveal', 'reveal-left');
    document.querySelector('.agenda-info-grid > div:nth-child(2)')?.classList.add('reveal', 'reveal-right');

    const io2 = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) { e.target.classList.add('is-visible'); io2.unobserve(e.target); }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.reveal').forEach((el) => io2.observe(el));

    window.addEventListener('load', () => {
        document.querySelectorAll('.reveal').forEach((el) => {
            if (el.getBoundingClientRect().top < window.innerHeight * 0.95) el.classList.add('is-visible');
        });
    });
</script>
@endpush
