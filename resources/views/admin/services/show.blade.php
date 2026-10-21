@extends('layouts.admin')

@section('title', 'Detail Permohonan Layanan')

@section('content')
<div class="page-intro">
    <div>
        <h1>{{ $request->ticket_number }}</h1>
        <p>
            {{ $service['icon'] ?? '🧰' }} {{ $request->service_name }}
            · diajukan {{ $request->created_at->translatedFormat('d M Y, H:i') }} WIB
        </p>
    </div>
    <a href="{{ route('admin.services.index') }}" class="btn btn-ghost">← Kembali</a>
</div>

<div class="admin-grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Data Permohonan</h2></div>

        <dl class="detail-list">
            <dt>Layanan</dt><dd>{{ $service['icon'] ?? '🧰' }} {{ $request->service_name }}</dd>
            <dt>Nama</dt><dd>{{ $request->name }}</dd>
            <dt>Email</dt><dd>{{ $request->email }}</dd>
            <dt>No. WhatsApp</dt><dd>{{ $request->phone }}</dd>
            @if($request->institution)<dt>Instansi</dt><dd>{{ $request->institution }}</dd>@endif
            @if($request->position)<dt>Jabatan / Kelas</dt><dd>{{ $request->position }}</dd>@endif
            @if($request->subject)<dt>Keperluan</dt><dd>{{ $request->subject }}</dd>@endif
            @if($request->start_date)<dt>Tanggal Mulai</dt><dd>{{ $request->start_date->translatedFormat('d F Y') }}</dd>@endif
            @if($request->end_date)<dt>Tanggal Selesai</dt><dd>{{ $request->end_date->translatedFormat('d F Y') }}</dd>@endif
            @if($request->user_id)
                <dt>Akun</dt><dd>{{ $request->user?->name ?? 'Akun terhapus' }}</dd>
            @else
                <dt>Akun</dt><dd><span class="muted">Pemohon tamu (tanpa akun)</span></dd>
            @endif
        </dl>

        @php($details = collect($request->details ?? [])->except(['institution', 'position', 'start_date', 'end_date']))
        @if($details->isNotEmpty())
            <h3 class="panel-subhead">Detail Khusus Layanan</h3>
            <dl class="detail-list">
                @foreach($details as $key => $value)
                    <dt>{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                    <dd>{{ is_array($value) ? implode(', ', $value) : $value }}</dd>
                @endforeach
            </dl>
        @endif

        @if($request->message)
            <h3 class="panel-subhead">Keterangan Tambahan</h3>
            <p class="muted" style="white-space:pre-line">{{ $request->message }}</p>
        @endif

        <div class="panel-foot">
            <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $request->phone)) }}?text={{ urlencode('Halo ' . $request->name . ', menindaklanjuti permohonan ' . $request->service_name . ' dengan nomor tiket ' . $request->ticket_number) }}"
               class="btn btn-primary" target="_blank">💬 Hubungi via WhatsApp</a>
            <a href="mailto:{{ $request->email }}?subject={{ urlencode('Permohonan ' . $request->service_name . ' — ' . $request->ticket_number) }}"
               class="btn btn-outline">✉️ Kirim Email</a>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Status &amp; Catatan</h2></div>

        <dl class="detail-list">
            <dt>Status Saat Ini</dt>
            <dd><span class="status status-service-{{ $request->status }}">{{ $statuses[$request->status] ?? $request->status }}</span></dd>
            @if($request->processed_at)
                <dt>Terakhir Diproses</dt><dd>{{ $request->processed_at->translatedFormat('d M Y, H:i') }} WIB</dd>
            @endif
        </dl>

        <form action="{{ route('admin.services.status', $request) }}" method="POST" class="status-form">
            @csrf @method('PATCH')
            <div class="form-group">
                <label for="status">Ubah Status</label>
                <select id="status" name="status">
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($request->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="admin_note">Catatan Petugas (dilihat pemohon)</label>
                <textarea id="admin_note" name="admin_note" rows="4" placeholder="Contoh: Silakan melengkapi surat pengantar dari sekolah.">{{ old('admin_note', $request->admin_note) }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Simpan Status</button>
        </form>

        <form action="{{ route('admin.services.destroy', $request) }}" method="POST" onsubmit="return confirm('Hapus permohonan {{ $request->ticket_number }}? Tindakan ini tidak bisa dibatalkan.')" style="margin-top:1rem">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-ghost btn-block" style="color:var(--red)">🗑️ Hapus Permohonan</button>
        </form>
    </section>
</div>
@endsection
