<?php

namespace App\Providers;

use App\Models\User;
use App\Models\AcademicSession;
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
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });
        // Model::preventLazyLoading(! app()->isProduction());
    }
}
