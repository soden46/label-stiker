<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LabelPrintController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/labels', [LabelPrintController::class, 'index'])->name('labels.index');
    Route::get('/labels/create', [LabelPrintController::class, 'create'])->name('labels.create');
    Route::post('/labels', [LabelPrintController::class, 'store'])->name('labels.store');
    Route::post('/labels/bulk-pdf', [LabelPrintController::class, 'bulkPdf'])->name('labels.bulk-pdf');
    Route::get('/labels/{labelPrint}', [LabelPrintController::class, 'show'])->name('labels.show');
    Route::get('/labels/{labelPrint}/pdf', [LabelPrintController::class, 'pdf'])->name('labels.pdf');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});
