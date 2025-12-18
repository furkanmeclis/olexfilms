<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WarrantyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Customer routes
Route::get('/customer/{hash}', [CustomerController::class, 'index'])->name('customer.notify');
Route::post('/customer/{hash}/update', [CustomerController::class, 'update'])->name('customer.notifyUpdate');
Route::post('/api/customer/otp-login', [CustomerController::class, 'customerOtpLogin'])->name('customer-otp-login');
Route::post('/customer/otp-verify', [CustomerController::class, 'customerOtpVerify'])->name('customer-otp-verify');

// Warranty routes
Route::get('/warranty/{service_no}', [WarrantyController::class, 'index'])->name('warranty.show');
