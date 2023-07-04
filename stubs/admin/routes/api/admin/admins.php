<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\Admins\AdminRolesAndPermissionsController;
use App\Http\Controllers\Admin\Admins\DeleteController;
use App\Http\Controllers\Admin\Admins\IndexController;
use App\Http\Controllers\Admin\Admins\ShowController;
use App\Http\Controllers\Admin\Admins\StoreController;
use App\Http\Controllers\Admin\Admins\UpdateController;
use Illuminate\Support\Facades\Route;

Route::get(
    uri: '/',
    action: IndexController::class,
)->name('index');

Route::post(
    uri: '/',
    action: StoreController::class,
)->name('store');

Route::get(
    uri: '/{admin}',
    action: ShowController::class,
)->name('show');

Route::get(
    uri: '/roles/permissions',
    action: AdminRolesAndPermissionsController::class,
)->name('roles-permissions');

Route::put(
    uri: '/{admin}',
    action: UpdateController::class,
)->name('update');

Route::delete(
    uri: '/{admin}',
    action: DeleteController::class,
)->name('destroy');
