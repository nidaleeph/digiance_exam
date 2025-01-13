<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaymentController;

Route::group(['middleware' => ['web']], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/plans', [SubscriptionController::class, 'getAllPlans']);

    Route::group(['middleware' => 'auth'], function () {
        Route::get('/subscription', [SubscriptionController::class, 'getUserSubscription']);
        // Route::post('/subscription/cancel', [SubscriptionController::class, 'cancelSubscription']);
        Route::post('/subscription/update', [SubscriptionController::class, 'updateSubscription']);
        Route::post('/subscribe', action: [SubscriptionController::class, 'subscribe']);


        Route::get('/payments/history', [PaymentController::class, 'paymentHistory']);
        Route::post('/create-payment-intent', [PaymentController::class, 'createPaymentIntent']);

        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/get-user', [AuthController::class, 'getLoggedInUser']);
    });
});

