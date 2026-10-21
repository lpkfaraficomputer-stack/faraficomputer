<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Farafi Computer — Kursus Komputer Terpercaya')</title>
    <meta name="description" content="Kursus komputer online terlengkap: Office, Pemrograman, Desain Grafis, Jaringan, dan Multimedia. Belajar dari instruktur berpengalaman.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/agent.css') }}">
    <link rel="stylesheet" href="{{ asset('css/interactive.css') }}">
</head>
<body>
    @php
        $cartCount = collect(session('cart', []))->sum('qty');
        $currentCategory = optional(\App\Models\Category::where('slug', request()->segment(2))->first());
    @endphp

    <div class="topbar">
        <div class="container topbar-inner">
            <span>📞 (021) 555-0123 &nbsp;·&nbsp; ✉ halo@faraficomputer.id</span>
            <span class="topbar-right">🎓 Sertifikat resmi setelah lulus &nbsp;·&nbsp; 📦 Akses seumur hidup</span>
        </div>
    </div>

    <header class="header">
        <div class="container header-inner">
            <a href="{{ route('home') }}" class="logo">
                <span class="logo-mark">F</span>
                <span class="logo-text">Farafi<strong>Computer</strong></span>
            </a>

            <nav class="nav" id="mainNav">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('courses.index') }}" class="nav-link {{ request()->routeIs('courses.index') ? 'active' : '' }}">Semua Kursus</a>
                <div class="nav-dropdown">
                    <button class="nav-link nav-dropdown-toggle" type="button">
                        Kategori
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="nav-dropdown-menu">
                        @foreach(\App\Models\Category::orderBy('sort_order')->get() as $cat)
                            <a href="{{ route('categories.show', $cat->slug) }}">{{ $cat->name }}</a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('services.index') }}" class="nav-link {{ request()->routeIs('services.*') ? 'active' : '' }}">Layanan</a>
                <a href="{{ route('cart.index') }}" class="nav-link">Keranjang</a>
            </nav>

            <div class="header-actions">
                @auth
                    <a href="{{ auth()->user()->homeRoute() }}" class="nav-link user-chip" title="Masuk sebagai {{ auth()->user()->roleLabel() }}">
                        <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="user-chip-text">{{ auth()->user()->name }}</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="btn btn-ghost btn-sm">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm nav-btn-login">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-outline btn-sm">Daftar</a>
                @endauth
                <a href="{{ route('cart.index') }}" class="cart-btn" title="Keranjang">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    @if($cartCount > 0)
                        <span class="cart-badge">{{ $cartCount }}</span>
                    @endif
                </a>
                <button class="hamburger" id="hamburger" aria-label="Menu">☰</button>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="container">
            <div class="alert alert-success">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="container">
            <div class="alert alert-error">{{ session('error') }}</div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container footer-grid">
            <div>
                <a href="{{ route('home') }}" class="logo logo-light">
                    <span class="logo-mark">F</span>
                    <span class="logo-text">Farafi<strong>Computer</strong></span>
                </a>
                <p class="footer-desc">Lembaga kursus komputer terpercaya sejak 2010. Melatih talenta digital Indonesia dengan kurikulum yang selalu up-to-date.</p>
                <div class="footer-contact">
                    📍 Jl. Digital Raya No. 88, Jakarta<br>
                    📞 (021) 555-0123 · ✉ halo@faraficomputer.id<br>
                    🕘 Senin–Sabtu, 08.00–17.00 WIB
                </div>
                <div class="footer-socials">
                    <a href="#" class="social-btn" title="Facebook">f</a>
                    <a href="#" class="social-btn" title="Instagram">📸</a>
                    <a href="#" class="social-btn" title="YouTube">▶</a>
                    <a href="#" class="social-btn" title="TikTok">♪</a>
                    <a href="#" class="social-btn" title="WhatsApp">💬</a>
                </div>
            </div>
            <div>
                <h4>Kategori Kursus</h4>
                <ul>
                    @foreach(\App\Models\Category::orderBy('sort_order')->get() as $cat)
                        <li><a href="{{ route('categories.show', $cat->slug) }}">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4>Navigasi</h4>
                <ul>
                    <li><a href="{{ route('home') }}">Beranda</a></li>
                    <li><a href="{{ route('courses.index') }}">Semua Kursus</a></li>
                    <li><a href="{{ route('cart.index') }}">Keranjang</a></li>
                    <li><a href="{{ route('courses.index', ['sort' => 'populer']) }}">Kursus Terpopuler</a></li>
                    <li><a href="{{ route('login') }}">Masuk / Daftar</a></li>
                </ul>
            </div>
            <div>
                <h4>Statistik</h4>
                <div class="footer-stat-box"><span>📚 Total Kelas</span><strong>{{ \App\Models\Course::count() }}+</strong></div>
                <div class="footer-stat-box"><span>🗂️ Bidang Keahlian</span><strong>{{ \App\Models\Category::count() }}</strong></div>
                <div class="footer-stat-box"><span>🎓 Siswa Terdaftar</span><strong>{{ \App\Models\User::where('role', 'murid')->count() }}+</strong></div>
                <p class="footer-note">Diperbarui otomatis dari basis data Farafi Computer.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                © {{ date('Y') }} Farafi Computer. Seluruh hak cipta dilindungi.
            </div>
        </div>
    </footer>

    <button id="backToTop" title="Kembali ke atas" aria-label="Kembali ke atas">↑</button>

    @include('partials.agent')

    <script src="{{ asset('js/interactive.js') }}" defer></script>

    <script>
        document.getElementById('hamburger')?.addEventListener('click', function () {
            document.getElementById('mainNav')?.classList.toggle('open');
        });
    </script>
    @stack('scripts')
</body>
</html>
