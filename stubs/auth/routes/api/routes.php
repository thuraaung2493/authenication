<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/**
 * Auth Routes
 */
Route::prefix('auth')->name('auth:')->group(
    base_path('/routes/api/auth.php')
);
