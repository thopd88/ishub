<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'name',
        'description',
        'type',
        'command',
        'cron_expression',
        'is_enabled',
        'not_before',
        'not_after',
        'last_run_at',
        'next_run_at',
        'options',
    ];

    protected $casts = [
        'is_enabled' => 'bool',
        'not_before' => 'datetime',
        'not_after' => 'datetime',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'options' => 'array',
    ];

    /**
     * @return HasMany<ModuleJobRun>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(ModuleJobRun::class);
    }

    /**
     * Scope for enabled jobs.
     *
     * @param  Builder<ModuleJob>  $query
     * @return Builder<ModuleJob>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }
}
