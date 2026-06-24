<?php

use Illuminate\Support\Facades\Route;
use Panelis\Database\Controllers\AuthController;
use Panelis\Database\Controllers\DatabaseController;

Route::get('/redirect', [AuthController::class, 'redirect'])->name('redirect');
Route::get('/callback', [AuthController::class, 'callback'])->name('callback');

Route::get('/download/{file}', [DatabaseController::class, 'download'])->name('download');
