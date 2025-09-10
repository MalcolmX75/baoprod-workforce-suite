<?php

use Illuminate\Support\Facades\Route;
use BaoProd\Workforce\Http\Controllers\Web\AuthController;
use BaoProd\Workforce\Http\Controllers\Web\DashboardController;
use BaoProd\Workforce\Http\Controllers\Web\UserController;
use BaoProd\Workforce\Http\Controllers\Web\JobController;

// Landing page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');
    Route::get('/dashboard/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');
    
    // Profile
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    
    // User management
    Route::resource('users', UserController::class);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
    
    // Contract management (placeholder routes)
    Route::get('/contrats', function() {
        return redirect()->route('dashboard')->with('info', 'Gestion des contrats en développement');
    })->name('contrats.index');
    
    Route::get('/contrats/create', function() {
        return redirect()->route('dashboard')->with('info', 'Création de contrat en développement');
    })->name('contrats.create');
    
    // Job management
    Route::resource('jobs', JobController::class);
    Route::patch('/jobs/{job}/status', [JobController::class, 'updateStatus'])->name('jobs.status');
    Route::post('/jobs/{job}/duplicate', [JobController::class, 'duplicate'])->name('jobs.duplicate');
});
