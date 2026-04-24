<?php

use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\SubscriptionManagementController;
use App\Http\Controllers\Admin\TenantManagementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredTenantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\Dashboard\OrderController as DashboardOrderController;
use App\Http\Controllers\Dashboard\OrderItemController as DashboardOrderItemController;
use App\Http\Controllers\Dashboard\ServiceController as DashboardServiceController;
use App\Http\Controllers\Dashboard\OnboardingController as DashboardOnboardingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StorefrontController;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredTenantController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredTenantController::class, 'store']);
    Route::get('register/pending', [RegisteredTenantController::class, 'pending'])
        ->name('register.pending');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

/*
|--------------------------------------------------------------------------
| Landing Page
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home')->middleware('optional-auth');

/*
|--------------------------------------------------------------------------
| Tenant Dashboard Routes (BEFORE wildcard!)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'tenant'])->prefix('dashboard')->name('dashboard.')->group(function () {
    // Onboarding (must be BEFORE onboarding middleware, no redirect loop)
    Route::get('/onboarding', [DashboardOnboardingController::class, 'create'])
        ->name('onboarding');

    Route::post('/onboarding', [DashboardOnboardingController::class, 'store'])
        ->name('onboarding.store');
});

// Dashboard routes requiring completed onboarding
Route::middleware(['auth', 'tenant', 'onboarding'])->prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->name('home');

    Route::get('/settings', [DashboardController::class, 'settings'])
        ->name('settings');

    Route::put('/settings', [DashboardController::class, 'updateSettings'])
        ->name('settings.update');

    // Location page
    Route::get('/location', [DashboardController::class, 'locationPage'])
        ->name('location');

    // Template page
    Route::get('/template', [DashboardController::class, 'templatePage'])
        ->name('template');

    // Module management
    Route::post('/settings/modules', [DashboardController::class, 'updateModules'])
        ->name('settings.modules.update');

    // Tenant subscription request
    Route::post('/subscription/request', [DashboardController::class, 'requestSubscription'])
        ->name('subscription.request');

    // Links management
    Route::get('/links', [DashboardController::class, 'links'])
        ->name('links.index');

    Route::put('/links', [DashboardController::class, 'updateLinks'])
        ->name('links.update');

    // Products (catalog module)
    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');

    Route::get('/products/create', [ProductController::class, 'create'])
        ->name('products.create');

    Route::post('/products', [ProductController::class, 'store'])
        ->name('products.store');

    Route::get('/products/{product}', [ProductController::class, 'show'])
        ->name('products.show');

    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
        ->name('products.edit');

    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->name('products.update');

    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->name('products.destroy');

    // Services (booking module)
    Route::middleware(['require.module:booking'])->group(function () {
        Route::get('/services', [DashboardServiceController::class, 'index'])
            ->name('services.index');

        Route::get('/services/create', [DashboardServiceController::class, 'create'])
            ->name('services.create');

        Route::post('/services', [DashboardServiceController::class, 'store'])
            ->name('services.store');

        Route::get('/services/{service}', [DashboardServiceController::class, 'show'])
            ->name('services.show');

        Route::get('/services/{service}/edit', [DashboardServiceController::class, 'edit'])
            ->name('services.edit');

        Route::put('/services/{service}', [DashboardServiceController::class, 'update'])
            ->name('services.update');

        Route::delete('/services/{service}', [DashboardServiceController::class, 'destroy'])
            ->name('services.destroy');

        Route::put('/services/{service}/toggle', [DashboardServiceController::class, 'toggleAvailability'])
            ->name('services.toggle-availability');
    });

    Route::post('/orders/log', [DashboardController::class, 'logOrder'])
        ->name('orders.log');

    // Order management
    Route::get('/orders', [DashboardOrderController::class, 'index'])
        ->name('orders.index');

    Route::get('/orders/{id}', [DashboardOrderController::class, 'show'])
        ->name('orders.show');

    Route::patch('/orders/{id}', [DashboardOrderController::class, 'update'])
        ->name('orders.update');

    Route::put('/orders/{id}/accept', [DashboardOrderController::class, 'accept'])
        ->name('orders.accept');

    Route::put('/orders/{id}/mark-paid', [DashboardOrderController::class, 'markPaid'])
        ->name('orders.mark-paid');

    Route::patch('/orders/{orderId}/items/{itemId}/quantity', [DashboardOrderItemController::class, 'updateQuantity'])
        ->name('orders.items.quantity');

    Route::delete('/orders/{orderId}/items/{itemId}', [DashboardOrderItemController::class, 'destroy'])
        ->name('orders.items.destroy');

    Route::post('/orders/{orderId}/items/{itemId}/restore', [DashboardOrderItemController::class, 'restore'])
        ->name('orders.items.restore');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/tenants', [TenantManagementController::class, 'index'])
        ->name('tenants.index');

    Route::get('/tenants/{tenant}', [TenantManagementController::class, 'show'])
        ->name('tenants.show');

    Route::post('/tenants/{tenant}/approve', [TenantManagementController::class, 'approve'])
        ->name('tenants.approve');

    Route::post('/tenants/{tenant}/suspend', [TenantManagementController::class, 'suspend'])
        ->name('tenants.suspend');

    Route::post('/tenants/{tenant}/reactivate', [TenantManagementController::class, 'reactivate'])
        ->name('tenants.reactivate');

    Route::delete('/tenants/{tenant}', [TenantManagementController::class, 'destroy'])
        ->name('tenants.destroy');

    // Subscription management
    Route::get('/tenants/{tenant}/subscription', [SubscriptionManagementController::class, 'show'])
        ->name('tenants.subscription.show');

    Route::post('/tenants/{tenant}/subscription/activate', [SubscriptionManagementController::class, 'activate'])
        ->name('tenants.subscription.activate');

    Route::post('/tenants/{tenant}/subscription/cancel', [SubscriptionManagementController::class, 'cancel'])
        ->name('tenants.subscription.cancel');

    Route::post('/tenants/{tenant}/subscription/renew', [SubscriptionManagementController::class, 'renew'])
        ->name('tenants.subscription.renew');

    Route::post('/tenants/{tenant}/subscription/extend-trial', [SubscriptionManagementController::class, 'extendTrial'])
        ->name('tenants.subscription.extend-trial');

    // Subscription request workflow (tenant requests, admin approves)
    Route::post('/tenants/{tenant}/subscription/request', [SubscriptionManagementController::class, 'requestSubscription'])
        ->name('tenants.subscription.request');

    Route::post('/tenants/{tenant}/subscription/approve', [SubscriptionManagementController::class, 'approveRequest'])
        ->name('tenants.subscription.approve');

    Route::post('/tenants/{tenant}/subscription/reject', [SubscriptionManagementController::class, 'rejectRequest'])
        ->name('tenants.subscription.reject');

    // Pricing configuration
    Route::get('/pricing', [PricingController::class, 'index'])
        ->name('pricing.index');

    Route::put('/pricing/{plan}', [PricingController::class, 'update'])
        ->name('pricing.update');

    Route::post('/pricing/bulk-update', [PricingController::class, 'bulkUpdate'])
        ->name('pricing.bulk-update');

    Route::get('/templates', [TenantManagementController::class, 'templates'])
        ->name('templates.index');

    Route::post('/templates', [TenantManagementController::class, 'storeTemplate'])
        ->name('templates.store');

    Route::put('/templates/{template}', [TenantManagementController::class, 'updateTemplate'])
        ->name('templates.update');
});

/*
|--------------------------------------------------------------------------
| Public Storefront Routes (2 pages per store)
|--------------------------------------------------------------------------
| Sitemap:
|   /{store_link}           → Home (Link Aggregator)
|   /{store_link}/catalog   → Catalog (Product Grid with Cart)
*/

Route::middleware([ResolveTenant::class])->prefix('{store_link}')->name('storefront.')->group(function () {
    // Home Page - Link Aggregator (NEW)
    Route::get('/', [StorefrontController::class, 'home'])
        ->name('home');

    // Catalog Page - Product Grid (CURRENT)
    Route::get('/catalog', [StorefrontController::class, 'catalog'])
        ->name('catalog');

    // Load More Products (for infinite scroll)
    Route::get('/catalog/products', [StorefrontController::class, 'loadMoreProducts'])
        ->name('catalog.products.load-more');

    // Dine-In Menu - Display Only (No Cart)
    Route::get('/dine-in', [StorefrontController::class, 'dineInMenu'])
        ->name('dine-in');

    // Load More Dine-In Products (for infinite scroll)
    Route::get('/dine-in/products', [StorefrontController::class, 'loadMoreDineInProducts'])
        ->name('dine-in.products.load-more');

    // Product Detail
    Route::get('/products/{productSlug}', [StorefrontController::class, 'showProduct'])
        ->name('products.show');

    // Service Detail (booking)
    Route::get('/services/{serviceSlug}', [StorefrontController::class, 'showService'])
        ->name('services.show');

    // Order Creation (API)
    Route::post('/orders', [OrderController::class, 'store'])
        ->name('orders.store');
});

/*
|--------------------------------------------------------------------------
| Public Receipt Route
|--------------------------------------------------------------------------
*/

Route::get('/receipt/{order_number}', [ReceiptController::class, 'show'])
    ->name('receipt.show');
