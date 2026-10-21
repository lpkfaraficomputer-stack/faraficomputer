@extends('layouts.app')

@section('title', 'Masuk — Farafi Computer')

@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-head">
            <span class="auth-icon">🔐</span>
            <h1>Masuk ke Akun Anda</h1>
            <p>Akses kelas, pesanan, dan sertifikat Anda.</p>
        </div>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form action="{{ route('login.attempt') }}" method="POST" class="auth-form">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com">
                @error('email')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="••••••••">
                @error('password')<small class="form-error">{{ $message }}</small>@enderror
            </div>
            <label class="auth-check">
                <input type="checkbox" name="remember" value="1"> Ingat saya
            </label>
            <button type="submit" class="btn btn-primary btn-lg btn-block">Masuk</button>
        </form>

        <p class="auth-alt">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai murid</a></p>
    </div>
</div>
@endsection
