<?php

namespace App\Providers;

use App\Services\ModulePermissionService;
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
        // Register module permissions on boot
        $modulePermissionService = app(ModulePermissionService::class);
        $modulePermissionService->registerModuleRoles();
        $modulePermissionService->registerModulePermissions();
    }
}
