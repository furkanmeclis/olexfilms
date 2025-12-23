<?php

use App\Http\Controllers\Api\NexptgSyncController;
use App\Http\Controllers\Api\NexptgTestController;
use Illuminate\Support\Facades\Route;

Route::post('/nexptg/sync', [NexptgSyncController::class, 'sync'])
    ->middleware(['api', \App\Http\Middleware\NexptgBasicAuth::class]);

// Test endpoint (sadece development için - production'da kaldırılmalı)
if (app()->environment('local', 'testing')) {
    Route::get('/nexptg/test', [NexptgTestController::class, 'test'])
        ->middleware(['api']);
}
