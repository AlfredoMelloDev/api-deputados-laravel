<?php

use App\Http\Controllers\Api\DeputyController;
use App\Http\Controllers\Api\DeputyExpenseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function (): void {
    Route::get('/deputados', [DeputyController::class, 'index'])->name('api.v1.deputies.index');
    Route::get('/deputados/{deputy:camara_id}', [DeputyController::class, 'show'])->name('api.v1.deputies.show');
    Route::get('/deputados/{deputy:camara_id}/despesas', DeputyExpenseController::class)->name('api.v1.deputies.expenses.index');
});
