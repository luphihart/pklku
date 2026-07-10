<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        // Share global branding settings to all views
        View::composer('*', function ($view) {
            static $globalSettings = null;
            if ($globalSettings === null) {
                try {
                    $globalSettings = \App\Modules\Setting\Models\Setting::pluck('value', 'key')->all();
                } catch (\Throwable $e) {
                    $globalSettings = [];
                }
            }
            $view->with('globalSettings', $globalSettings);
        });
    }
}
