<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Dompdf;
use Barryvdh\DomPDF\PDF;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fix: Override dompdf binding so it uses the correct public_path().
        // On cPanel, bootstrap/app.php calls usePublicPath() which overrides
        // the public path binding. We must re-bind 'dompdf' AFTER that so
        // dompdf gets the correct webroot instead of the non-existent
        // /home/sina4714/pklku/public directory.
        $this->app->extend('dompdf', function ($dompdf, $app) {
            $path = realpath(public_path());
            if ($path !== false) {
                $dompdf->setBasePath($path);
            }
            return $dompdf;
        });
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
