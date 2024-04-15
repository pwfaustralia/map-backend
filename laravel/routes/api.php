<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', 'App\Http\Controllers\UserController@login');

Route::prefix('/users')->middleware('auth:api')->group(function () {
    Route::post('/', 'App\Http\Controllers\UserController@register')->middleware('scope:create-users');

    Route::put('/{id}', 'App\Http\Controllers\UserController@updateClient')->middleware('scope:update-users');

    Route::get('/', 'App\Http\Controllers\UserController@listUsers')->middleware('scope:view-all-users');

    Route::get('/{id}', 'App\Http\Controllers\UserController@getUser')->middleware('scope:view-users');

    Route::delete('/{id}', 'App\Http\Controllers\UserController@deleteUser')->middleware('scope:delete-users');
});


Route::prefix('clients')->middleware('auth:api')->group(function () {
    Route::post('/', 'App\Http\Controllers\ClientController@createClient')->middleware('scope:create-clients');

    Route::put('/{id}', 'App\Http\Controllers\ClientController@updateClient')->middleware('scope:update-clients');

    Route::get('/', 'App\Http\Controllers\ClientController@listClients')->middleware('scope:view-all-clients');

    Route::get('/{id}', 'App\Http\Controllers\ClientController@getClient')->middleware('scope:view-clients');

    Route::delete('/{id}', 'App\Http\Controllers\ClientController@deleteClient')->middleware('scope:delete-clients');
});
