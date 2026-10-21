<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    public const STATUS_BELUM_DIBAYAR = 'belum_dibayar';

    public const STATUS_DIBAYAR = 'dibayar';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    public const STATUSES = [
        self::STATUS_BELUM_DIBAYAR => 'Belum Dibayar',
        self::STATUS_DIBAYAR => 'Dibayar',
        self::STATUS_DIBATALKAN => 'Dibatalkan',
    ];

    /** Jenis tagihan yang umum dipakai Farafi Computer. */
    public const TYPES = [
        'spp' => 'SPP / Iuran Bulanan',
        'pendaftaran' => 'Biaya Pendaftaran',
        'kursus' => 'Biaya Kursus',
        'sertifikat' => 'Biaya Sertifikat',
        'praktikum' => 'Biaya Praktikum',
        'lainnya' => 'Lainnya',
    ];

    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'payment_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_DIBAYAR;
    }

    public function isPayable(): bool
    {
        return $this->status === self::STATUS_BELUM_DIBAYAR;
    }

    public function isOverdue(): bool
    {
        return $this->isPayable()
            && $this->due_date
            && $this->due_date->isPast();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
