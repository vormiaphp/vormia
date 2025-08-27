<?php

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

// Todo: USER AUTH ROUTES
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Todo: API VRM DEFAULT
Route::group(['prefix' => 'vrm'], function () {
    // Role routes
    Route::prefix('roles')->group(function () {
        Route::controller(App\Http\Controllers\Vrm\RoleController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{idOrSlug}', 'show');
            Route::put('/{idOrSlug}', 'update');
            Route::delete('/{idOrSlug}', 'destroy');
        });
    });

    // Permission routes
    Route::prefix('permissions')->group(function () {
        Route::controller(App\Http\Controllers\Vrm\PermissionController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
            Route::post('/assign-to-role/{roleIdOrSlug}', 'assignToRole');
        });
    });

    // User-Role routes
    Route::prefix('users')->group(function () {
        Route::controller(App\Http\Controllers\Vrm\UserRoleController::class)->group(function () {
            Route::post('/{userId}/roles', 'assignRoles');
            Route::get('/{userId}/roles', 'getUserRoles');
            Route::delete('/{userId}/roles', 'removeRoles');
            Route::get('/by-role/{roleIdOrSlug}', 'getUsersByRole');
        });
    });
});

// Todo: API VERSION 1
Route::group(['prefix' => 'v1'], function () {
    // Todo register
    Route::controller(App\Http\Controllers\Api\V1\AuthRegisterController::class)->group(function () {
        Route::post('/register', 'register');
        Route::get('/verify-email', 'verifyEmail');
        Route::post('/resend-verification', 'resendVerification');
    });

    // Todo password reset
    Route::controller(App\Http\Controllers\Api\V1\AuthResetPasswordController::class)->group(function () {
        Route::post('/password-reset', 'passwordReset');
        Route::post('/password-reset/verify', 'passwordResetVerify');
    });

    // Todo login
    Route::post('/login', [\App\Http\Controllers\Api\V1\AuthLoginController::class, 'login']);

    // Protected routes (require authentication)
    Route::middleware('api-auth')->group(function () {
        // User routes
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::middleware('auth:sanctum')->post('/logout', [\App\Http\Controllers\Api\V1\AuthLoginController::class, 'logout']);
    });
});
