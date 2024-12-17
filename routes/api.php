<?php

use App\Http\Controllers\accountConnectController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PlanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/connect-stripe', [accountConnectController::class, 'stripeConnectAccount']);

Route::post('payment-intent', [accountConnectController::class, 'createPaymentIntent']);
Route::post('update-account', [accountConnectController::class, 'updateConnectedAccount']);
Route::post('delete-account', [accountConnectController::class, 'deleteConnectedAccount']);

// subscription method
Route::get('/plans', [PlanController::class, 'getPlans']);
Route::post('/plan', [PlanController::class, 'createPlan']);
Route::post('/checkout/{plan_id}', [PaymentController::class, 'checkout']);
Route::get('/checkout/success', [PaymentController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [PaymentController::class, 'cancel']);
