@extends('layouts.app')

@section('title', ($activeCategory->name ?? 'Semua Kursus') . ' — Farafi Computer')

@section('content')
<div class="page-head">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>›</span>
            <a href="{{ route('courses.index') }}">Kursus</a>
            @if($activeCategory)
                <span>›</span> <span>{{ $activeCategory->name }}</span>
            @endif
        </nav>
        <h1>{{ $activeCategory->name ?? 'Semua Kursus Komputer' }}</h1>
        <p>{{ $activeCategory->description ?? 'Jelajahi seluruh kelas komputer kami — dari dasar hingga tingkat profesional.' }}</p>
    </div>
</div>

<div class="container page-body">
    <aside class="filters">
        <form action="{{ route('courses.index') }}" method="GET" class="filter-search">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kursus...">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>

        <div class="filter-group">
            <h4>Kategori</h4>
            <ul class="filter-list">
                <li>
                    <a href="{{ route('courses.index') }}@if(request('q'))?q={{ request('q') }}@endif"
                       class="{{ request()->routeIs('courses.index') && !request('kategori') ? 'active' : '' }}">
                        Semua Kategori <span>{{ \App\Models\Course::count() }}</span>
                    </a>
                </li>
                @foreach($categories as $category)
                    <li>
                        <a href="{{ route('categories.show', $category->slug) }}"
                           class="{{ $activeCategory?->id === $category->id ? 'active' : '' }}">
                            {{ $category->name }} <span>{{ $category->courses_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="filter-group">
            <h4>Tingkat Kemampuan</h4>
            <ul class="filter-list">
                @foreach($levels as $level)
                    <li>
                        <a href="{{ route('courses.index', array_filter(['kategori' => request('kategori'), 'q' => request('q'), 'level' => $level])) }}"
                           class="{{ request('level') === $level ? 'active' : '' }}">
                            {{ $level }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>

    <section class="results">
        <div class="results-bar">
            <span class="results-count">{{ $courses->total() }} kursus ditemukan</span>
            <form method="GET" class="sort-form">
                @foreach(request()->query() as $key => $value)
                    @if($key !== 'sort')
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
                <select name="sort" onchange="this.form.submit()">
                    <option value="terbaru" @selected(request('sort') === 'terbaru' || !request('sort'))>Terbaru</option>
                    <option value="populer" @selected(request('sort') === 'populer')">Paling Populer</option>
                    <option value="harga_termurah" @selected(request('sort') === 'harga_termurah')">Harga Termurah</option>
                    <option value="harga_termahal" @selected(request('sort') === 'harga_termahal')">Harga Termahal</option>
                </select>
            </form>
        </div>

        @if($courses->isEmpty())
            <div class="empty-state">
                <span>🔍</span>
                <h3>Kursus tidak ditemukan</h3>
                <p>Coba kata kunci lain atau hapus filter pencarian Anda.</p>
                <a href="{{ route('courses.index') }}" class="btn btn-primary">Lihat Semua Kursus</a>
            </div>
        @else
            <div class="course-grid">
                @foreach($courses as $course)
                    @include('partials.course-card', ['course' => $course])
                @endforeach
            </div>
            <div class="pagination">
                <a href="{{ $courses->previousPageUrl() ?: '#' }}" class="page-link {{ $courses->onFirstPage() ? 'disabled' : '' }}">‹</a>
                @for($i = 1; $i <= $courses->lastPage(); $i++)
                    <a href="{{ $courses->url($i) }}" class="page-link {{ $courses->currentPage() === $i ? 'active' : '' }}">{{ $i }}</a>
                @endfor
                <a href="{{ $courses->nextPageUrl() ?: '#' }}" class="page-link {{ !$courses->hasMorePages() ? 'disabled' : '' }}">›</a>
            </div>
        @endif
    </section>
</div>
@endsection
