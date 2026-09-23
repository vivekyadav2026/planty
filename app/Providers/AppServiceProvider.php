<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('frontend.partials.header', function ($view) {
            $categories = \Illuminate\Support\Facades\Cache::remember('active_categories', 3600, function() {
                return \App\Models\Category::where('is_active', true)->get();
            });
            $view->with('headerCategories', $categories);
        });
    }
}
