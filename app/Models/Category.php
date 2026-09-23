<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('active_categories');
            \Illuminate\Support\Facades\Cache::forget('home_category_sections');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('active_categories');
            \Illuminate\Support\Facades\Cache::forget('home_category_sections');
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        if (empty($slug)) {
            $slug = 'category-' . time();
        }

        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function getImageUrlAttribute(): string
    {
        if (!empty($this->image)) {
            if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
                return $this->image;
            }
            return asset($this->image);
        }

        // Check if category has active products and use first product image as fallback
        $firstProduct = $this->products()->where('is_active', true)->first();
        if ($firstProduct) {
            return $firstProduct->primary_image_url;
        }
        return 'https://images.unsplash.com/photo-1416879598555-2591605c48b2?q=80&w=400&auto=format&fit=crop';
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
