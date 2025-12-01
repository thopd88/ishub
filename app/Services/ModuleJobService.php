<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ModuleJob;
use Cron\CronExpression;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class ModuleJobService
{
    public function __construct(
        protected ModuleService $moduleService,
    ) {}

    /**
     * Sync jobs defined in module.json files into the database.
     */
    public function syncModuleJobs(): void
    {
        $modules = $this->moduleService->getEnabledModules();

        foreach ($modules as $module) {
            $moduleName = $module['name'] ?? null;
            if (! $moduleName) {
                continue;
            }

            $jobs = $this->getJobsForModule($moduleName);

            foreach ($jobs as $job) {
                $this->upsertModuleJob($moduleName, $job);
            }
        }
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getJobsForModule(string $moduleName): Collection
    {
        $moduleJsonPath = base_path('Modules/'.$moduleName.'/module.json');

        if (! File::exists($moduleJsonPath)) {
            return collect();
        }

        $moduleData = json_decode(File::get($moduleJsonPath), true);

        $jobs = $moduleData['jobs'] ?? [];

        return collect($jobs)->map(function (array $job): array {
            return [
                'name' => $job['name'] ?? null,
                'description' => $job['description'] ?? null,
                'type' => $job['type'] ?? 'command',
                'command' => $job['command'] ?? null,
                'cron_expression' => $job['cron'] ?? '* * * * *',
                'default_enabled' => $job['enabled'] ?? true,
                'options' => $job['options'] ?? [],
            ];
        })->filter(fn (array $job): bool => ! empty($job['name']));
    }

    /**
     * @param  array<string, mixed>  $job
     */
    protected function upsertModuleJob(string $moduleName, array $job): void
    {
        /** @var ModuleJob $existing */
        $existing = ModuleJob::query()
            ->where('module', $moduleName)
            ->where('name', $job['name'])
            ->first();

        $attributes = [
            'description' => $job['description'],
            'type' => $job['type'],
            'command' => $job['command'],
            'cron_expression' => $job['cron_expression'],
            'options' => $job['options'],
        ];

        if (! $existing) {
            $isEnabled = (bool) ($job['default_enabled'] ?? true);
            $nextRunAt = $this->calculateNextRunAt($job['cron_expression']);

            ModuleJob::query()->create(array_merge([
                'module' => $moduleName,
                'name' => $job['name'],
                'is_enabled' => $isEnabled,
                'next_run_at' => $nextRunAt,
            ], $attributes));

            return;
        }

        $existing->fill($attributes);

        // If cron expression changed, recalculate next run.
        if ($existing->isDirty('cron_expression')) {
            $existing->next_run_at = $this->calculateNextRunAt($existing->cron_expression);
        }

        $existing->save();
    }

    protected function calculateNextRunAt(string $cronExpression): ?Carbon
    {
        try {
            $cron = CronExpression::factory($cronExpression);

            return Carbon::instance($cron->getNextRunDate());
        } catch (\Throwable) {
            return null;
        }
    }
}
