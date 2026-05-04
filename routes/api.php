<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OperationsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::any('/login', [AuthController::class, 'login'])->name('login');
Route::post('/2fa/verify', [AuthController::class, 'verify2FA']);

// RMB Settlement Webhook
Route::post('/webhooks/rmb-settlement', [OperationsController::class, 'handleRmbConfirmation']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication Terminate
    Route::post('/logout', [AuthController::class, 'logout']);

    // Operations Management
    Route::get('/dashboard', [OperationsController::class, 'dashboard']);
    Route::get('/rate', [OperationsController::class, 'getLiveRates']);
    Route::post('/swap', [OperationsController::class, 'initiateSwap']);
    Route::post('/swap/confirm', [OperationsController::class, 'confirmSwap']);
    Route::post('/fund/wallet', [OperationsController::class, 'fundWallet']);
    Route::get('/ledger/{id}', [OperationsController::class, 'getLedgerHistory']);
});
