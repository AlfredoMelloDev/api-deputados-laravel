<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExpenseResource;
use App\Models\Deputy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DeputyExpenseController extends Controller
{
    public function __invoke(Request $request, Deputy $deputy): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'ano' => ['nullable', 'integer', 'digits:4', 'min:2008', 'max:'.now()->year],
            'mes' => ['nullable', 'integer', 'between:1,12'],
            'tipo' => ['nullable', 'string', 'max:255'],
            'fornecedor' => ['nullable', 'string', 'max:150'],
            'data_inicial' => ['nullable', 'date'],
            'data_final' => ['nullable', 'date', 'after_or_equal:data_inicial'],
            'por_pagina' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $expenses = $deputy->expenses()
            ->when($filters['ano'] ?? null, fn ($query, int $year) => $query->where('year', $year))
            ->when($filters['mes'] ?? null, fn ($query, int $month) => $query->where('month', $month))
            ->when($filters['tipo'] ?? null, fn ($query, string $type) => $query->where('expense_type', $type))
            ->when($filters['fornecedor'] ?? null, fn ($query, string $supplier) => $query->where('supplier_name', 'like', '%'.$supplier.'%'))
            ->when($filters['data_inicial'] ?? null, fn ($query, string $date) => $query->whereDate('document_date', '>=', $date))
            ->when($filters['data_final'] ?? null, fn ($query, string $date) => $query->whereDate('document_date', '<=', $date))
            ->latest('document_date')
            ->latest('id')
            ->paginate($filters['por_pagina'] ?? 20)
            ->withQueryString();

        return ExpenseResource::collection($expenses);
    }
}
