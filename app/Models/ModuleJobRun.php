<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleJobRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_job_id',
        'status',
        'worker',
        'started_at',
        'finished_at',
        'output',
        'exception_message',
        'exception_trace',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<ModuleJob, ModuleJobRun>
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(ModuleJob::class, 'module_job_id');
    }
}
