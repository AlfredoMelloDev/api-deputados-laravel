<?php

use App\Http\Controllers\DeputyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DeputyController::class, 'index'])->name('deputies.index');
Route::get('/deputados/{deputy}', [DeputyController::class, 'show'])->name('deputies.show');
