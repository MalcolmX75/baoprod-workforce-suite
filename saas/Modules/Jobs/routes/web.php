<?php

use Illuminate\Support\Facades\Route;
use Modules\Jobs\Http\Controllers\JobsController;
use Modules\Jobs\Http\Controllers\CategoryController;
use Modules\Jobs\Http\Controllers\ApplicationController;

Route::middleware(['auth', 'verified'])->prefix('dashboard/jobs')->name('jobs.')->group(function () {
    // Category Routes
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Job Routes
    Route::resource('/', JobsController::class, ['parameters' => ['' => 'job']])->names('posts');

    // Application Routes
    Route::get('{job}/applications', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::put('applications/{application}', [ApplicationController::class, 'update'])->name('applications.update');
});
