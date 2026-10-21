@extends('layouts.admin')

@section('title', $invoice->exists ? 'Edit Tagihan' : 'Buat Tagihan')

@section('content')
<div class="page-intro">
    <div>
        <h1>{{ $invoice->exists ? 'Edit Tagihan' : 'Buat Tagihan Baru' }}</h1>
        <p>{{ $invoice->exists ? $invoice->invoice_number : 'Buat tagihan untuk murid tertentu.' }}</p>
    </div>
    <a href="{{ route('admin.invoices.index') }}" class="btn btn-ghost">← Kembali</a>
</div>

<section class="panel">
    <div class="panel-form">
        <form action="{{ $invoice->exists ? route('admin.invoices.update', $invoice) : route('admin.invoices.store') }}" method="POST">
            @csrf
            @if($invoice->exists)
                @method('PUT')
            @endif

            <div class="form-group">
                <label for="user_id">Murid</label>
                <select id="user_id" name="user_id" required>
                    <option value="">— Pilih murid —</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}"
                            @selected((int) old('user_id', $preselectedUserId ?? $invoice->user_id) === $student->id)>
                            {{ $student->name }} — {{ $student->email }}
                        </option>
                    @endforeach
                </select>
                @error('user_id')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="title">Judul Tagihan</label>
                <input type="text" id="title" name="title" value="{{ old('title', $invoice->title) }}"
                       placeholder="Contoh: SPP Kursus Bulan November 2026" required>
                @error('title')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="type">Jenis Tagihan</label>
                <select id="type" name="type" required>
                    @foreach($types as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $invoice->type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="amount">Nominal (Rp)</label>
                <input type="number" id="amount" name="amount" value="{{ old('amount', $invoice->amount) }}"
                       min="1000" step="1000" placeholder="150000" required>
                @error('amount')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="form-group">
                <label for="due_date">Jatuh Tempo</label>
                <input type="date" id="due_date" name="due_date"
                       value="{{ old('due_date', optional($invoice->due_date)->toDateString()) }}">
                @error('due_date')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="form-group form-group-full">
                <label for="description">Keterangan <small class="muted">(opsional)</small></label>
                <textarea id="description" name="description" rows="3" placeholder="Catatan tambahan untuk murid...">{{ old('description', $invoice->description) }}</textarea>
                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">{{ $invoice->exists ? 'Simpan Perubahan' : 'Buat Tagihan' }}</button>
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</section>
@endsection
