<?php

use App\Http\Controllers\PlatformController;
use App\Http\Controllers\SubscriptionPaymentController;
use Illuminate\Support\Facades\Route;

Route::controller(PlatformController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/store', 'store')->name('store');
    Route::get('/services', 'services')->name('services');
    Route::post('/services/book', 'bookService')->name('services.book');
    Route::post('/garden-setup', [\App\Http\Controllers\GardenSetupController::class, 'store'])->name('garden.setup');
    Route::get('/subscriptions', 'subscriptions')->name('subscriptions');
    Route::post('/subscriptions/checkout', [SubscriptionPaymentController::class, 'checkout'])->name('subscriptions.checkout');
    Route::post('/subscriptions/pay', [SubscriptionPaymentController::class, 'pay'])->name('subscriptions.pay');
    Route::post('/subscriptions/verify', [SubscriptionPaymentController::class, 'verify'])->name('subscriptions.verify');
    Route::get('/ai-tools', 'aiTools')->name('ai-tools');
    Route::post('/ai/ask', [\App\Http\Controllers\AiController::class, 'ask'])->name('ai.ask');
    Route::post('/reminders/create', [\App\Http\Controllers\ReminderController::class, 'store'])->name('reminders.create');
    Route::get('/reminders', 'reminders')->name('reminders');
    Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/operations', 'operations')->name('operations');
});

Route::middleware(['web'])->group(function (): void {
    Route::get('/auth/google/redirect', [\App\Http\Controllers\CustomerAuthController::class, 'googleRedirect'])->name('customer.google.redirect');
    Route::get('/customer/google/redirect', [\App\Http\Controllers\CustomerAuthController::clyass, 'googleRedirect']);
    Route::get('/auth/google/callback', [\App\Http\Controllers\CustomerAuthController::class, 'googleCallback'])->name('customer.google.callback');
    Route::get('/customer/google/callback', [\App\Http\Controllers\CustomerAuthController::class, 'googleCallback']);
    Route::get('/customer/login', [\App\Http\Controllers\CustomerAuthController::class, 'loginForm'])->name('customer.login');
    Route::post('/customer/login', [\App\Http\Controllers\CustomerAuthController::class, 'login'])->name('customer.login.post');
    Route::get('/customer/register', [\App\Http\Controllers\CustomerAuthController::class, 'registerForm'])->name('customer.register');
    Route::post('/customer/register', [\App\Http\Controllers\CustomerAuthController::class, 'register'])->name('customer.register.post');

    Route::get('/customer/cart', [\App\Http\Controllers\CustomerAccountController::class, 'cart'])->name('customer.cart');
    Route::post('/customer/cart/add', [\App\Http\Controllers\CustomerAccountController::class, 'addToCart'])->name('customer.cart.add');
    Route::post('/customer/cart/{cartItem}/update', [\App\Http\Controllers\CustomerAccountController::class, 'updateCart'])->name('customer.cart.update');
    Route::delete('/customer/cart/{cartItem}/remove', [\App\Http\Controllers\CustomerAccountController::class, 'removeCartItem'])->name('customer.cart.remove');
    Route::get('/customer/orders', [\App\Http\Controllers\CustomerAccountController::class, 'orders'])->name('customer.orders');
    Route::post('/customer/checkout', [\App\Http\Controllers\CustomerAccountController::class, 'checkout'])->name('customer.checkout');
    Route::get('/customer/saved-products', [\App\Http\Controllers\CustomerAccountController::class, 'savedProducts'])->name('customer.saved-products');
    Route::post('/customer/saved-products/save', [\App\Http\Controllers\CustomerAccountController::class, 'saveProduct'])->name('customer.saved-products.save');
    Route::delete('/customer/saved-products/{savedProduct}/remove', [\App\Http\Controllers\CustomerAccountController::class, 'removeSavedProduct'])->name('customer.saved-products.remove');
    Route::get('/customer/profile', [\App\Http\Controllers\CustomerAccountController::class, 'profile'])->name('customer.profile');
});

// Admin Panel Routes
require base_path('routes/admin.php');

// Staff Panel Routes
require base_path('routes/staff.php');
