<?php

use App\Http\Controllers\Staff\StaffAuthController;
use App\Http\Controllers\Staff\StaffDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('staff')->name('staff.')->group(function () {
    Route::get('login', [StaffAuthController::class, 'loginForm'])->name('login');
    Route::post('login', [StaffAuthController::class, 'login'])->name('login.post');
    Route::get('register', [StaffAuthController::class, 'registerForm'])->name('register');
    Route::post('register', [StaffAuthController::class, 'register'])->name('register.post');
    Route::post('send-otp', [StaffAuthController::class, 'sendOtp'])->name('send-otp');
    Route::post('verify-otp', [StaffAuthController::class, 'verifyOtp'])->name('verify-otp');

    Route::middleware(['auth', 'staff'])->group(function () {
        Route::post('logout', [StaffAuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
        Route::post('appointments/{booking}/accept', [StaffDashboardController::class, 'acceptAppointment'])->name('appointments.accept');
    });
});
