<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Vormia\Vormia\Http\Controllers\Api\AuthLoginController;
use Vormia\Vormia\Http\Controllers\Api\MediaPreviewController;
use Vormia\Vormia\Http\Controllers\Api\PermissionController;
use Vormia\Vormia\Http\Controllers\Api\RoleController;
use Vormia\Vormia\Http\Controllers\Api\UserRoleController;

Route::prefix('vrm')->group(function () {
    Route::get('/media/preview', [MediaPreviewController::class, 'show']);

    Route::prefix('roles')->controller(RoleController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{idOrSlug}', 'show');
        Route::put('/{idOrSlug}', 'update');
        Route::delete('/{idOrSlug}', 'destroy');
    });

    Route::prefix('permissions')->controller(PermissionController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::post('/assign-to-role/{roleIdOrSlug}', 'assignToRole');
    });

    Route::prefix('users')->controller(UserRoleController::class)->group(function () {
        Route::post('/{userId}/roles', 'assignRoles');
        Route::get('/{userId}/roles', 'getUserRoles');
        Route::delete('/{userId}/roles', 'removeRoles');
        Route::get('/by-role/{roleIdOrSlug}', 'getUsersByRole');
    });
});

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthLoginController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', fn (Request $request) => $request->user());
        Route::post('/logout', [AuthLoginController::class, 'logout']);
    });
});
