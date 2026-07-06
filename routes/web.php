<?php

use Illuminate\Support\Facades\Route;
use Panelis\Database\Http\Controllers\DatabaseController;

Route::get('/download/{file}', [DatabaseController::class, 'download'])->name('download');
