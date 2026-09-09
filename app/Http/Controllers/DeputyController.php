<?php

namespace App\Http\Controllers;

use App\Models\Deputy;
use App\Models\Expense;
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
            'expense_year' => ['nullable', 'integer', 'digits:4', 'min:2008', 'max:'.now()->year],
        ]);

        $availableYears = Expense::query()->distinct()->orderByDesc('year')->pluck('year');
        $analyticsYear = (int) ($filters['expense_year'] ?? ($availableYears->contains(2025) ? 2025 : ($availableYears->first() ?? now()->year)));
        $yearExpenses = Expense::query()->where('year', $analyticsYear);

        $analytics = [
            'count' => (clone $yearExpenses)->count(),
            'total' => (float) (clone $yearExpenses)->sum('net_value'),
            'deputies' => (clone $yearExpenses)->distinct()->count('deputy_id'),
        ];

        $topExpenseTypes = (clone $yearExpenses)
            ->selectRaw('expense_type, SUM(net_value) as total')
            ->groupBy('expense_type')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

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
            'availableYears' => $availableYears,
            'analyticsYear' => $analyticsYear,
            'analytics' => $analytics,
            'topExpenseTypes' => $topExpenseTypes,
        ]);
    }

    public function show(Request $request, Deputy $deputy): View
    {
        $filters = $request->validate([
            'year' => ['nullable', 'integer', 'digits:4', 'min:2008', 'max:'.now()->year],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'type' => ['nullable', 'string', 'max:255'],
        ]);

        $expenseQuery = $deputy->expenses()
            ->when($filters['year'] ?? null, fn ($query, int $year) => $query->where('year', $year))
            ->when($filters['month'] ?? null, fn ($query, int $month) => $query->where('month', $month))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('expense_type', $type));

        return view('deputies.show', [
            'deputy' => $deputy,
            'expenses' => (clone $expenseQuery)->latest('document_date')->latest('id')->paginate(20)->withQueryString(),
            'expenseCount' => (clone $expenseQuery)->count(),
            'expenseTotal' => (float) (clone $expenseQuery)->sum('net_value'),
            'years' => $deputy->expenses()->distinct()->orderByDesc('year')->pluck('year'),
            'types' => $deputy->expenses()->distinct()->orderBy('expense_type')->pluck('expense_type'),
        ]);
    }
}
