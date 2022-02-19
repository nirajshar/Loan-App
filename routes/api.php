<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LoanApplicationController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\LoanAmortizationController;


Route::group([ 'middleware' => 'api', 'prefix' => 'v1'], function () {
    
    # AUTH ROUTES
    Route::group(['prefix' => 'auth'], function() {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/user-profile', [AuthController::class, 'userProfile']);    
    });

    # USER ROUTES
    Route::group(['middleware' => 'auth.role:user'], function() {
        Route::post('ewi/pay', [LoanController::class, 'submitPayment'])->middleware();
    });

    # SHARED ROUTES (ADMIN | USER)
    Route::group(['middleware' => 'auth.role:user,admin'], function() {
        Route::apiResource('loan_applications', LoanApplicationController::class, ['except' => ['destroy']]);
    });    
    
    # ADMIN ROUTES
    Route::group(['middleware' => 'auth.role:admin'], function() {
        Route::post('loan_applications/{id}/approve-reject', [LoanApplicationController::class, 'approveRejectLoanApplication']);
        Route::apiResource('loans', LoanController::class, ['except' => ['store', 'destroy']]);
    });
    
});