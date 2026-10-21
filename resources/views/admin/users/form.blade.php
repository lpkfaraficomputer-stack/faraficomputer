@extends('layouts.admin')

@section('title', $user->exists ? 'Ubah Pengguna' : 'Pengguna Baru')

@section('content')
<div class="page-intro">
    <div>
        <h1>{{ $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna Baru' }}</h1>
        <p>{{ $user->exists ? 'Perbarui data akun dan role pengguna.' : 'Buat akun super admin, operator, atau murid.' }}</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">← Kembali</a>
</div>

<section class="panel panel-form">
    <form method="{{ $user->exists ? 'POST' : 'POST' }}"
          action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div class="form-grid">
            <div class="form-group">
                <label for="name">Nama Lengkap *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="phone">No. WhatsApp</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
            </div>
            <div class="form-group">
                <label for="role">Role *</label>
                <select id="role" name="role" required>
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('role')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="password">Password {{ $user->exists ? '(kosongkan jika tidak diubah)' : '*' }}</label>
                <input type="password" id="password" name="password" {{ $user->exists ? '' : 'required' }} placeholder="Min. 8 karakter">
                @error('password')<small class="form-error">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">{{ $user->exists ? 'Simpan Perubahan' : 'Buat Pengguna' }}</button>
        </div>
    </form>
</section>

<div class="role-hint">
    <div><span class="role-badge role-super_admin">Super Admin</span> Akses penuh: pengguna, kategori, kursus, pesanan, dan dashboard.</div>
    <div><span class="role-badge role-operator">Operator</span> Kelola kursus dan pesanan saja.</div>
    <div><span class="role-badge role-murid">Murid</span> Beli kursus dan melihat pesanan sendiri.</div>
</div>
@endsection
