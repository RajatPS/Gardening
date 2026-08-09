<?php

use App\Http\Controllers\Admin\{
    DashboardController,
    UserController,
    StaffController,
    ProductController,                                                                                                                                                          
    OrderController,
    AppointmentController,
    SubscriptionController,
    TransactionController,
    InventoryController,
    NotificationController,
    ReportController,
    AuditLogController,
    AuthController,
    SettingsController
};
use Illuminate\Support\Facades\Route;

// Admin Authentication Routes
Route::prefix('admin')->group(function () {
    Route::get('login', [AuthController::class, 'loginForm'])->name('admin.login');
    Route::post('login', [AuthController::class, 'login'])->name('admin.login.post');
    Route::get('forgot-password', [AuthController::class, 'forgotPasswordForm'])->name('admin.forgot-password');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('admin.forgot-password.post');
    Route::get('reset-password/{token}', [AuthController::class, 'resetPasswordForm'])->name('admin.reset-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('admin.reset-password.post');

    // Protected Admin Routes
    Route::middleware(['auth', 'admin'])->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('change-password', [AuthController::class, 'changePasswordForm'])->name('admin.change-password');
        Route::post('change-password', [AuthController::class, 'changePassword'])->name('admin.change-password.post');
        Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('admin.users.index');
            Route::get('/{id}', [UserController::class, 'show'])->name('admin.users.show');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('admin.users.update');
            Route::post('/{id}/activate', [UserController::class, 'activate'])->name('admin.users.activate');
            Route::post('/{id}/deactivate', [UserController::class, 'deactivate'])->name('admin.users.deactivate');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        });
        // Staff Management
        Route::prefix('staff')->group(function () {
            Route::get('/', [StaffController::class, 'index'])->name('admin.staff.index');
            Route::get('create', [StaffController::class, 'create'])->name('admin.staff.create');
            Route::post('/', [StaffController::class, 'store'])->name('admin.staff.store');
            Route::get('/{id}', [StaffController::class, 'show'])->name('admin.staff.show');
            Route::get('/{id}/edit', [StaffController::class, 'edit'])->name('admin.staff.edit');
            Route::put('/{id}', [StaffController::class, 'update'])->name('admin.staff.update');
            Route::post('/{id}/suspend', [StaffController::class, 'suspend'])->name('admin.staff.suspend');
            Route::post('/{id}/activate', [StaffController::class, 'activate'])->name('admin.staff.activate');
            Route::post('/{id}/reset-password', [StaffController::class, 'resetPassword'])->name('admin.staff.reset-password');
            Route::delete('/{id}', [StaffController::class, 'destroy'])->name('admin.staff.destroy');
        });

        // Product Management
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('admin.products.index');
            Route::get('create', [ProductController::class, 'create'])->name('admin.products.create');
            Route::post('/', [ProductController::class, 'store'])->name('admin.products.store');
            Route::get('/{id}', [ProductController::class, 'show'])->name('admin.products.show');
            Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('admin.products.edit');
            Route::put('/{id}', [ProductController::class, 'update'])->name('admin.products.update');
            Route::post('/{id}/activate', [ProductController::class, 'activate'])->name('admin.products.activate');
            Route::post('/{id}/deactivate', [ProductController::class, 'deactivate'])->name('admin.products.deactivate');
            Route::delete('/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
        });

        // Order Management
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('admin.orders.index');
            Route::get('/{id}', [OrderController::class, 'show'])->name('admin.orders.show');
            Route::post('/{id}/update-status', [OrderController::class, 'updateStatus'])->name('admin.orders.update-status');
            Route::post('/{id}/record-cod-payment', [OrderController::class, 'recordCodPayment'])->name('admin.orders.record-cod-payment');
            Route::get('/{id}/invoice', [OrderController::class, 'generateInvoice'])->name('admin.orders.invoice');
            Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');
            Route::delete('/{id}', [OrderController::class, 'destroy'])->name('admin.orders.destroy');
        });

        // Appointment Management
        Route::prefix('appointments')->group(function () {
            Route::get('/', [AppointmentController::class, 'index'])->name('admin.appointments.index');
            Route::get('/{id}', [AppointmentController::class, 'show'])->name('admin.appointments.show');
            Route::post('/{id}/assign-staff', [AppointmentController::class, 'assignStaff'])->name('admin.appointments.assign-staff');
            Route::post('/{id}/reschedule', [AppointmentController::class, 'reschedule'])->name('admin.appointments.reschedule');
            Route::post('/{id}/update-status', [AppointmentController::class, 'updateStatus'])->name('admin.appointments.update-status');
            Route::get('/search-staff', [AppointmentController::class, 'searchStaff'])->name('admin.appointments.search-staff');
            Route::post('/{id}/complete', [AppointmentController::class, 'complete'])->name('admin.appointments.complete');
            Route::post('/{id}/cancel', [AppointmentController::class, 'cancel'])->name('admin.appointments.cancel');
            Route::delete('/{id}', [AppointmentController::class, 'destroy'])->name('admin.appointments.destroy');
        });

        // Subscription Management
        Route::prefix('subscriptions')->group(function () {
            Route::get('/', [SubscriptionController::class, 'index'])->name('admin.subscriptions.index');
            Route::get('/{id}', [SubscriptionController::class, 'show'])->name('admin.subscriptions.show');
            Route::post('/{id}/renew', [SubscriptionController::class, 'renew'])->name('admin.subscriptions.renew');
            Route::post('/{id}/upgrade', [SubscriptionController::class, 'upgrade'])->name('admin.subscriptions.upgrade');
            Route::post('/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('admin.subscriptions.cancel');
            Route::delete('/{id}', [SubscriptionController::class, 'destroy'])->name('admin.subscriptions.destroy');
        });

        // Transaction Management
        Route::prefix('transactions')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('admin.transactions.index');
            Route::get('/{id}', [TransactionController::class, 'show'])->name('admin.transactions.show');
            Route::delete('/{id}', [TransactionController::class, 'destroy'])->name('admin.transactions.destroy');
        });

        // Inventory Management
        Route::prefix('inventory')->group(function () {
            Route::get('/', [InventoryController::class, 'index'])->name('admin.inventory.index');
            Route::post('/{id}/add-stock', [InventoryController::class, 'addStock'])->name('admin.inventory.add-stock');
            Route::post('/{id}/reduce-stock', [InventoryController::class, 'reduceStock'])->name('admin.inventory.reduce-stock');
            Route::post('/{id}/adjust', [InventoryController::class, 'adjustInventory'])->name('admin.inventory.adjust');
            Route::delete('/{id}', [InventoryController::class, 'destroy'])->name('admin.inventory.destroy');
        });

        // Notification Management
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('admin.notifications.index');
            Route::get('create', [NotificationController::class, 'create'])->name('admin.notifications.create');
            Route::post('/', [NotificationController::class, 'store'])->name('admin.notifications.store');
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('revenue', [ReportController::class, 'revenue'])->name('admin.reports.revenue');
            Route::get('sales', [ReportController::class, 'sales'])->name('admin.reports.sales');
            Route::get('users', [ReportController::class, 'users'])->name('admin.reports.users');
            Route::get('subscriptions', [ReportController::class, 'subscriptions'])->name('admin.reports.subscriptions');
            Route::get('products', [ReportController::class, 'products'])->name('admin.reports.products');
            Route::get('staff-performance', [ReportController::class, 'staffPerformance'])->name('admin.reports.staff-performance');
        });

        // Audit Logs
        Route::prefix('audit-logs')->group(function () {
            Route::get('/', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');
            Route::get('/{id}', [AuditLogController::class, 'show'])->name('admin.audit-logs.show');
        });

        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('general', [SettingsController::class, 'generalSettings'])->name('admin.settings.general');
            Route::post('general', [SettingsController::class, 'updateGeneralSettings'])->name('admin.settings.general.update');
            Route::get('payment', [SettingsController::class, 'paymentSettings'])->name('admin.settings.payment');
            Route::post('payment', [SettingsController::class, 'updatePaymentSettings'])->name('admin.settings.payment.update');
            Route::get('notification', [SettingsController::class, 'notificationSettings'])->name('admin.settings.notification');
            Route::post('notification', [SettingsController::class, 'updateNotificationSettings'])->name('admin.settings.notification.update');
            Route::get('profile', [SettingsController::class, 'profile'])->name('admin.settings.profile');
            Route::post('profile', [SettingsController::class, 'updateProfile'])->name('admin.settings.profile.update');
            Route::post('password', [SettingsController::class, 'changePassword'])->name('admin.settings.password.update');
            // Garden setups
            Route::get('garden-setups', [\App\Http\Controllers\Admin\GardenSetupController::class, 'index'])->name('admin.garden-setups.index');
            Route::delete('garden-setups/{id}', [\App\Http\Controllers\Admin\GardenSetupController::class, 'destroy'])->name('admin.garden-setups.destroy');
        });
    });
});
