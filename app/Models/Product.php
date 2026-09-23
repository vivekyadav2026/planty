<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('home_bestsellers');
            \Illuminate\Support\Facades\Cache::forget('home_featured');
            \Illuminate\Support\Facades\Cache::forget('home_deal_of_week');
            \Illuminate\Support\Facades\Cache::forget('home_category_sections');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('home_bestsellers');
            \Illuminate\Support\Facades\Cache::forget('home_featured');
            \Illuminate\Support\Facades\Cache::forget('home_deal_of_week');
            \Illuminate\Support\Facades\Cache::forget('home_category_sections');
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        if (empty($slug)) {
            $slug = 'product-' . time();
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        $images = json_decode($this->images, true);
        if (!is_array($images)) {
            $images = !empty($this->images) ? [$this->images] : [];
        }
        $firstImg = count($images) > 0 ? $images[0] : null;
        if (empty($firstImg)) {
            return 'https://images.unsplash.com/photo-1416879598555-2591605c48b2?q=80&w=400&auto=format&fit=crop';
        }
        if (str_starts_with($firstImg, 'http://') || str_starts_with($firstImg, 'https://')) {
            return $firstImg;
        }
        return asset($firstImg);
    }

    public function getAllImageUrlsAttribute(): array
    {
        $images = json_decode($this->images, true);
        if (!is_array($images)) {
            $images = !empty($this->images) ? [$this->images] : [];
        }
        $urls = [];
        foreach ($images as $img) {
            if (empty($img)) continue;
            if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
                $urls[] = $img;
            } else {
                $urls[] = asset($img);
            }
        }
        return count($urls) > 0 ? $urls : ['https://images.unsplash.com/photo-1416879598555-2591605c48b2?q=80&w=400&auto=format&fit=crop'];
    }
}
