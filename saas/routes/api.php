<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use BaoProd\Workforce\Http\Controllers\Api\AuthController;
use BaoProd\Workforce\Http\Controllers\Api\JobController;
use BaoProd\Workforce\Http\Controllers\Api\ApplicationController;
use BaoProd\Workforce\Http\Controllers\Api\ModuleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
    });

    // Public job listings (with tenant middleware for public access)
    Route::middleware(['tenant'])->group(function () {
        Route::get('jobs', [JobController::class, 'index']);
        Route::get('jobs/{job}', [JobController::class, 'show']);
    });
    
    // Test route without tenant middleware
    Route::get('test', function () {
        return response()->json([
            'message' => 'API is working!',
            'timestamp' => now(),
            'version' => '1.0.0'
        ]);
    });
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::put('password', [AuthController::class, 'changePassword']);
    });

    // Tenant-specific routes
    Route::middleware(['tenant'])->group(function () {
        
        // Job management
        Route::apiResource('jobs', JobController::class)->except(['index', 'show']);
        Route::get('jobs/statistics', [JobController::class, 'statistics']);
        
        // Application management
        Route::apiResource('applications', ApplicationController::class);
        
        // Module management
        Route::prefix('modules')->group(function () {
            Route::get('/', [ModuleController::class, 'index']);
            Route::get('active', [ModuleController::class, 'active']);
            Route::post('{module}/activate', [ModuleController::class, 'activate']);
            Route::delete('{module}/deactivate', [ModuleController::class, 'deactivate']);
        });
    });
});

// Module-specific routes (conditional based on tenant modules)
Route::prefix('v1')->middleware(['auth:sanctum', 'tenant'])->group(function () {
    
    // Contrats module routes
    Route::middleware(['tenant:contrats'])->prefix('contrats')->group(function () {
        // Contract routes will be added here
    });
    
    // Timesheets module routes
    Route::middleware(['tenant:timesheets'])->prefix('timesheets')->group(function () {
        // Timesheet routes will be added here
    });
    
    // Paie module routes
    Route::middleware(['tenant:paie'])->prefix('paie')->group(function () {
        // Payroll routes will be added here
    });
    
    // Absences module routes
    Route::middleware(['tenant:absences'])->prefix('absences')->group(function () {
        // Absence routes will be added here
    });
    
    // Reporting module routes
    Route::middleware(['tenant:reporting'])->prefix('reporting')->group(function () {
        // Reporting routes will be added here
    });
    
    // Messagerie module routes
    Route::middleware(['tenant:messagerie'])->prefix('messagerie')->group(function () {
        // Messaging routes will be added here
    });
});

// Health check route
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});