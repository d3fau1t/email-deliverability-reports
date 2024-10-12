<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;

Route::prefix('email-deliverability-reports')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Download
    Route::get('/download', [DownloadController::class, 'download'])->name('download');
});

