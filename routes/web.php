<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Customer routes
Route::get('/customer/{hash}', [CustomerController::class, 'index'])->name('customer.notify');
Route::post('/customer/{hash}/update', [CustomerController::class, 'update'])->name('customer.notifyUpdate');
Route::post('/api/customer/otp-login', [CustomerController::class, 'customerOtpLogin'])->name('customer-otp-login');
Route::post('/customer/otp-verify', [CustomerController::class, 'customerOtpVerify'])->name('customer-otp-verify');

// Warranty routes
Route::get('/warranty/{serviceNo}', [WarrantyController::class, 'index'])->name('warranty.show');
Route::get('/warranty/{serviceNo}/pdf', [WarrantyController::class, 'downloadPdf'])->name('warranty.pdf');

// Excel Import routes (only super admin)
Route::middleware([EnsureSuperAdmin::class])->group(function () {
    Route::get('/excel-import', [ExcelImportController::class, 'index'])->name('excel-import.index');
    Route::post('/excel-import/preview', [ExcelImportController::class, 'preview'])->name('excel-import.preview');
    Route::post('/excel-import/confirm', [ExcelImportController::class, 'confirm'])->name('excel-import.confirm');
    Route::post('/excel-import/create-product', [ExcelImportController::class, 'createProduct'])->name('excel-import.create-product');
});
