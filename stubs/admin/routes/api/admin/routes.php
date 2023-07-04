<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/**
 * Admin Auth Routes
 */
Route::prefix('auth')->as('auth:')->group(
    base_path('/routes/api/admin/auth.php')
);

Route::middleware('auth:admin')->group(function (): void {

    /**
     * App Versions Routes
     */
    Route::prefix('app-versions')->as('app-versions:')->group(
        base_path('/routes/api/admin/app-versions.php')
    );

    /**
     * Users Routes
     */
    Route::prefix('users')->as('users:')->group(
        base_path('/routes/api/admin/users.php')
    );

    /**
     * Admins Routes
     */
    Route::prefix('admins')->as('admins:')->group(
        base_path('/routes/api/admin/admins.php')
    );

    /**
     * Roles Routes
     */
    Route::prefix('roles')->as('roles:')->group(
        base_path('/routes/api/admin/roles.php')
    );

    /**
     * Permissions Routes
     */
    Route::prefix('permissions')->as('permissions:')->group(
        base_path('/routes/api/admin/permissions.php')
    );
});
