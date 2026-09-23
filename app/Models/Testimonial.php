<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('home_testimonials');
        });

        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('home_testimonials');
        });
    }
}
