<?php

use App\Http\Controllers\Api\SmsPackageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/sms-packages', [SmsPackageController::class, 'index']);
});
