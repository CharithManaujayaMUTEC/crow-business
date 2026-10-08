<?php

use App\Http\Controllers\Api\EmployeePortalController;
use App\Http\Controllers\Api\SmsPackageController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Existing SMS API
    Route::get('/sms-packages', [SmsPackageController::class, 'index']);

    // Crow Desk authentication
    Route::post('/auth/login', [EmployeePortalController::class, 'login']);

    /*
    |--------------------------------------------------------------------------
    | Authenticated Crow Desk API
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [EmployeePortalController::class, 'logout']);

        Route::get('/me', [EmployeePortalController::class, 'me']);

        Route::get('/dashboard', [EmployeePortalController::class, 'dashboard']);

        /*
        |--------------------------------------------------------------------------
        | Attendance
        |--------------------------------------------------------------------------
        */

        Route::get('/attendance', [EmployeePortalController::class, 'attendance']);

        Route::post(
            '/attendance/check-in',
            [EmployeePortalController::class, 'checkIn']
        );

        Route::post(
            '/attendance/check-out',
            [EmployeePortalController::class, 'checkOut']
        );

        /*
        |--------------------------------------------------------------------------
        | Leave Management
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/leave-types',
            [EmployeePortalController::class, 'leaveTypes']
        );

        Route::get(
            '/leaves',
            [EmployeePortalController::class, 'leaves']
        );

        Route::post(
            '/leaves',
            [EmployeePortalController::class, 'createLeave']
        );

        Route::post(
            '/leaves/{leaveRequest}/cancel',
            [EmployeePortalController::class, 'cancelLeave']
        );

        /*
        |--------------------------------------------------------------------------
        | Leave Approvals
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/leave-approvals',
            [EmployeePortalController::class, 'approvals']
        );

        Route::patch(
            '/leave-approvals/{leaveRequest}',
            [EmployeePortalController::class, 'updateApproval']
        );

        /*
        |--------------------------------------------------------------------------
        | Employee Tasks
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/tasks',
            [TaskController::class, 'index']
        );

        Route::post(
            '/tasks',
            [TaskController::class, 'store']
        );

        Route::patch(
            '/tasks/{task}',
            [TaskController::class, 'update']
        );

        Route::get(
            '/task-employees',
            [TaskController::class, 'employees']
        );
    });
});
