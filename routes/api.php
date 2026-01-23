<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Api\UserContextController;
use App\Http\Controllers\Api\DiseaseCategoryController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\SalaryController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\CheckoutController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider within the "api" middleware group.
|
*/

// Public route
Route::get('/time-check', function () {
    return response()->json([
        'success' => true,
        'current_time_cambodia' => \Carbon\Carbon::now('Asia/Phnom_Penh')->toDateTimeString(),
        'timezone' => config('app.timezone'),
        'server_time' => now()->toDateTimeString(),
    ]);
});

Route::post('/login', [AuthController::class, 'login']);

// Routes protected by sanctum auth
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // Users
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}', [UserController::class, 'update']);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword']);
    Route::put('users/{id}/roles', [UserRoleController::class, 'update']);

    // Patients
    Route::apiResource('patients', PatientController::class);

    // Disease categories
    Route::apiResource('disease-categories', DiseaseCategoryController::class);

    // Checkouts
    Route::apiResource('checkouts', CheckoutController::class);

    // Employees
    Route::apiResource('employees', EmployeeController::class);
    Route::post('employees/{employee}/activate', [EmployeeController::class, 'activate']);
    Route::post('employees/{employee}/deactivate', [EmployeeController::class, 'deactivate']);

    // Attendance
    Route::get('attendances', [AttendanceController::class, 'index']);
    Route::get('attendances/stats', [AttendanceController::class, 'overallStats']);
    Route::post('attendances/scan', [AttendanceController::class, 'scanQr']);
    Route::post('attendances/scan-office', [AttendanceController::class, 'scanCompanyQr']);
    Route::post('employees/{employee}/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('employees/{employee}/check-out', [AttendanceController::class, 'checkOut']);
    Route::put('attendances/{attendance}', [AttendanceController::class, 'update']);
    Route::delete('attendances/{attendance}', [AttendanceController::class, 'destroy']);
    Route::get('my-attendance', [AttendanceController::class, 'myAttendance']);
    Route::get('my-attendance/stats', [AttendanceController::class, 'myStats']);
    Route::get('employees/{employee}/attendance', [AttendanceController::class, 'byEmployeeMonth']);

    // Salaries
    Route::get('salaries', [SalaryController::class, 'history']);
    Route::get('salaries/{salary}', [SalaryController::class, 'show']);
    Route::put('salaries/{salary}', [SalaryController::class, 'update']);
    Route::post('employees/{employee}/generate-salary', [SalaryController::class, 'generate']);
    Route::get('salaries/{salary}/slip', [SalaryController::class, 'slip']);

    // Settings
    Route::get('settings', [SettingController::class, 'index']);
    Route::put('settings', [SettingController::class, 'update']);
    Route::post('settings/telegram', [SettingController::class, 'updateTelegram']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::post('/user/preferences', [DashboardController::class, 'updatePreferences']);

    // User context
    Route::get('/user-context', UserContextController::class);

    // Roles & permissions
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
    Route::apiResource('permissions', PermissionController::class);
});

// Shortcut to get current user with roles & permissions
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->load('roles', 'permissions');
});
