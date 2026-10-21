@extends('layouts.app')

@section('title', 'Daftar — Farafi Computer')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-head">
            <span class="auth-icon">🎓</span>
            <h1>Daftar Akun Murid</h1>
            <p>Beli kursus, pantau pesanan, dan kumpulkan sertifikat.</p>
        </div>

        <form action="{{ route('register.store') }}" method="POST" class="auth-form">
            @csrf
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Contoh: Dewi Lestari">
                @error('name')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com">
                @error('email')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="phone">No. WhatsApp</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                @error('phone')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Min. 8 karakter">
                @error('password')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation">Ulangi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ketik ulang password">
            </div>
            <button type="submit" class="btn btn-primary btn-lg btn-block">Buat Akun</button>
        </form>

        <p class="auth-alt">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
    </div>
</div>
@endsection
