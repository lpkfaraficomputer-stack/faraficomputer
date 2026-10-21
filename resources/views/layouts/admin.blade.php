<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Admin') — Farafi Computer</title>
    <meta name="robots" content="noindex, nofollow">
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
            <div class="admin-nav-label">Utama</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="admin-nav-ico">📊</span> <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.courses.index') }}" class="{{ request()->routeIs('admin.courses.*') ? 'active' : '' }}">
                <span class="admin-nav-ico">📚</span> <span>Kursus</span>
                <span class="admin-nav-count">{{ \App\Models\Course::count() }}</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <span class="admin-nav-ico">🧾</span> <span>Pesanan</span>
                @php($pending = \App\Models\Order::where('status', 'menunggu_pembayaran')->count())
                @if($pending > 0)<span class="admin-nav-badge">{{ $pending }}</span>@endif
            </a>
            <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                <span class="admin-nav-ico">🧰</span> <span>Layanan</span>
                @php($layananBaru = \App\Models\ServiceRequest::where('status', 'baru')->count())
                @if($layananBaru > 0)<span class="admin-nav-badge">{{ $layananBaru }}</span>@endif
            </a>
            <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <span class="admin-nav-ico">📈</span> <span>Laporan Keuangan</span>
            </a>

            @if($user->isSuperAdmin())
                <div class="admin-nav-label">Khusus Super Admin</div>
                <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <span class="admin-nav-ico">🗂️</span> <span>Kategori</span>
                    <span class="admin-nav-count">{{ \App\Models\Category::count() }}</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <span class="admin-nav-ico">👥</span> <span>Pengguna</span>
                    <span class="admin-nav-count">{{ \App\Models\User::count() }}</span>
                </a>
            @endif
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
                <span>@yield('title', 'Panel Admin')</span>
                <nav class="admin-crumb">
                    <a href="{{ route('admin.dashboard') }}">Panel</a>
                    <span>/</span>
                    <span>@yield('title', 'Dashboard')</span>
                </nav>
            </div>
            <div class="admin-topbar-user">
                <a href="{{ route('home') }}" class="btn btn-ghost btn-sm" target="_blank">🌐 Lihat Toko</a>
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
