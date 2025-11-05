<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class ModuleService
{
    protected string $modulesPath;

    protected string $statusFile;

    public function __construct()
    {
        $this->modulesPath = base_path('Modules');
        $this->statusFile = base_path('modules_statuses.json');
    }

    public function getEnabledModules(): array
    {
        if (! File::exists($this->statusFile)) {
            return [];
        }

        $statuses = json_decode(File::get($this->statusFile), true);

        return collect($statuses)
            ->filter(fn ($enabled) => $enabled === true)
            ->keys()
            ->map(fn ($moduleName) => $this->getModuleMetadata($moduleName))
            ->filter()
            ->values()
            ->all();
    }

    protected function getModuleMetadata(string $moduleName): ?array
    {
        $moduleJsonPath = $this->modulesPath.'/'.$moduleName.'/module.json';

        if (! File::exists($moduleJsonPath)) {
            return null;
        }

        $moduleData = json_decode(File::get($moduleJsonPath), true);

        return [
            'name' => $moduleData['name'] ?? $moduleName,
            'alias' => $moduleData['alias'] ?? strtolower($moduleName),
            'description' => $moduleData['description'] ?? '',
            'icon' => $moduleData['icon'] ?? 'Folder',
            'route' => $moduleData['route'] ?? '/'.strtolower($moduleName),
            'priority' => $moduleData['priority'] ?? 0,
        ];
    }

    public function getModulesForNavigation(): array
    {
        return collect($this->getEnabledModules())
            ->sortBy('priority')
            ->map(fn ($module) => [
                'title' => $module['name'],
                'href' => $module['route'],
                'icon' => $module['icon'],
            ])
            ->values()
            ->all();
    }
}
