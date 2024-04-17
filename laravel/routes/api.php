<?php

use App\Http\Controllers\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication
Route::post('/users/login', 'App\Http\Controllers\UserController@login');

// Users
Route::prefix('/users')->middleware('auth:api')->group(function () {
    Route::post('/', 'App\Http\Controllers\UserController@register')->middleware('scope:create-users');
    Route::put('/{id}', 'App\Http\Controllers\UserController@updateClient')->middleware('scope:update-users');
    Route::get('/', 'App\Http\Controllers\UserController@listUsers')->middleware('scope:view-all-users');
    Route::get('/{id}', 'App\Http\Controllers\UserController@getUser')->middleware('scope:view-users');
    Route::delete('/{id}', 'App\Http\Controllers\UserController@deleteUser')->middleware('scope:delete-users');
});

// Clients
Route::prefix('clients')->middleware('auth:api')->group(function () {
    Route::post('/', 'App\Http\Controllers\ClientController@createClient')->middleware('scope:create-clients');
    Route::put('/{id}', 'App\Http\Controllers\ClientController@updateClient')->middleware('scope:update-clients');
    Route::get('/', 'App\Http\Controllers\ClientController@listClients')->middleware('scope:view-all-clients');
    Route::get('/{id}', 'App\Http\Controllers\ClientController@getClient')->middleware('scope:view-clients');
    Route::delete('/{id}', 'App\Http\Controllers\ClientController@deleteClient')->middleware('scope:delete-clients');
});

// Verify email
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Resend link to verify email
Route::post('/email/verify/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return response(['message' => 'Verification link sent!'], 200);
})->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');
