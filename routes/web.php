<?php

use App\Http\Controllers\PlatformController;
use Illuminate\Support\Facades\Route;

Route::controller(PlatformController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/store', 'store')->name('store');
    Route::get('/services', 'services')->name('services');
    Route::post('/services/book', 'bookService')->name('services.book');
    Route::post('/garden-setup', [\App\Http\Controllers\GardenSetupController::class, 'store'])->name('garden.setup');
    Route::get('/subscriptions', 'subscriptions')->name('subscriptions');
    Route::get('/ai-tools', 'aiTools')->name('ai-tools');
    Route::post('/ai/ask', [\App\Http\Controllers\AiController::class, 'ask'])->name('ai.ask');
    Route::post('/reminders/create', [\App\Http\Controllers\ReminderController::class, 'store'])->name('reminders.create');
    Route::get('/reminders', 'reminders')->name('reminders');
    Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/operations', 'operations')->name('operations');
});

// Admin Panel Routes
require base_path('routes/admin.php');
