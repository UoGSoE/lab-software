<?php

namespace App\Providers;

use Illuminate\Support\Facades;
use App\Models\Software;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

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
        Facades\View::composer('components.layouts.app', function (View $view) {
            $view->with('pendingDeletionCount', Cache::rememberForever('pendingDeletionCount', function () {
                return Software::pendingDeletion()->count();    
            }));
        });

    }
}
