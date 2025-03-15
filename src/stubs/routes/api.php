<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Todo: API VERSION 1
Route::group(['prefix' => 'v1'], function () {
    // Todo: v1 Auth
    Route::group(['prefix' => '/auth'], function () {
        Route::group(['prefix' => '/register'], function () {
            Route::controller(App\Http\Controllers\Api\V1\Auth\Registration::class)->group(function () {
                Route::post('/user', 'user_registration');
            });
        });
    });
});
