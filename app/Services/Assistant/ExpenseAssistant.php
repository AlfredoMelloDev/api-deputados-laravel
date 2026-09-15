<?php

namespace App\Services\Assistant;

use App\Models\Deputy;
use App\Models\Expense;
use App\Models\SyncRun;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ExpenseAssistant
{
    /** @var array<string, string> */
    private const CATEGORY_ALIASES = [
        'combustivel' => 'combustiveis',
        'gasolina' => 'combustiveis',
        'propaganda' => 'divulgacao',
        'publicidade' => 'divulgacao',
        'divulgacao' => 'divulgacao',
        'passagem' => 'passagem',
        'voo' => 'passagem aerea',
        'aviao' => 'passagem aerea',
        'veiculo' => 'veiculos automotores',
        'carro' => 'veiculos automotores',
        'escritorio' => 'escritorio',
        'telefone' => 'telefonia',
        'internet' => 'telefonia',
        'consultoria' => 'consultoria',
        'hospedagem' => 'hospedagem',
        'alimentacao' => 'alimentacao',
        'correio' => 'servicos postais',
        'seguranca' => 'seguranca',
        'taxi' => 'taxi',
        'pedagio' => 'pedagio',
    ];

    /** @return array<string, mixed> */
    public function answer(string $question, ?int $requestedYear = null): array
    {
        $normalizedQuestion = $this->normalize($question);
        $availableYears = Expense::query()->distinct()->orderByDesc('year')->pluck('year');
        $year = $this->yearFrom($normalizedQuestion, $requestedYear, $availableYears);
        $limit = $this->limitFrom($normalizedQuestion);
        $category = $this->resolveCategory($normalizedQuestion, $year);

        $result = match (true) {
            $this->mentions($normalizedQuestion, ['categoria', 'categorias', 'tipo de despesa', 'tipos de despesa']) => $this->topCategories($year, $limit),
            $this->mentions($normalizedQuestion, ['partido', 'partidos']) => $this->topGroups($year, 'party_acronym', 'Partidos com maiores despesas', $limit, $category),
            $this->mentions($normalizedQuestion, ['estado', 'estados', 'uf']) => $this->topGroups($year, 'state_acronym', 'Estados com maiores despesas', $limit, $category),
            $this->mentions($normalizedQuestion, ['fornecedor', 'fornecedores', 'empresa', 'empresas']) => $this->topSuppliers($year, $limit, $category),
            $category !== null && $this->mentions($normalizedQuestion, ['quanto', 'total', 'valor']) => $this->categoryTotal($year, $category),
            default => $this->topDeputies($year, $limit, $category),
        };

        return [
            ...$result,
            'year' => $year,
            'category' => $category,
            'coverage' => $this->coverage($year),
        ];
    }

    /** @return array<string, mixed> */
    private function topDeputies(int $year, int $limit, ?string $category): array
    {
        $rows = Deputy::query()
            ->join('expenses', 'expenses.deputy_id', '=', 'deputies.id')
            ->where('expenses.year', $year)
            ->when($category, fn (Builder $query, string $value) => $query->where('expenses.expense_type', $value))
            ->select(['deputies.id', 'deputies.name', 'deputies.party_acronym', 'deputies.state_acronym'])
            ->selectRaw('SUM(expenses.net_value) as total')
            ->selectRaw('COUNT(expenses.id) as expense_count')
            ->groupBy('deputies.id', 'deputies.name', 'deputies.party_acronym', 'deputies.state_acronym')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        $subject = $category ? Str::lower($category) : 'todas as categorias';

        return [
            'title' => $category ? 'Deputados que mais gastaram na categoria' : 'Deputados com maiores despesas',
            'answer' => $rows->isEmpty()
                ? "Não encontrei despesas de {$subject} em {$year}."
                : "Ranking calculado pela soma do valor líquido de {$subject} em {$year}.",
            'items' => $rows->map(fn ($row): array => [
                'label' => $row->name,
                'detail' => trim(($row->party_acronym ?: 'Sem partido').' · '.($row->state_acronym ?: 'UF não informada').' · '.number_format((int) $row->expense_count, 0, ',', '.').' despesas'),
                'value' => $this->currency($row->total),
                'url' => route('deputies.show', $row->id),
            ])->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function topCategories(int $year, int $limit): array
    {
        $rows = Expense::query()
            ->where('year', $year)
            ->selectRaw('expense_type as label, SUM(net_value) as total, COUNT(*) as expense_count')
            ->groupBy('expense_type')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return $this->rankingResult($rows, 'Categorias com maiores despesas', "Categorias ordenadas pela soma do valor líquido em {$year}.");
    }

    /** @return array<string, mixed> */
    private function topGroups(int $year, string $column, string $title, int $limit, ?string $category): array
    {
        $rows = Deputy::query()
            ->join('expenses', 'expenses.deputy_id', '=', 'deputies.id')
            ->where('expenses.year', $year)
            ->whereNotNull("deputies.{$column}")
            ->when($category, fn (Builder $query, string $value) => $query->where('expenses.expense_type', $value))
            ->selectRaw("deputies.{$column} as label, SUM(expenses.net_value) as total, COUNT(expenses.id) as expense_count")
            ->groupBy("deputies.{$column}")
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        $description = $category
            ? 'Ranking de '.Str::lower($category)." pela soma do valor líquido em {$year}."
            : "Ranking pela soma do valor líquido de todas as categorias em {$year}.";

        return $this->rankingResult($rows, $title, $description);
    }

    /** @return array<string, mixed> */
    private function topSuppliers(int $year, int $limit, ?string $category): array
    {
        $rows = Expense::query()
            ->where('year', $year)
            ->when($category, fn (Builder $query, string $value) => $query->where('expense_type', $value))
            ->selectRaw('supplier_name as label, SUM(net_value) as total, COUNT(*) as expense_count')
            ->groupBy('supplier_name')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();

        return $this->rankingResult($rows, 'Fornecedores com maiores valores', "Valores somados a partir das despesas importadas de {$year}.");
    }

    /** @return array<string, mixed> */
    private function categoryTotal(int $year, string $category): array
    {
        $query = Expense::query()->where('year', $year)->where('expense_type', $category);
        $count = (clone $query)->count();
        $total = (float) (clone $query)->sum('net_value');

        return [
            'title' => 'Total da categoria',
            'answer' => $count === 0
                ? "Não encontrei despesas dessa categoria em {$year}."
                : Str::headline(Str::lower($category))." somou {$this->currency($total)} em {$year}, considerando ".number_format($count, 0, ',', '.').' despesas.',
            'items' => [],
        ];
    }

    /** @param Collection<int, object> $rows
     * @return array<string, mixed>
     */
    private function rankingResult(Collection $rows, string $title, string $answer): array
    {
        return [
            'title' => $title,
            'answer' => $rows->isEmpty() ? 'Não encontrei despesas para essa consulta.' : $answer,
            'items' => $rows->map(fn ($row): array => [
                'label' => $row->label,
                'detail' => number_format((int) $row->expense_count, 0, ',', '.').' despesas',
                'value' => $this->currency($row->total),
                'url' => null,
            ])->all(),
        ];
    }

    private function resolveCategory(string $question, int $year): ?string
    {
        $categories = Expense::query()->where('year', $year)->distinct()->pluck('expense_type');

        foreach (self::CATEGORY_ALIASES as $alias => $needle) {
            if (str_contains($question, $alias)) {
                return $categories->first(fn (string $category): bool => str_contains($this->normalize($category), $needle));
            }
        }

        $ignored = ['qual', 'quais', 'quem', 'mais', 'gastou', 'gastos', 'despesa', 'despesas', 'deputado', 'deputados', 'candidato', 'candidatos', 'com', 'para', 'por', 'dos', 'das', 'ano', 'total', 'valor'];
        $words = collect(explode(' ', $question))->filter(fn (string $word): bool => mb_strlen($word) >= 4 && ! in_array($word, $ignored, true));

        return $categories
            ->map(fn (string $category): array => [
                'category' => $category,
                'score' => $words->filter(fn (string $word): bool => str_contains($this->normalize($category), $word))->count(),
            ])
            ->filter(fn (array $candidate): bool => $candidate['score'] > 0)
            ->sortByDesc('score')
            ->first()['category'] ?? null;
    }

    /** @param Collection<int, int> $availableYears */
    private function yearFrom(string $question, ?int $requestedYear, Collection $availableYears): int
    {
        if (preg_match('/\b(20\d{2})\b/', $question, $matches) === 1) {
            return (int) $matches[1];
        }

        return $requestedYear ?? (int) ($availableYears->first() ?? now()->year);
    }

    private function limitFrom(string $question): int
    {
        if (preg_match('/\btop\s*(\d{1,2})\b/', $question, $matches) === 1) {
            return min(10, max(1, (int) $matches[1]));
        }

        if (preg_match('/\b(\d{1,2})\s+(?:deputad|candidat|categoria|partido|estado|fornecedor)/', $question, $matches) === 1) {
            return min(10, max(1, (int) $matches[1]));
        }

        return 3;
    }

    /** @return array{status: string, message: string} */
    private function coverage(int $year): array
    {
        $run = SyncRun::query()->where('year', $year)->latest('started_at')->first();

        return match ($run?->status) {
            'completed' => ['status' => 'complete', 'message' => "Dados de {$year} sincronizados."],
            'processing' => ['status' => 'partial', 'message' => "A sincronização de {$year} está em andamento; o resultado é parcial."],
            'failed', 'completed_with_errors' => ['status' => 'partial', 'message' => "A sincronização de {$year} tem pendências; o resultado pode estar incompleto."],
            default => ['status' => 'unverified', 'message' => "A cobertura de {$year} ainda não foi verificada."],
        };
    }

    /** @param array<int, string> $needles */
    private function mentions(string $question, array $needles): bool
    {
        return collect($needles)->contains(fn (string $needle): bool => str_contains($question, $needle));
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->toString();
    }

    private function currency(float|string $value): string
    {
        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }
}
