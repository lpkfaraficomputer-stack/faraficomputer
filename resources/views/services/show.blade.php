@extends('layouts.app')

@section('title', $service['name'] . ' — Farafi Computer')

@section('content')
<div class="page-head page-head-sm" style="--svc-color: {{ $service['color'] }}">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Beranda</a> <span>›</span>
            <a href="{{ route('services.index') }}">Layanan</a> <span>›</span>
            <span>{{ $service['name'] }}</span>
        </nav>
        <h1>{{ $service['icon'] }} {{ $service['name'] }}</h1>
        <p>{{ $service['tagline'] }}</p>
    </div>
</div>

<div class="container page-body page-body-single">
    <section class="service-layout">
        <div class="service-form-card">
            <h2>Formulir {{ $service['name'] }}</h2>
            <p class="service-form-desc">{{ $service['description'] }}</p>

            @if($errors->any())
                <div class="alert alert-error">
                    <strong>Periksa kembali data Anda:</strong>
                    <ul style="margin:.4rem 0 0 1.1rem">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('services.store', $type) }}" method="POST" class="service-form">
                @csrf

                <div class="form-section">
                    <h3>👤 Data Pemohon</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Nama Lengkap *</label>
                            <input type="text" id="name" name="name" value="{{ old('name', auth()->user()?->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" value="{{ old('email', auth()->user()?->email) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">No. WhatsApp *</label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" placeholder="08xxxxxxxxxx" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>📋 Detail Permohonan</h3>
                    <div class="form-grid">
                        @foreach($service['fields'] as $field)
                            @php($value = old('fields.' . $field['name']))
                            <div class="form-group {{ $field['type'] === 'courses' ? 'form-group-full' : '' }}">
                                <label for="f-{{ $field['name'] }}">{{ $field['label'] }} {{ $field['required'] ? '*' : '' }}</label>

                                @if($field['type'] === 'select')
                                    <select id="f-{{ $field['name'] }}" name="fields[{{ $field['name'] }}]" {{ $field['required'] ? 'required' : '' }}>
                                        <option value="">— Pilih —</option>
                                        @foreach($field['options'] as $option)
                                            <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                @elseif($field['type'] === 'courses')
                                    <select id="f-{{ $field['name'] }}" name="fields[{{ $field['name'] }}]" {{ $field['required'] ? 'required' : '' }}>
                                        <option value="">— Pilih kelas —</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" @selected((string) $value === (string) $course->id)>
                                                {{ $course->title }} — Rp{{ number_format($course->final_price, 0, ',', '.') }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input
                                        type="{{ $field['type'] }}"
                                        id="f-{{ $field['name'] }}"
                                        name="fields[{{ $field['name'] }}]"
                                        value="{{ $value }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        {{ $field['required'] ? 'required' : '' }}
                                        {{ $field['type'] === 'number' ? 'min=1' : '' }}
                                    >
                                @endif
                            </div>
                        @endforeach

                        <div class="form-group form-group-full">
                            <label for="subject">Keperluan / Judul Pengajuan</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Contoh: Permohonan PKL jurusan RPL tahun ajaran 2026/2027">
                        </div>

                        <div class="form-group form-group-full">
                            <label for="message">Keterangan Tambahan</label>
                            <textarea id="message" name="message" rows="4" placeholder="Sampaikan informasi lain yang perlu kami ketahui...">{{ old('message') }}</textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Kirim Permohonan</button>
                <p class="service-form-note">Dengan mengirim formulir ini, Anda menyetujui data digunakan untuk memproses permohonan layanan.</p>
            </form>
        </div>

        <aside class="service-side">
            <div class="service-side-card">
                <h3>📌 Persyaratan</h3>
                <ul class="check-list">
                    @foreach($service['requirements'] as $requirement)
                        <li>{{ $requirement }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="service-side-card">
                <h3>⚙️ Alur Pengajuan</h3>
                <ol class="service-steps">
                    <li><span>1</span> Isi formulir permohonan</li>
                    <li><span>2</span> Terima nomor tiket (LYN-…)</li>
                    <li><span>3</span> Tim kami verifikasi 1×24 jam kerja</li>
                    <li><span>4</span> Konfirmasi via WhatsApp / email</li>
                </ol>
            </div>

            <div class="service-side-card">
                <h3>💬 Butuh Bantuan?</h3>
                <p class="muted">Hubungi kami bila ada pertanyaan seputar pengajuan ini.</p>
                <a href="https://wa.me/{{ config('services.agent.contact.whatsapp') }}" class="btn btn-primary btn-block" target="_blank">WhatsApp Kami</a>
                <a href="{{ route('services.track') }}" class="btn btn-outline btn-block" style="margin-top:.5rem">Cek Status Permohonan</a>
            </div>
        </aside>
    </section>
</div>
@endsection
