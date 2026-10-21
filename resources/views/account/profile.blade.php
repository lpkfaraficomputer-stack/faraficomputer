@extends('layouts.account')

@section('title', 'Profil')

@section('content')
<div class="page-intro">
    <div>
        <h1>Profil Saya</h1>
        <p>Perbarui informasi akun Anda.</p>
    </div>
</div>

<div class="admin-grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Data Akun</h2></div>
        <div class="panel-form">
            <form action="{{ route('account.profile.update') }}" method="POST">
                @csrf @method('PUT')

                <div class="form-group">
                    <label for="name">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="phone">No. WhatsApp</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                    @error('phone')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="panel-subhead">Ganti Password <small class="muted">(kosongkan bila tidak ingin mengubah)</small></div>

                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <input type="password" id="password" name="password" autocomplete="new-password">
                    @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </section>

    <aside class="summary-box">
        <h3>Ringkasan</h3>
        <div class="summary-row"><span>Role</span><span class="role-badge role-murid">Murid</span></div>
        <div class="summary-row"><span>Email</span><span>{{ $user->email }}</span></div>
        <div class="summary-row"><span>Bergabung</span><span>{{ $user->created_at->translatedFormat('d M Y') }}</span></div>
        <form action="{{ route('logout') }}" method="POST" style="margin-top: 1rem">
            @csrf
            <button type="submit" class="btn btn-outline btn-block">Keluar dari Akun</button>
        </form>
    </aside>
</div>
@endsection
