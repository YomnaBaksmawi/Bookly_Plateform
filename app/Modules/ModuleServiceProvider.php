<?php

namespace App\Modules;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register module services here if needed
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Routes are now handled by the main api.php file
        // No need to load separate module routes here
    }
}
