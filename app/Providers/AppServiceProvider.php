<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\Software;
use Illuminate\View\View;
use Illuminate\Support\Facades;
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

        Blade::if('editingEnabled', function () {
            return Cache::remember('editingEnabled', now()->addHours(1), function () {
                $isAdmin = auth()->check() && auth()->user()->isAdmin();
                $isEditingEnabled = Setting::getSetting('notifications_system_open_date')?->toDate()?->isPast() && Setting::getSetting('notifications_system_close_date')?->toDate()?->isFuture();
                return $isAdmin || $isEditingEnabled;
            });
        });

        // Model::preventLazyLoading(! app()->isProduction());
        Facades\View::composer('components.layouts.app', function (View $view) {
            $view->with('pendingDeletionCount', Cache::rememberForever('pendingDeletionCount', function () {
                return Software::pendingDeletion()->count();    
            }));
        });

    }
}
