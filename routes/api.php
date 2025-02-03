<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PaymentPesananController;
use App\Http\Controllers\PaymentController;

// File: routes/api.php






Route::post('/register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('/admin/register', [AuthController::class, 'registerAdmin']);
Route::post('/admin/login', [AuthController::class, 'loginAdmin']);

Route::apiResource('categories', CategoryController::class);



// API routes untuk Produk


Route::apiResource('products', ProductController::class);
// Route::get('products/supplier', [ProductController::class, 'getBySupplier']);
Route::get('products/supplier/{supplier_id}', [ProductController::class, 'getBySupplier']);






// Route untuk Pesanan
Route::resource('pesanan', PesananController::class);

// Route untuk PaymentPesanan
Route::resource('payment_pesanan', PaymentPesananController::class);
// Webhook untuk Midtrans

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// routes/api.php
Route::post('payment-notification', [AuthController::class, 'paymentNotification']);

Route::post('/midtrans/webhook/registration', [AuthController::class, 'paymentNotification']); // Pembayaran saat pendaftaran
Route::post('/midtrans/webhook/pesanan', [PaymentPesananController::class, 'midtransWebhook']); // Pembayaran untuk pesanan


Route::resource('payments', PaymentController::class);
// Di routes/api.php
Route::post('/payment-notification', [PaymentController::class, 'paymentNotification']);







