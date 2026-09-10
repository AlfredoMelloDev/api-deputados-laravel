<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DeputyResource;
use App\Models\Deputy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DeputyController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'nome' => ['nullable', 'string', 'max:100'],
            'partido' => ['nullable', 'string', 'max:20'],
            'uf' => ['nullable', 'string', 'size:2'],
            'ano_despesas' => ['nullable', 'integer', 'digits:4', 'min:2008', 'max:'.now()->year],
            'por_pagina' => ['nullable', 'integer', 'between:1,100'],
        ]);

        $expenseYear = $filters['ano_despesas'] ?? null;
        $expenseConstraint = fn ($query) => $query->when($expenseYear, fn ($query, int $year) => $query->where('year', $year));

        $deputies = Deputy::query()
            ->withCount(['expenses' => $expenseConstraint])
            ->withSum(['expenses' => $expenseConstraint], 'net_value')
            ->when($filters['nome'] ?? null, fn ($query, string $name) => $query->where('name', 'like', '%'.$name.'%'))
            ->when($filters['partido'] ?? null, fn ($query, string $party) => $query->where('party_acronym', strtoupper($party)))
            ->when($filters['uf'] ?? null, fn ($query, string $state) => $query->where('state_acronym', strtoupper($state)))
            ->orderBy('name')
            ->paginate($filters['por_pagina'] ?? 20)
            ->withQueryString();

        return DeputyResource::collection($deputies);
    }

    public function show(Deputy $deputy): DeputyResource
    {
        $deputy->loadCount('expenses')->loadSum('expenses', 'net_value');

        return new DeputyResource($deputy);
    }
}
