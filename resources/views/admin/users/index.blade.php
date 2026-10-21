@extends('layouts.admin')

@section('title', 'Pengguna')

@section('content')
<div class="page-intro">
    <div>
        <h1>Manajemen Pengguna</h1>
        <p>{{ $users->total() }} akun terdaftar — super admin, operator, dan murid.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Pengguna Baru</a>
</div>

<section class="panel">
    <div class="panel-filters">
        <form method="GET" class="filter-inline">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / email...">
            <select name="role" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                @foreach($roles as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr><th>Nama</th><th>Email</th><th>Role</th><th>Pesanan</th><th>Terdaftar</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td class="cell-strong">{{ $user->name }} @if($user->is(auth()->user()))<small class="muted">(Anda)</small>@endif</td>
                    <td>{{ $user->email }}<br><small class="muted">{{ $user->phone ?? '-' }}</small></td>
                    <td><span class="role-badge role-{{ $user->role }}">{{ $user->roleLabel() }}</span></td>
                    <td>{{ $user->orders()->count() }}</td>
                    <td><small class="muted">{{ $user->created_at->translatedFormat('d M Y') }}</small></td>
                    <td class="cell-actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline btn-xs">Ubah</a>
                        @if(!$user->is(auth()->user()))
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty">Tidak ada pengguna yang cocok.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="panel-pagination">@include('partials.pagination', ['paginator' => $users])</div>
</section>
@endsection
