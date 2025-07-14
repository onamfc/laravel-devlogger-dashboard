<?php

use Illuminate\Support\Facades\Route;
use Onamfc\DevLoggerDashboard\Http\Controllers\DashboardController;
use Onamfc\DevLoggerDashboard\Http\Controllers\LogController;

Route::get('/', [DashboardController::class, 'index'])->name('devlogger.dashboard');
Route::get('/logs/{log}', [LogController::class, 'show'])->name('devlogger.logs.show');
Route::delete('/logs/{log}', [LogController::class, 'destroy'])->name('devlogger.logs.destroy');
Route::post('/logs/bulk-action', [LogController::class, 'bulkAction'])->name('devlogger.logs.bulk-action');
Route::get('/logs/{log}/file', [LogController::class, 'openFile'])->name('devlogger.logs.open-file');