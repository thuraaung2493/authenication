<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\EmailRegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::post('/{type}/login', LoginController::class)->name('social:login');

Route::post('/register', EmailRegisterController::class)->name('register');
