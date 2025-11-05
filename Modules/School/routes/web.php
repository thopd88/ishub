<?php

use Illuminate\Support\Facades\Route;
use Modules\School\Http\Controllers\AssignmentController;
use Modules\School\Http\Controllers\DashboardController;
use Modules\School\Http\Controllers\SubmissionController;

Route::middleware(['auth', 'verified'])
    ->prefix('school')
    ->name('school.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Assignment routes
        Route::resource('assignments', AssignmentController::class);

        // Submission routes
        Route::post('assignments/{assignment}/submissions', [SubmissionController::class, 'store'])
            ->name('submissions.store');
        Route::get('submissions/{submission}', [SubmissionController::class, 'show'])
            ->name('submissions.show');
        Route::post('submissions/{submission}/grade', [SubmissionController::class, 'grade'])
            ->name('submissions.grade');
    });
