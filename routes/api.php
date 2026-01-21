<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\UserContextController;

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

Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);

    // User CRUD (Admin)
    Route::apiResource('users', App\Http\Controllers\Api\UserController::class);
    Route::get('/profile', [App\Http\Controllers\Api\AuthController::class, 'profile']);

    // Helper endpoint for frontend state
    Route::get('/user-context', UserContextController::class);

    // Roles Management
    Route::apiResource('roles', RoleController::class);
    // Specific endpoint to assign permissions to a role
    Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermissions']);

    // Permissions Management
    Route::apiResource('permissions', PermissionController::class);

    // Assign Roles to Users
    Route::put('users/{id}/roles', [UserRoleController::class, 'update']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
