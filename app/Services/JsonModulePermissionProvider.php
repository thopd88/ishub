<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\HasModulePermissions;

class JsonModulePermissionProvider implements HasModulePermissions
{
    protected string $moduleName;

    protected array $moduleData;

    public function __construct(string $moduleName, array $moduleData)
    {
        $this->moduleName = $moduleName;
        $this->moduleData = $moduleData;
    }

    public function getModuleName(): string
    {
        return $this->moduleData['name'] ?? $this->moduleName;
    }

    public function getModuleAlias(): string
    {
        return $this->moduleData['alias'] ?? strtolower($this->moduleName);
    }

    public function getRoles(): array
    {
        $roles = $this->moduleData['roles'] ?? [];
        $result = [];

        foreach ($roles as $role) {
            if (is_string($role)) {
                $result[] = [
                    'name' => $role,
                    'guard_name' => 'web',
                ];
            } elseif (is_array($role)) {
                $result[] = [
                    'name' => $role['name'] ?? '',
                    'description' => $role['description'] ?? null,
                    'guard_name' => $role['guard_name'] ?? 'web',
                ];
            }
        }

        return $result;
    }

    public function getPermissions(): array
    {
        $permissions = $this->moduleData['permissions'] ?? [];
        $result = [];

        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $result[] = [
                    'name' => $permission,
                    'guard_name' => 'web',
                ];
            } elseif (is_array($permission)) {
                $result[] = [
                    'name' => $permission['name'] ?? '',
                    'description' => $permission['description'] ?? null,
                    'guard_name' => $permission['guard_name'] ?? 'web',
                    'roles' => $permission['roles'] ?? [],
                ];
            }
        }

        return $result;
    }
}
