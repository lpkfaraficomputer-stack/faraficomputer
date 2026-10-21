@extends('layouts.admin')

@section('title', $course->exists ? 'Ubah Kursus' : 'Kursus Baru')

@section('content')
<div class="page-intro">
    <div>
        <h1>{{ $course->exists ? 'Ubah Kursus' : 'Tambah Kursus Baru' }}</h1>
        <p>Lengkapi informasi kursus agar tampil menarik di toko.</p>
    </div>
    <a href="{{ route('admin.courses.index') }}" class="btn btn-ghost">← Kembali</a>
</div>

<section class="panel panel-form">
    <form method="POST" enctype="multipart/form-data" action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}">
        @csrf
        @if($course->exists) @method('PUT') @endif

        <div class="form-grid">
            <div class="form-group">
                <label for="title">Judul Kursus *</label>
                <input type="text" id="title" name="title" value="{{ old('title', $course->title) }}" required>
                @error('title')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="category_id">Kategori *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">— Pilih kategori —</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $course->category_id) == $category->id)>{{ $category->icon }} {{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group form-group-full">
                <label for="image">Gambar Kursus (thumbnail)</label>
                @if($course->image)
                    <div class="image-preview">
                        <img src="{{ $course->image_url }}" alt="Pratinjau {{ $course->title }}">
                        <span class="muted">Gambar saat ini. Unggah baru untuk mengganti.</span>
                    </div>
                @endif
                <input type="file" id="image" name="image" accept="image/*">
                <small class="form-help">Unggah berkas JPG/PNG (maks. 2 MB) — atau tempelkan URL gambar di bawah.</small>
                @error('image')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group form-group-full">
                <label for="image_url_input">Atau URL Gambar</label>
                <input type="url" id="image_url_input" name="image_url" value="{{ old('image_url', \Illuminate\Support\Str::startsWith($course->image, ['http://', 'https://']) ? $course->image : '') }}" placeholder="https://contoh.com/gambar.jpg">
                <small class="form-help">Kosongkan jika memakai berkas yang diunggah. URL dipakai bila tidak ada berkas diunggah.</small>
                @error('image_url')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group form-group-full">
                <label for="short_description">Deskripsi Singkat *</label>
                <input type="text" id="short_description" name="short_description" value="{{ old('short_description', $course->short_description) }}" required maxlength="255">
                @error('short_description')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group form-group-full">
                <label for="description">Deskripsi Lengkap</label>
                <textarea id="description" name="description" rows="5">{{ old('description', $course->description) }}</textarea>
            </div>
            <div class="form-group">
                <label for="instructor">Nama Instruktur</label>
                <input type="text" id="instructor" name="instructor" value="{{ old('instructor', $course->instructor) }}">
            </div>
            <div class="form-group">
                <label for="level">Level *</label>
                <select id="level" name="level" required>
                    @foreach(['Pemula', 'Menengah', 'Lanjutan'] as $level)
                        <option value="{{ $level }}" @selected(old('level', $course->level) === $level)>{{ $level }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="price">Harga (Rp) *</label>
                <input type="number" id="price" name="price" value="{{ old('price', $course->price) }}" min="0" required>
                @error('price')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="discount_price">Harga Diskon (Rp)</label>
                <input type="number" id="discount_price" name="discount_price" value="{{ old('discount_price', $course->discount_price) }}" min="0" placeholder="Kosongkan jika tanpa diskon">
                @error('discount_price')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="duration_hours">Durasi (jam) *</label>
                <input type="number" id="duration_hours" name="duration_hours" value="{{ old('duration_hours', $course->duration_hours ?? 0) }}" min="0" required>
            </div>
            <div class="form-group">
                <label for="lessons">Jumlah Materi *</label>
                <input type="number" id="lessons" name="lessons" value="{{ old('lessons', $course->lessons ?? 0) }}" min="0" required>
            </div>
            <div class="form-group">
                <label for="rating">Rating (0–5) *</label>
                <input type="number" step="0.1" id="rating" name="rating" value="{{ old('rating', $course->rating ?? 0) }}" min="0" max="5" required>
            </div>
            <div class="form-group">
                <label for="students">Jumlah Siswa *</label>
                <input type="number" id="students" name="students" value="{{ old('students', $course->students ?? 0) }}" min="0" required>
            </div>
        </div>

        <label class="auth-check">
            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $course->is_featured))>
            Tampilkan sebagai kursus unggulan di beranda
        </label>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">{{ $course->exists ? 'Simpan Perubahan' : 'Buat Kursus' }}</button>
        </div>
    </form>
</section>
@endsection
