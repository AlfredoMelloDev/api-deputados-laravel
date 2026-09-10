<?php

use App\Http\Controllers\DeputyController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/openapi.yaml', fn (): Response => response(
    file_get_contents(public_path('openapi.yaml')),
    200,
    ['Content-Type' => 'application/yaml; charset=UTF-8'],
))->name('api.openapi');

Route::get('/', [DeputyController::class, 'index'])->name('deputies.index');
Route::get('/deputados/{deputy}/despesas/exportar', [DeputyController::class, 'export'])->name('deputies.expenses.export');
Route::get('/deputados/{deputy}', [DeputyController::class, 'show'])->name('deputies.show');
