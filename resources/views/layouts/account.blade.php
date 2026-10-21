<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Area Murid') — Farafi Computer</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/interactive.css') }}">
</head>
<body class="admin-body">
@php($user = auth()->user())

<div class="admin-shell">
    <aside class="admin-sidebar" id="adminSidebar">
        <a href="{{ route('home') }}" class="admin-logo">
            <span class="logo-mark">F</span>
            <span class="logo-text">Farafi<strong>Computer</strong></span>
        </a>

        <nav class="admin-nav">
            <div class="admin-nav-label">Area Murid</div>
            <a href="{{ route('account.dashboard') }}" class="{{ request()->routeIs('account.dashboard') ? 'active' : '' }}">
                <span class="admin-nav-ico">🏠</span> <span>Dashboard</span>
            </a>
            <a href="{{ route('account.invoices.index') }}" class="{{ request()->routeIs('account.invoices.*') ? 'active' : '' }}">
                <span class="admin-nav-ico">🧾</span> <span>Tagihan Saya</span>
                @php($tagihanAktif = $user->invoices()->where('status', \App\Models\Invoice::STATUS_BELUM_DIBAYAR)->count())
                @if($tagihanAktif > 0)<span class="admin-nav-badge">{{ $tagihanAktif }}</span>@endif
            </a>
            <a href="{{ route('account.orders.index') }}" class="{{ request()->routeIs('account.orders.*') ? 'active' : '' }}">
                <span class="admin-nav-ico">📚</span> <span>Kursus Saya</span>
            </a>
            <a href="{{ route('account.profile') }}" class="{{ request()->routeIs('account.profile') ? 'active' : '' }}">
                <span class="admin-nav-ico">👤</span> <span>Profil</span>
            </a>

            <div class="admin-nav-label">Belanja</div>
            <a href="{{ route('courses.index') }}">
                <span class="admin-nav-ico">🛒</span> <span>Jelajahi Kursus</span>
            </a>
            <a href="{{ route('home') }}">
                <span class="admin-nav-ico">🌐</span> <span>Lihat Toko</span>
            </a>
        </nav>

        <div class="admin-sidebar-foot">
            <div class="admin-who">
                <span class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                <span class="admin-who-text">
                    <strong>{{ $user->name }}</strong>
                    <span class="role-badge role-{{ $user->role }}">{{ $user->roleLabel() }}</span>
                </span>
            </div>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button class="hamburger" id="adminHamburger" aria-label="Menu">☰</button>
            <div class="admin-topbar-title">
                <span>@yield('title', 'Area Murid')</span>
                <nav class="admin-crumb">
                    <a href="{{ route('account.dashboard') }}">Akun</a>
                    <span>/</span>
                    <span>@yield('title', 'Dashboard')</span>
                </nav>
            </div>
            <div class="admin-topbar-user">
                <span class="admin-topbar-sep"></span>
                <span class="user-chip">
                    <span class="user-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    <span class="user-chip-text">{{ $user->name }}</span>
                </span>
                <form action="{{ route('logout') }}" method="POST" class="logout-form">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm">Keluar</button>
                </form>
            </div>
        </header>

        <div class="admin-content">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

<script>
    document.getElementById('adminHamburger')?.addEventListener('click', function () {
        document.getElementById('adminSidebar')?.classList.toggle('open');
    });
</script>
@stack('scripts')
<script src="{{ asset('js/interactive.js') }}" defer></script>
</body>
</html>
