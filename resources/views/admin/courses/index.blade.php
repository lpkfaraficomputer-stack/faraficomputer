@extends('layouts.admin')

@section('title', 'Kursus')

@section('content')
<div class="page-intro">
    <div>
        <h1>Manajemen Kursus</h1>
        <p>{{ $courses->total() }} kursus terdaftar di toko.</p>
    </div>
    <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">+ Kursus Baru</a>
</div>

<section class="panel">
    <div class="panel-filters">
        <form method="GET" class="filter-inline">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul / instruktur...">
            <select name="kategori" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->slug }}" @selected(request('kategori') === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr><th>Kursus</th><th>Kategori</th><th>Level</th><th>Harga</th><th>Penjualan</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($courses as $course)
                <tr>
                    <td>
                        <div class="cell-course">
                            <span class="cell-thumb" style="--cat-color: {{ $course->category->color }}">
                                @if($course->image_url)
                                    <img src="{{ $course->image_url }}" alt="" loading="lazy">
                                @else
                                    {{ $course->category->icon }}
                                @endif
                            </span>
                            <span class="cell-course-body">
                                <strong>{{ $course->title }}</strong>
                                @if($course->is_featured)<span class="chip chip-featured">⭐ Unggulan</span>@endif
                                <small class="muted">{{ $course->instructor ?? 'Tanpa instruktur' }} · ⭐ {{ number_format($course->rating, 1) }} · {{ $course->students }} siswa</small>
                            </span>
                        </div>
                    </td>
                    <td><span class="chip" style="--chip-color: {{ $course->category->color }}">{{ $course->category->icon }} {{ $course->category->name }}</span></td>
                    <td>{{ $course->level }}</td>
                    <td class="cell-strong">
                        @if($course->discount_price)<small class="line-through muted">Rp{{ number_format($course->price, 0, ',', '.') }}</small><br>@endif
                        Rp{{ number_format($course->final_price, 0, ',', '.') }}
                    </td>
                    <td>{{ $course->items()->count() }}</td>
                    <td class="cell-actions">
                        <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-outline btn-xs">Ubah</a>
                        <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Hapus kursus {{ $course->title }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty">Tidak ada kursus yang cocok.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="panel-pagination">@include('partials.pagination', ['paginator' => $courses])</div>
</section>
@endsection
