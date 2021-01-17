<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PaystackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::post('/auth/signup', [AuthController::class, 'signup'])->name('signup');
Route::post('/auth/login', [AuthController::class, 'login'])->name('login');
Route::get('/paystack/callback', [PaystackController::class, 'verify'])->name('paystack.verify');

Route::middleware(['auth:sanctum'])->group(function () {  
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // Loan API
    Route::prefix('loan')->group(function() {
        Route::post('create', [LoanController::class, 'createLoan'])->name('loan.create');
    });

    // Paystack
    Route::prefix('paystack')->group(function() {
        Route::post('initialize', [PaystackController::class, 'initialize'])->name('paystack.initialize');
        Route::get('charge', [PaystackController::class, 'charge'])->name('paystack.charge');
    });
    
});
