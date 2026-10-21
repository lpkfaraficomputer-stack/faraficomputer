@extends('layouts.admin')

@section('title', 'Layanan')

@section('content')
<div class="page-intro">
    <div>
        <h1>Permohonan Layanan</h1>
        <p>{{ $requests->total() }} permohonan — PKL, pendaftaran kursus, MOU, kunjungan, sertifikasi &amp; bantuan teknis.</p>
    </div>
</div>

<section class="panel">
    <div class="panel-filters">
        <form method="GET" class="filter-inline">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari no. tiket / nama / email / instansi...">
            <select name="jenis" onchange="this.form.submit()">
                <option value="">Semua Layanan</option>
                @foreach($services as $value => $service)
                    <option value="{{ $value }}" @selected(request('jenis') === $value)>
                        {{ $service['icon'] }} {{ $service['name'] }}
                    </option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>
                        {{ $label }} ({{ $statusCounts[$value] ?? 0 }})
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr><th>No. Tiket</th><th>Layanan</th><th>Pemohon</th><th>Instansi</th><th>Status</th><th>Tanggal</th></tr>
        </thead>
        <tbody>
            @forelse($requests as $req)
                <tr>
                    <td><a href="{{ route('admin.services.show', $req) }}" class="cell-link cell-strong">{{ $req->ticket_number }}</a></td>
                    <td>
                        {{ $services[$req->service_type]['icon'] ?? '🧰' }}
                        {{ $req->service_name }}
                    </td>
                    <td>{{ $req->name }}<br><small class="muted">{{ $req->email }}</small></td>
                    <td>{{ $req->institution ?? '—' }}</td>
                    <td><span class="status status-service-{{ $req->status }}">{{ $statuses[$req->status] ?? $req->status }}</span></td>
                    <td><small class="muted">{{ $req->created_at->translatedFormat('d M Y H:i') }}</small></td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty">Belum ada permohonan layanan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="panel-pagination">@include('partials.pagination', ['paginator' => $requests])</div>
</section>
@endsection
