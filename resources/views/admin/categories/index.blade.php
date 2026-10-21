@extends('layouts.admin')

@section('title', 'Kategori')

@section('content')
<div class="page-intro">
    <div>
        <h1>Kategori Kursus</h1>
        <p>{{ $categories->count() }} kategori dipakai untuk mengelompokkan kursus di toko.</p>
    </div>
</div>

<div class="admin-grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Daftar Kategori</h2></div>
        <table class="data-table">
            <thead><tr><th>Kategori</th><th>Urutan</th><th>Kursus</th><th></th></tr></thead>
            <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td>
                            <span class="chip" style="--chip-color: {{ $category->color }}">{{ $category->icon }} {{ $category->name }}</span>
                            <br><small class="muted">{{ $category->description }}</small>
                        </td>
                        <td>{{ $category->sort_order }}</td>
                        <td class="cell-strong">{{ $category->courses_count }}</td>
                        <td class="cell-actions">
                            <button type="button" class="btn btn-outline btn-xs" onclick='editCategory({{ json_encode($category) }})'>Ubah</button>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="cell-empty">Belum ada kategori.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="panel panel-form">
        <div class="panel-head"><h2 id="formTitle">Tambah Kategori</h2></div>

        <form method="POST" action="{{ route('admin.categories.store') }}" id="categoryForm">
            @csrf
            @method('POST')
            <div class="form-grid">
                <div class="form-group">
                    <label for="cat-name">Nama *</label>
                    <input type="text" id="cat-name" name="name" required>
                    @error('name')<small class="form-error">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="cat-sort">Urutan</label>
                    <input type="number" id="cat-sort" name="sort_order" value="{{ old('sort_order', $categories->count() + 1) }}" min="0" required>
                </div>
                <div class="form-group">
                    <label for="cat-icon">Ikon (emoji)</label>
                    <input type="text" id="cat-icon" name="icon" value="{{ old('icon', '💻') }}" maxlength="10">
                </div>
                <div class="form-group">
                    <label for="cat-color">Warna</label>
                    <input type="color" id="cat-color" name="color" value="{{ old('color', '#4f46e5') }}" class="color-input">
                </div>
                <div class="form-group form-group-full">
                    <label for="cat-desc">Deskripsi</label>
                    <input type="text" id="cat-desc" name="description" value="{{ old('description') }}" maxlength="500">
                </div>
            </div>
            @error('color')<small class="form-error">{{ $message }}</small>@enderror
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg" id="formSubmit">Tambah Kategori</button>
                <button type="button" class="btn btn-ghost" id="formCancel" onclick="resetCategoryForm()" style="display:none">Batal Ubah</button>
            </div>
        </form>

        <div class="role-hint">
            <div>💡 Kategori hanya bisa dihapus jika tidak memiliki kursus di dalamnya.</div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    function editCategory(cat) {
        const form = document.getElementById('categoryForm');
        form.action = '/admin/kategori/' + cat.id;
        form.querySelector('[name="_method"]').value = 'PUT';
        document.getElementById('formTitle').textContent = 'Ubah Kategori: ' + cat.name;
        document.getElementById('cat-name').value = cat.name;
        document.getElementById('cat-sort').value = cat.sort_order;
        document.getElementById('cat-icon').value = cat.icon || '';
        document.getElementById('cat-color').value = cat.color || '#4f46e5';
        document.getElementById('cat-desc').value = cat.description || '';
        document.getElementById('formSubmit').textContent = 'Simpan Perubahan';
        document.getElementById('formCancel').style.display = '';
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function resetCategoryForm() {
        const form = document.getElementById('categoryForm');
        form.action = '{{ route('admin.categories.store') }}';
        form.querySelector('[name="_method"]').value = 'POST';
        form.reset();
        document.getElementById('formTitle').textContent = 'Tambah Kategori';
        document.getElementById('formSubmit').textContent = 'Tambah Kategori';
        document.getElementById('formCancel').style.display = 'none';
    }
</script>
@endpush
