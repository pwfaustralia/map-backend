<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\LoanBalanceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyEmailController;
use App\Models\LoanBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication
Route::post('/users/login', 'App\Http\Controllers\UserController@login');
Route::middleware(['cookie-auth', 'auth:api'])->post('/users/logout', 'App\Http\Controllers\UserController@logout');

// Users
Route::prefix('/users')->middleware(['cookie-auth', 'auth:api'])->group(function () {
    Route::get('/me', [UserController::class, 'me']);
    Route::get('/checkup', [UserController::class, 'access_checkup']);
    Route::post('/', [UserController::class, 'register'])->middleware('scope:create-users');
    Route::put('/{id}', [UserController::class, 'updateUser'])->middleware('scope:update-users');
    Route::get('/', [UserController::class, 'listUsers'])->middleware('scope:view-all-users');
    Route::get('/{id}', [UserController::class, 'getUser'])->middleware('scope:view-users');
    Route::delete('/{id}', [UserController::class, 'deleteUser'])->middleware('scope:delete-users');
});

// Clients
Route::prefix('clients')->middleware(['cookie-auth', 'auth:api'])->group(function () {
    Route::post('/', [ClientController::class, 'createClient'])->middleware('scope:create-clients');
    Route::put('/{id}', [ClientController::class, 'updateClient'])->middleware('scope:update-clients');
    Route::get('/', [ClientController::class, 'listClients'])->middleware('scope:view-all-clients');
    Route::get('/{id}', [ClientController::class, 'getClient'])->middleware('scope:view-clients');
    Route::get('/{id}/loanaccounts', [ClientController::class, 'getLoanAccounts'])->middleware(['scope:view-clients', 'scope:view-accounts']);
    Route::post('/{id}/setloanprimaryaccount', [ClientController::class, 'setPrimaryLoanAccount'])->middleware(['scope:update-clients', 'scope:update-accounts']);
    Route::delete('/{id}', [ClientController::class, 'deleteClient'])->middleware('scope:delete-clients');
    Route::get('/{id}/yodlee', [ClientController::class, 'getUserYodleeAccessTokenWithHeader'])->middleware('scope:view-clients');
    Route::get('/{id}/yodlee/status', [ClientController::class, 'getYodleeStatus'])->middleware('scope:view-clients');
});

// Transactions
Route::prefix('transactions')->middleware(['cookie-auth', 'auth:api'])->group(function () {
    Route::post('/import', [TransactionController::class, 'importAccountTransactions'])->middleware('scope:import-transactions');
});

// Accounts
Route::prefix('accounts')->middleware(['cookie-auth', 'auth:api'])->group(function () {
    Route::put('/assign', [AccountController::class, 'assignToClient'])->middleware('scope:update-accounts');
});

// Loan Balances
Route::prefix('loanbalances')->middleware(['cookie-auth', 'auth:api'])->group(function () {
    Route::get('/list', [LoanBalanceController::class, 'listLoanBalances'])->middleware('scope:view-accounts');
});

// // Verify email
// Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
//     ->middleware(['signed', 'throttle:6,1'])
//     ->name('verification.verify');

// // Resend link to verify email
// Route::post('/email/verify/resend', function (Request $request) {
//     $request->user()->sendEmailVerificationNotification();
//     return response(['message' => 'Verification link sent!'], 200);
// })->middleware(['cookie-auth', 'auth:api', 'throttle:6,1'])->name('verification.send');
