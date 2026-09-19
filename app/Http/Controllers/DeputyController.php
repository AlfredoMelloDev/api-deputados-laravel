<?php

namespace App\Http\Controllers;

use App\Models\Deputy;
use App\Models\Expense;
use App\Models\SyncRun;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $analyticsYear = (int) ($filters['expense_year'] ?? ($availableYears->first() ?? now()->year));
        $yearExpenses = Expense::query()->where('year', $analyticsYear);
        $analyticsSyncRun = SyncRun::query()
            ->where('year', $analyticsYear)
            ->latest('started_at')
            ->first();

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

        $monthlyExpenses = (clone $yearExpenses)
            ->selectRaw('month, SUM(net_value) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $topDeputies = Deputy::query()
            ->join('expenses', 'expenses.deputy_id', '=', 'deputies.id')
            ->where('expenses.year', $analyticsYear)
            ->select(['deputies.id', 'deputies.name', 'deputies.party_acronym', 'deputies.state_acronym', 'deputies.photo_url'])
            ->selectRaw('SUM(expenses.net_value) as total')
            ->groupBy('deputies.id', 'deputies.name', 'deputies.party_acronym', 'deputies.state_acronym', 'deputies.photo_url')
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

        $syncRuns = SyncRun::query()
            ->latest('started_at')
            ->latest('id')
            ->get()
            ->unique('year')
            ->take(5)
            ->values();

        return view('deputies.index', [
            'deputies' => $deputies,
            'deputyNames' => Deputy::query()->orderBy('name')->pluck('name'),
            'parties' => Deputy::query()->whereNotNull('party_acronym')->distinct()->orderBy('party_acronym')->pluck('party_acronym'),
            'states' => Deputy::query()->whereNotNull('state_acronym')->distinct()->orderBy('state_acronym')->pluck('state_acronym'),
            'availableYears' => $availableYears,
            'analyticsYear' => $analyticsYear,
            'analytics' => $analytics,
            'topExpenseTypes' => $topExpenseTypes,
            'monthlyExpenses' => $monthlyExpenses,
            'topDeputies' => $topDeputies,
            'analyticsSyncRun' => $analyticsSyncRun,
            'syncRuns' => $syncRuns,
        ]);
    }

    public function show(Request $request, Deputy $deputy): View
    {
        $expenseQuery = $this->filteredExpenses($deputy, $this->expenseFilters($request));

        return view('deputies.show', [
            'deputy' => $deputy,
            'expenses' => (clone $expenseQuery)->latest('document_date')->latest('id')->paginate(20)->withQueryString(),
            'expenseCount' => (clone $expenseQuery)->count(),
            'expenseTotal' => (float) (clone $expenseQuery)->sum('net_value'),
            'years' => $deputy->expenses()->distinct()->orderByDesc('year')->pluck('year'),
            'types' => $deputy->expenses()->distinct()->orderBy('expense_type')->pluck('expense_type'),
        ]);
    }

    public function export(Request $request, Deputy $deputy): StreamedResponse
    {
        $expenses = $this->filteredExpenses($deputy, $this->expenseFilters($request));
        $filename = 'despesas-'.Str::slug($deputy->name).'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($expenses): void {
            echo "\xEF\xBB\xBF";
            $output = fopen('php://output', 'w');

            fputcsv($output, ['Data', 'Ano', 'Mês', 'Tipo', 'Fornecedor', 'CNPJ/CPF', 'Documento', 'Valor do documento', 'Valor líquido', 'Valor da glosa', 'URL'], ';', '"', '');

            $expenses->orderBy('id')->chunkById(500, function ($chunk) use ($output): void {
                foreach ($chunk as $expense) {
                    fputcsv($output, [
                        $expense->document_date?->format('d/m/Y'),
                        $expense->year,
                        $expense->month,
                        $this->safeCsvCell($expense->expense_type),
                        $this->safeCsvCell($expense->supplier_name),
                        $this->safeCsvCell($expense->supplier_tax_id),
                        $this->safeCsvCell($expense->document_number),
                        number_format((float) $expense->document_value, 2, ',', ''),
                        number_format((float) $expense->net_value, 2, ',', ''),
                        number_format((float) $expense->disallowance_value, 2, ',', ''),
                        $this->safeCsvCell($expense->document_url),
                    ], ';', '"', '');
                }
            });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array<string, mixed> */
    private function expenseFilters(Request $request): array
    {
        return $request->validate([
            'year' => ['nullable', 'integer', 'digits:4', 'min:2008', 'max:'.now()->year],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'type' => ['nullable', 'string', 'max:255'],
            'supplier' => ['nullable', 'string', 'max:150'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }

    /** @param array<string, mixed> $filters */
    private function filteredExpenses(Deputy $deputy, array $filters): HasMany
    {
        return $deputy->expenses()
            ->when($filters['year'] ?? null, fn ($query, int $year) => $query->where('year', $year))
            ->when($filters['month'] ?? null, fn ($query, int $month) => $query->where('month', $month))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('expense_type', $type))
            ->when($filters['supplier'] ?? null, fn ($query, string $supplier) => $query->where('supplier_name', 'like', '%'.$supplier.'%'))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('document_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('document_date', '<=', $date));
    }

    private function safeCsvCell(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^[=+\-@]/', $value) === 1 ? "'{$value}" : $value;
    }
}
