<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

Route::post('/{type}/login', LoginController::class)->name('login');

Route::delete('/logout', LogoutController::class)
    ->name('logout')
    ->middleware('auth:sanctum');
