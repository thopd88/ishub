<?php

namespace App\Console\Commands;

use App\Models\ModuleJob;
use App\Models\ModuleJobRun;
use Cron\CronExpression;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class RunDueModuleJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module-jobs:run-due';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all due module-defined jobs.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        DB::transaction(function () use ($now): void {
            /** @var \Illuminate\Database\Eloquent\Collection<int, ModuleJob> $jobs */
            $jobs = ModuleJob::query()
                ->enabled()
                ->where(function ($query) use ($now): void {
                    $query->whereNull('not_before')
                        ->orWhere('not_before', '<=', $now);
                })
                ->where(function ($query) use ($now): void {
                    $query->whereNull('not_after')
                        ->orWhere('not_after', '>=', $now);
                })
                ->where(function ($query) use ($now): void {
                    $query->whereNull('next_run_at')
                        ->orWhere('next_run_at', '<=', $now);
                })
                ->lockForUpdate()
                ->get();

            foreach ($jobs as $job) {
                $this->runJob($job, $now);
            }
        });

        return self::SUCCESS;
    }

    protected function runJob(ModuleJob $job, Carbon $now): void
    {
        $jobRun = ModuleJobRun::query()->create([
            'module_job_id' => $job->id,
            'status' => 'running',
            'worker' => gethostname(),
            'started_at' => $now,
        ]);

        $output = null;

        try {
            if ($job->type === 'command' && $job->command) {
                $this->info(sprintf('Running module job %s:%s', $job->module, $job->name));

                Artisan::call($job->command);
                $output = Artisan::output();
            } else {
                $output = 'Unsupported job type or missing configuration.';
            }

            $jobRun->update([
                'status' => 'success',
                'finished_at' => Carbon::now(),
                'output' => $output,
            ]);
        } catch (\Throwable $e) {
            $jobRun->update([
                'status' => 'failed',
                'finished_at' => Carbon::now(),
                'output' => $output,
                'exception_message' => $e->getMessage(),
                'exception_trace' => $e->getTraceAsString(),
            ]);
        } finally {
            $job->last_run_at = $now;
            $job->next_run_at = $this->calculateNextRunAt($job->cron_expression, $now);
            $job->save();
        }
    }

    protected function calculateNextRunAt(string $cronExpression, Carbon $from): ?Carbon
    {
        try {
            $cron = CronExpression::factory($cronExpression);

            return Carbon::instance($cron->getNextRunDate($from));
        } catch (\Throwable) {
            return null;
        }
    }
}
