<?php

use Illuminate\Support\Facades\Route;
use Onamfc\DevLoggerDashboard\Http\Controllers\DashboardController;
use Onamfc\DevLoggerDashboard\Http\Controllers\LogController;

Route::get('/', [DashboardController::class, 'index'])->name('devlogger.dashboard');
Route::get('/db-logs/{log}', [LogController::class, 'show'])->name('devlogger.db-logs.show');
Route::delete('/db-logs/{log}', [LogController::class, 'destroy'])->name('devlogger.db-logs.destroy');
Route::post('/db-logs/bulk-action', [LogController::class, 'bulkAction'])->name('devlogger.db-logs.bulk-action');
Route::get('/db-logs/{log}/file', [LogController::class, 'openFile'])->name('devlogger.db-logs.open-file');