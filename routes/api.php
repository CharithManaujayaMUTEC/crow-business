<?php

use App\Http\Controllers\Api\EmployeePortalController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/auth/login', [
        EmployeePortalController::class,
        'login',
    ]);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [
            EmployeePortalController::class,
            'logout',
        ]);

        Route::get('/me', [
            EmployeePortalController::class,
            'me',
        ]);

        Route::get('/dashboard', [
            EmployeePortalController::class,
            'dashboard',
        ]);

        Route::get('/attendance', [
            EmployeePortalController::class,
            'attendance',
        ]);

        Route::post('/attendance/check-in', [
            EmployeePortalController::class,
            'checkIn',
        ]);

        Route::post('/attendance/check-out', [
            EmployeePortalController::class,
            'checkOut',
        ]);

        Route::get('/leave-types', [
            EmployeePortalController::class,
            'leaveTypes',
        ]);

        Route::get('/leaves', [
            EmployeePortalController::class,
            'leaves',
        ]);

        Route::post('/leaves', [
            EmployeePortalController::class,
            'applyLeave',
        ]);

        Route::post('/leaves/{leaveRequest}/cancel', [
            EmployeePortalController::class,
            'cancelLeave',
        ]);

        Route::get('/leave-approvals', [
            EmployeePortalController::class,
            'approvalRequests',
        ]);

        Route::patch('/leave-approvals/{leaveRequest}', [
            EmployeePortalController::class,
            'approveLeave',
        ]);

        Route::get('/tasks', [
            TaskController::class,
            'index',
        ]);

        Route::post('/tasks', [
            TaskController::class,
            'store',
        ]);

        Route::patch('/tasks/{task}', [
            TaskController::class,
            'update',
        ]);

        Route::get('/task-employees', [
            TaskController::class,
            'employees',
        ]);
    });
});
