<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\WelcomeController;
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
