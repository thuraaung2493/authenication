<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\EmailVerifyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\OtpResendController;

Route::post('/{type}/login', LoginController::class)->name('login');

Route::post('/register', RegisterController::class)->name('register');

Route::post('/email-verify', EmailVerifyController::class)->name('email-verify');

Route::post('/otp-resend', OtpResendController::class)->name('otp-resend');

Route::post('/forgot-password', ForgotPasswordController::class)->name('forgot-password');

Route::delete('/logout', LogoutController::class)
    ->name('logout')
    ->middleware('auth:sanctum');
