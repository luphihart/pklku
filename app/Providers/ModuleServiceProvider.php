<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

class ModuleServiceProvider extends ServiceProvider
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
        $modulesPath = app_path('Modules');

        if (!File::isDirectory($modulesPath)) {
            return;
        }

        $modules = array_map('basename', File::directories($modulesPath));

        foreach ($modules as $module) {
            $moduleLower = strtolower($module);

            // 1. Load Routes
            $webRouteFile = app_path("Modules/{$module}/Routes/web.php");
            if (File::exists($webRouteFile)) {
                Route::middleware('web')->group($webRouteFile);
            }

            $apiRouteFile = app_path("Modules/{$module}/Routes/api.php");
            if (File::exists($apiRouteFile)) {
                Route::middleware('api')->prefix('api')->group($apiRouteFile);
            }

            // 2. Load Views (namespaced, e.g., auth::login)
            $viewPath = app_path("Modules/{$module}/Views");
            if (File::isDirectory($viewPath)) {
                $this->loadViewsFrom($viewPath, $moduleLower);
            }

            // 3. Load Migrations
            $migrationPath = app_path("Modules/{$module}/Database/Migrations");
            if (File::isDirectory($migrationPath)) {
                $this->loadMigrationsFrom($migrationPath);
            }
        }
    }
}
