<?php

namespace App\Http\Controllers;

use App\Models\Deputy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DeputyController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'party' => ['nullable', 'string', 'max:20'],
            'state' => ['nullable', 'string', 'size:2'],
        ]);

        $deputies = Deputy::query()
            ->withCount('expenses')
            ->withSum('expenses', 'net_value')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                ->where('name', 'like', '%'.$search.'%'))
            ->when($filters['party'] ?? null, fn ($query, string $party) => $query
                ->where('party_acronym', $party))
            ->when($filters['state'] ?? null, fn ($query, string $state) => $query
                ->where('state_acronym', strtoupper($state)))
            ->orderBy('name')
            ->paginate(18)
            ->withQueryString();

        return view('deputies.index', [
            'deputies' => $deputies,
            'parties' => Deputy::query()->whereNotNull('party_acronym')->distinct()->orderBy('party_acronym')->pluck('party_acronym'),
            'states' => Deputy::query()->whereNotNull('state_acronym')->distinct()->orderBy('state_acronym')->pluck('state_acronym'),
        ]);
    }
}
