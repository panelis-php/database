<?php

use Illuminate\Support\Facades\Route;
use Panelis\Database\Controllers\DatabaseController;

Route::get('/download/{file}', [DatabaseController::class, 'download'])->name('download');
