<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
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
        view()->composer('components.layouts.app', function ($view) {
            $ttl = now()->addHours(12);
            $view->with('total_user_count', Cache::remember('total_user_count', $ttl, function () {
                return User::count();
            }));
        });

        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });
        // Model::preventLazyLoading(! app()->isProduction());
    }
}
