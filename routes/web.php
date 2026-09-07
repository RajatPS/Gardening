<?php

use App\Http\Controllers\PlatformController;
use App\Http\Controllers\SubscriptionPaymentController;
use Illuminate\Support\Facades\Route;

Route::controller(PlatformController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/store', 'store')->name('store');
    Route::get('/products/{slug}', 'productDetails')->name('product.details');
    Route::get('/services', 'services')->name('services');
    Route::post('/services/book', 'bookService')->name('services.book')->middleware('customer.auth');
    Route::post('/garden-setup', [\App\Http\Controllers\GardenSetupController::class, 'store'])->name('garden.setup')->middleware('customer.auth');
    Route::get('/subscriptions', 'subscriptions')->name('subscriptions');
    Route::post('/subscriptions/checkout', [SubscriptionPaymentController::class, 'checkout'])->name('subscriptions.checkout')->middleware('customer.auth');
    Route::post('/subscriptions/pay', [SubscriptionPaymentController::class, 'pay'])->name('subscriptions.pay')->middleware('customer.auth');
    Route::post('/subscriptions/verify', [SubscriptionPaymentController::class, 'verify'])->name('subscriptions.verify')->middleware('customer.auth');
    Route::get('/ai-tools', 'aiTools')->name('ai-tools');
    Route::post('/ai/ask', [\App\Http\Controllers\AiController::class, 'ask'])->name('ai.ask');
    Route::post('/reminders/create', [\App\Http\Controllers\ReminderController::class, 'store'])->name('reminders.create')->middleware('customer.auth');
    Route::get('/reminders', 'reminders')->name('reminders');
    Route::get('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/operations', 'operations')->name('operations');
});

// Provide a named `login` route fallback so the default `auth` middleware
// can redirect unauthenticated users safely. Redirects to the customer login.
Route::get('/login', function () {
    return redirect()->route('customer.login');
})->name('login');

Route::get('/auth/google/redirect', [\App\Http\Controllers\CustomerAuthController::class, 'googleRedirect'])->name('customer.google.redirect');
Route::get('/customer/google/redirect', [\App\Http\Controllers\CustomerAuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [\App\Http\Controllers\CustomerAuthController::class, 'googleCallback'])->name('customer.google.callback');
Route::get('/customer/google/callback', [\App\Http\Controllers\CustomerAuthController::class, 'googleCallback']);
Route::get('/customer/login', [\App\Http\Controllers\CustomerAuthController::class, 'loginForm'])->name('customer.login');
Route::post('/customer/login', [\App\Http\Controllers\CustomerAuthController::class, 'login'])->name('customer.login.post');
Route::get('/customer/register', [\App\Http\Controllers\CustomerAuthController::class, 'registerForm'])->name('customer.register');
Route::post('/customer/register', [\App\Http\Controllers\CustomerAuthController::class, 'register'])->name('customer.register.post');
Route::get('/customer/forgot-password', [\App\Http\Controllers\CustomerAuthController::class, 'forgotPasswordForm'])->name('customer.forgot-password');
Route::post('/customer/forgot-password', [\App\Http\Controllers\CustomerAuthController::class, 'sendResetLink'])->name('customer.forgot-password.post');
Route::get('/customer/reset-password/{token}', [\App\Http\Controllers\CustomerAuthController::class, 'resetPasswordForm'])->name('customer.reset-password');
Route::post('/customer/reset-password', [\App\Http\Controllers\CustomerAuthController::class, 'resetPassword'])->name('customer.reset-password.post');

Route::post('/customer/cart/add', [\App\Http\Controllers\CustomerAccountController::class, 'addToCart'])->name('customer.cart.add');
Route::post('/customer/saved-products/save', [\App\Http\Controllers\CustomerAccountController::class, 'saveProduct'])->name('customer.saved-products.save')->middleware('customer.auth');
Route::post('/customer/saved-products/toggle', [\App\Http\Controllers\CustomerAccountController::class, 'toggleSavedProduct'])->name('customer.saved-products.toggle')->middleware('customer.auth');
Route::post('/customer/saved-products/sync', [\App\Http\Controllers\CustomerAccountController::class, 'syncSavedProducts'])->name('customer.saved-products.sync')->middleware('customer.auth');
Route::post('/customer/logout', [\App\Http\Controllers\CustomerAuthController::class, 'logout'])->name('customer.logout');

Route::middleware('customer.auth')->group(function (): void {
    Route::get('/customer/cart', [\App\Http\Controllers\CustomerAccountController::class, 'cart'])->name('customer.cart');
    Route::post('/customer/cart/{cartItem}/update', [\App\Http\Controllers\CustomerAccountController::class, 'updateCart'])->name('customer.cart.update');
    Route::delete('/customer/cart/{cartItem}/remove', [\App\Http\Controllers\CustomerAccountController::class, 'removeCartItem'])->name('customer.cart.remove');
    Route::get('/customer/orders', [\App\Http\Controllers\CustomerAccountController::class, 'orders'])->name('customer.orders');
    Route::match(['get', 'post'], '/customer/checkout', [\App\Http\Controllers\CustomerAccountController::class, 'checkout'])->name('customer.checkout');
    Route::post('/customer/checkout/pay', [\App\Http\Controllers\CustomerAccountController::class, 'checkoutPay'])->name('customer.checkout.pay');
    Route::post('/customer/checkout/verify', [\App\Http\Controllers\CustomerAccountController::class, 'checkoutVerify'])->name('customer.checkout.verify');
    Route::post('/customer/buy-now', [\App\Http\Controllers\CustomerAccountController::class, 'buyNow'])->name('customer.buy-now');
    Route::get('/customer/saved-products', [\App\Http\Controllers\CustomerAccountController::class, 'savedProducts'])->name('customer.saved-products');
    Route::delete('/customer/saved-products/{savedProduct}/remove', [\App\Http\Controllers\CustomerAccountController::class, 'removeSavedProduct'])->name('customer.saved-products.remove');
    Route::get('/customer/profile', [\App\Http\Controllers\CustomerAccountController::class, 'profile'])->name('customer.profile');
    Route::put('/customer/profile', [\App\Http\Controllers\CustomerAccountController::class, 'updateProfile'])->name('customer.profile.update');
    Route::get('/customer/change-password', [\App\Http\Controllers\CustomerAuthController::class, 'changePasswordForm'])->name('customer.change-password');
    Route::post('/customer/change-password', [\App\Http\Controllers\CustomerAuthController::class, 'changePassword'])->name('customer.change-password.post');
});

Route::post('/ai/disease/report', [\App\Http\Controllers\AiController::class, 'storeDiseaseReport'])->name('ai.disease.report');

// Admin Panel Routes
require base_path('routes/admin.php');

// Staff Panel Routes
require base_path('routes/staff.php');
