<?php

namespace App\Providers;

use App\Services\ModuleJobService;
use App\Services\ModulePermissionService;
use Illuminate\Support\Facades\Schema;
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
        if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            /** @var ModulePermissionService $modulePermissionService */
            $modulePermissionService = app(ModulePermissionService::class);
            $modulePermissionService->registerModuleRoles();
            $modulePermissionService->registerModulePermissions();
        }

        /** @var ModuleJobService $moduleJobService */
        $moduleJobService = app(ModuleJobService::class);
        $moduleJobService->syncModuleJobs();
    }
}
