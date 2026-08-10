<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LabelPrintController;
use App\Http\Controllers\LabelSettingController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect(auth()->user()->homePath()) : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    Route::get('/pos/login', [AuthController::class, 'createPos'])->name('pos.login');
    Route::post('/pos/login', [AuthController::class, 'storePos'])->name('pos.login.store');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'portal:pos', 'permission:pos.access'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
});

Route::middleware(['auth', 'portal:backoffice'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->middleware('permission:dashboard.view')->name('dashboard');
    Route::get('/labels', [LabelPrintController::class, 'index'])->middleware('permission:labels.view')->name('labels.index');
    Route::get('/labels/create', [LabelPrintController::class, 'create'])->middleware('permission:labels.create')->name('labels.create');
    Route::post('/labels', [LabelPrintController::class, 'store'])->middleware('permission:labels.create')->name('labels.store');
    Route::post('/labels/bulk-pdf', [LabelPrintController::class, 'bulkPdf'])->middleware('permission:labels.print')->name('labels.bulk-pdf');
    Route::get('/labels/{labelPrint}', [LabelPrintController::class, 'show'])->middleware('permission:labels.view,labels.create')->name('labels.show');
    Route::get('/labels/{labelPrint}/pdf', [LabelPrintController::class, 'pdf'])->middleware('permission:labels.print')->name('labels.pdf');
    Route::get('/stock-masuk', [StockController::class, 'createIn'])->middleware('permission:inventory.stock_in')->name('stock.in.create');
    Route::post('/stock-masuk', [StockController::class, 'storeIn'])->middleware('permission:inventory.stock_in')->name('stock.in.store');
    Route::get('/stock-keluar', [StockController::class, 'createOut'])->middleware('permission:inventory.stock_out')->name('stock.out.create');
    Route::post('/stock-keluar', [StockController::class, 'storeOut'])->middleware('permission:inventory.stock_out')->name('stock.out.store');
    Route::get('/products', [ProductController::class, 'index'])->middleware('permission:products.view')->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->middleware('permission:products.manage')->name('products.store');
    Route::post('/products/import', [ProductController::class, 'import'])->middleware('permission:products.import')->name('products.import');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->middleware('permission:products.manage')->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('permission:products.manage')->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('permission:products.manage')->name('products.destroy');
    Route::get('/settings', [LabelSettingController::class, 'edit'])->middleware('permission:branding.manage')->name('settings.edit');
    Route::put('/settings', [LabelSettingController::class, 'update'])->middleware('permission:branding.manage')->name('settings.update');
    Route::delete('/settings/logo', [LabelSettingController::class, 'destroy'])->middleware('permission:branding.manage')->name('settings.logo.destroy');
    Route::delete('/settings/favicon', [LabelSettingController::class, 'destroyFavicon'])->middleware('permission:branding.manage')->name('settings.favicon.destroy');

    Route::middleware('super_admin')->group(function () {
        Route::resource('roles', RoleController::class)->except('show');
        Route::resource('users', UserManagementController::class)->except('show');
    });
});
