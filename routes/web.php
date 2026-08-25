<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;

Route::get('/', function () {
    return view('redirect-admin');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pdf/quotations/{quotation}', [PdfController::class, 'quotation'])
        ->name('pdf.quotation');

    Route::get('/pdf/invoices/{invoice}', [PdfController::class, 'invoice'])
        ->name('pdf.invoice');

    Route::get('/pdf/payments/{payment}', [PdfController::class, 'payment'])
        ->name('pdf.payment');
});