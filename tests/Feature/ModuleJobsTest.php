<?php

declare(strict_types=1);

use App\Console\Commands\RunDueModuleJobs;
use App\Models\ModuleJob;
use App\Models\User;
use App\Services\ModuleJobService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('syncs jobs from module json into database', function (): void {
    $this->artisan('migrate', ['--path' => 'database/migrations/2025_12_01_000000_create_module_jobs_tables.php']);
    expect(Schema::hasTable('module_jobs'))->toBeTrue();

    // Fake module.json for Blog module
    $path = base_path('Modules/Blog/module.json');
    $original = File::get($path);

    try {
        $data = json_decode($original, true, flags: JSON_THROW_ON_ERROR);
        $data['jobs'] = [
            [
                'name' => 'blog.clean_drafts',
                'description' => 'Clean old draft posts',
                'type' => 'command',
                'command' => 'blog:clean-drafts',
                'cron' => '0 * * * *',
                'enabled' => true,
            ],
        ];

        File::put($path, json_encode($data, JSON_PRETTY_PRINT));

        /** @var ModuleJobService $service */
        $service = app(ModuleJobService::class);
        $service->syncModuleJobs();

        $job = ModuleJob::query()->where('module', 'Blog')->where('name', 'blog.clean_drafts')->first();

        expect($job)->not->toBeNull();
        expect($job->command)->toBe('blog:clean-drafts');
        expect($job->cron_expression)->toBe('0 * * * *');
        expect($job->is_enabled)->toBeTrue();
    } finally {
        File::put($path, $original);
    }
});

it('runs due module jobs command and records history', function (): void {
    $this->artisan('migrate', ['--path' => 'database/migrations/2025_12_01_000000_create_module_jobs_tables.php']);
    expect(Schema::hasTable('module_jobs'))->toBeTrue();

    // Create a fake job that is due now
    $job = ModuleJob::query()->create([
        'module' => 'Blog',
        'name' => 'blog.clean_drafts',
        'type' => 'command',
        'command' => 'inspire',
        'cron_expression' => '* * * * *',
        'is_enabled' => true,
        'next_run_at' => Carbon::now()->subMinute(),
    ]);

    Artisan::call(RunDueModuleJobs::class);

    $job->refresh();

    expect($job->last_run_at)->not->toBeNull();
    expect($job->runs)->toHaveCount(1);
    expect($job->runs->first()->status)->toBe('success');
});
