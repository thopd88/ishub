<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\HasModulePermissions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModulePermissionService
{
    protected ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Get all module permission providers from enabled modules.
     *
     * @return Collection<int, HasModulePermissions>
     */
    public function getModuleProviders(): Collection
    {
        $modules = $this->moduleService->getEnabledModules();
        $providers = collect();

        foreach ($modules as $module) {
            $moduleName = $module['name'] ?? null;
            if (! $moduleName) {
                continue;
            }

            $provider = $this->getProviderForModule($moduleName);
            if ($provider) {
                $providers->push($provider);
            }
        }

        return $providers;
    }

    /**
     * Get all roles from all enabled modules.
     *
     * @return array<string, array{module: string, name: string, description?: string, guard_name?: string}>
     */
    public function getAllModuleRoles(): array
    {
        $roles = [];
        $providers = $this->getModuleProviders();

        foreach ($providers as $provider) {
            $moduleRoles = $provider->getRoles();
            foreach ($moduleRoles as $role) {
                $roles[$role['name']] = [
                    'module' => $provider->getModuleName(),
                    'name' => $role['name'],
                    'description' => $role['description'] ?? null,
                    'guard_name' => $role['guard_name'] ?? 'web',
                ];
            }
        }

        return $roles;
    }

    /**
     * Get all permissions from all enabled modules.
     *
     * @return array<string, array{module: string, name: string, description?: string, guard_name?: string, roles?: string[]}>
     */
    public function getAllModulePermissions(): array
    {
        $permissions = [];
        $providers = $this->getModuleProviders();

        foreach ($providers as $provider) {
            $modulePermissions = $provider->getPermissions();
            foreach ($modulePermissions as $permission) {
                $permissions[$permission['name']] = [
                    'module' => $provider->getModuleName(),
                    'name' => $permission['name'],
                    'description' => $permission['description'] ?? null,
                    'guard_name' => $permission['guard_name'] ?? 'web',
                    'roles' => $permission['roles'] ?? [],
                ];
            }
        }

        return $permissions;
    }

    /**
     * Register all module roles in the database.
     */
    public function registerModuleRoles(): void
    {
        $roles = $this->getAllModuleRoles();

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                []
            );
        }
    }

    /**
     * Register all module permissions in the database.
     */
    public function registerModulePermissions(): void
    {
        $permissions = $this->getAllModulePermissions();

        foreach ($permissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name'], 'guard_name' => $permissionData['guard_name']],
                []
            );
        }
    }

    /**
     * Get a provider instance for a module.
     */
    protected function getProviderForModule(string $moduleName): ?HasModulePermissions
    {
        $moduleJsonPath = base_path('Modules/'.$moduleName.'/module.json');

        if (! File::exists($moduleJsonPath)) {
            return null;
        }

        $moduleData = json_decode(File::get($moduleJsonPath), true);

        // Check if module.json has roles and permissions defined
        if (isset($moduleData['roles']) || isset($moduleData['permissions'])) {
            return new JsonModulePermissionProvider($moduleName, $moduleData);
        }

        // Try to load a dedicated permission provider class
        $providerClass = "Modules\\{$moduleName}\\Providers\\PermissionProvider";
        if (class_exists($providerClass)) {
            $provider = app($providerClass);
            if ($provider instanceof HasModulePermissions) {
                return $provider;
            }
        }

        return null;
    }
}
