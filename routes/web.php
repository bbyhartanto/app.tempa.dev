<?php

use App\Http\Controllers\Admin\TenantManagementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredTenantController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StorefrontController;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Authentication Routes (MVP)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredTenantController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredTenantController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
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
    Route::get('/', [DashboardController::class, 'index'])
        ->name('home');

    Route::get('/settings', [DashboardController::class, 'settings'])
        ->name('settings');

    Route::put('/settings', [DashboardController::class, 'updateSettings'])
        ->name('settings.update');

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

    Route::post('/orders/log', [DashboardController::class, 'logOrder'])
        ->name('orders.log');
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

    Route::get('/templates', [TenantManagementController::class, 'templates'])
        ->name('templates.index');

    Route::post('/templates', [TenantManagementController::class, 'storeTemplate'])
        ->name('templates.store');

    Route::put('/templates/{template}', [TenantManagementController::class, 'updateTemplate'])
        ->name('templates.update');
});

/*
|--------------------------------------------------------------------------
| Public Storefront Routes (wildcard at the end!)
|--------------------------------------------------------------------------
*/

Route::middleware([ResolveTenant::class])->group(function () {
    Route::get('/{store_link}', [StorefrontController::class, 'index'])
        ->name('storefront.home');

    Route::get('/{store_link}/products/{productSlug}', [StorefrontController::class, 'showProduct'])
        ->name('storefront.products.show');
});
