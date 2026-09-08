<?php

use App\Http\Controllers\Staff\StaffAuthController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Staff\OrderController;
use App\Http\Controllers\Staff\StaffProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('staff')->name('staff.')->group(function () {
    Route::get('login', [StaffAuthController::class, 'loginForm'])->name('login');
    Route::post('login', [StaffAuthController::class, 'login'])->name('login.post');
    Route::get('register', [StaffAuthController::class, 'registerForm'])->name('register');
    Route::post('register', [StaffAuthController::class, 'register'])->name('register.post');
    Route::get('forgot-password', [StaffAuthController::class, 'forgotPasswordForm'])->name('forgot-password');
    Route::post('forgot-password', [StaffAuthController::class, 'sendResetLink'])->name('forgot-password.post');
    Route::get('reset-password/{token}', [StaffAuthController::class, 'resetPasswordForm'])->name('reset-password');
    Route::post('reset-password', [StaffAuthController::class, 'resetPassword'])->name('reset-password.post');
    Route::post('send-otp', [StaffAuthController::class, 'sendOtp'])->name('send-otp');
    Route::post('verify-otp', [StaffAuthController::class, 'verifyOtp'])->name('verify-otp');

    Route::middleware(['auth', 'staff'])->group(function () {
        Route::post('logout', [StaffAuthController::class, 'logout'])->name('logout');
        Route::get('change-password', [StaffAuthController::class, 'changePasswordForm'])->name('change-password');
        Route::post('change-password', [StaffAuthController::class, 'changePassword'])->name('change-password.post');
        Route::get('dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
        Route::get('profile', [StaffProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [StaffProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/location', [StaffProfileController::class, 'updateLocation'])->name('profile.location');
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('appointments/{booking}/accept', [StaffDashboardController::class, 'acceptAppointment'])->name('appointments.accept');
    });
});
