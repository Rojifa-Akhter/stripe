<?php

use App\Http\Controllers\accountConnectController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\smsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/connect-stripe', [accountConnectController::class, 'stripeConnectAccount']);

Route::post('payment-intent', [accountConnectController::class, 'createPaymentIntent']);
Route::post('update-account', [accountConnectController::class, 'updateConnectedAccount']);
Route::post('delete-account', [accountConnectController::class, 'deleteConnectedAccount']);

//login
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('mobile-verification', [AuthController::class, 'otpSend']);
    
});

//sms controller
Route::get('/sendsms', [smsController::class, 'sendSms']);