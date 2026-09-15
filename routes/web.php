<?php

use App\Http\Controllers\DeputyController;
use App\Http\Controllers\ExpenseAssistantController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/openapi.yaml', fn (): Response => response(
    file_get_contents(public_path('openapi.yaml')),
    200,
    ['Content-Type' => 'application/yaml; charset=UTF-8'],
))->name('api.openapi');

Route::get('/', [DeputyController::class, 'index'])->name('deputies.index');
Route::post('/assistente/perguntar', ExpenseAssistantController::class)->middleware('throttle:20,1')->name('assistant.ask');
Route::get('/deputados/{deputy}/despesas/exportar', [DeputyController::class, 'export'])->name('deputies.expenses.export');
Route::get('/deputados/{deputy}', [DeputyController::class, 'show'])->name('deputies.show');
