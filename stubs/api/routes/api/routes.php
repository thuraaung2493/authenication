<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/**
 * Auth Routes
 */
Route::prefix('auth')->name('auth:')->group(
    base_path('/routes/api/auth.php')
);

/**
 * API V1 Routes
 */
Route::prefix('v1')->name('v1:')->group(static function (): void {
    /**
     * User Routes
     */
    Route::prefix('users')->name('users:')->group(
        base_path('/routes/api/v1/users.php')
    );
});
