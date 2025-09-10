<?php

use Illuminate\Support\Facades\Route;
use BaoProd\Workforce\Http\Controllers\Api\Public\JobBoardSyncController;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
|
| Ces routes sont accessibles publiquement pour la synchronisation
| avec des systèmes externes (WordPress, autres JobBoards, etc.)
| Elles nécessitent une clé API valide.
|
*/

Route::prefix('public')->group(function () {
    
    // Job Board Sync - Public endpoints
    Route::prefix('jobs')->group(function () {
        // Get published jobs (for WordPress, etc.)
        Route::get('/', [JobBoardSyncController::class, 'getJobs']);
        
        // Get single job
        Route::get('/{id}', [JobBoardSyncController::class, 'getJob']);
        
        // Submit application from external source
        Route::post('/{jobId}/apply', [JobBoardSyncController::class, 'submitApplication']);
    });
    
    // Webhook endpoints for receiving data from external systems
    Route::prefix('webhook')->group(function () {
        Route::post('jobs', [JobBoardSyncController::class, 'receiveJobWebhook']);
        Route::post('applications', [JobBoardSyncController::class, 'receiveApplicationWebhook']);
    });
    
});