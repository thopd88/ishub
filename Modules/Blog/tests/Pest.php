<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

uses(Tests\TestCase::class, RefreshDatabase::class)
    ->beforeEach(function () {
        // Run Blog module migrations after RefreshDatabase
        Artisan::call('migrate', [
            '--path' => 'Modules/Blog/database/migrations',
            '--realpath' => true,
        ]);
    })
    ->in('Feature');
