<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

Route::post('/{type}/login', LoginController::class)->name('login');

Route::post('/register', RegisterController::class)->name('register');

Route::delete('/logout', LogoutController::class)
    ->name('logout')
    ->middleware('auth:sanctum');
