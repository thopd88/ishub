<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('module_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('module');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('type')->default('command');
            $table->string('command')->nullable();
            $table->string('cron_expression')->default('* * * * *');
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('not_before')->nullable();
            $table->timestamp('not_after')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable()->index();
            $table->json('options')->nullable();
            $table->timestamps();

            $table->unique(['module', 'name']);
        });

        Schema::create('module_job_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('module_job_id')->constrained('module_jobs')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('worker')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('output')->nullable();
            $table->text('exception_message')->nullable();
            $table->longText('exception_trace')->nullable();
            $table->timestamps();

            $table->index(['module_job_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_job_runs');
        Schema::dropIfExists('module_jobs');
    }
};
