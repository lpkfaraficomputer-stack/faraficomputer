<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_featured' => 'boolean',
        'rating' => 'float',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFinalPriceAttribute(): int
    {
        return $this->discount_price ?? $this->price;
    }

    /**
     * URL gambar thumbnail. Bila belum ada gambar, kembalikan null
     * sehingga tampilan memakai latar gradien kategori sebagai cadangan.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        // Gambar eksternal (URL penuh) dipakai apa adanya.
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/'.ltrim($this->image, '/'));
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (! $this->discount_price || $this->discount_price >= $this->price) {
            return null;
        }

        return (int) round((1 - $this->discount_price / $this->price) * 100);
    }
}
