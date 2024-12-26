<?php

use App\Http\Controllers\smsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/sendsms', [smsController::class, 'sendSms']);