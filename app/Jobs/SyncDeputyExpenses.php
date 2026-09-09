<?php

namespace App\Jobs;

use App\Models\Deputy;
use App\Models\Expense;
use App\Models\SyncRun;
use App\Services\Camara\CamaraApiClient;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncDeputyExpenses implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 30, 60];

    public int $uniqueFor = 3600;

    public function __construct(
        public int $deputyId,
        public int $year,
        public ?int $syncRunId = null,
    ) {}

    public function uniqueId(): string
    {
        return "{$this->deputyId}:{$this->year}";
    }

    public function handle(CamaraApiClient $client): void
    {
        $deputy = Deputy::query()->findOrFail($this->deputyId);
        $expenses = $client->expenses($deputy->camara_id, $this->year, $deputy->legislature_id);
        $timestamp = now();

        $records = array_map(fn (array $data): array => [
            'deputy_id' => $deputy->id,
            'external_key' => $this->externalKey($data),
            'year' => (int) $data['ano'],
            'month' => (int) $data['mes'],
            'expense_type' => (string) $data['tipoDespesa'],
            'document_code' => $data['codDocumento'] ?? null,
            'document_type' => $data['tipoDocumento'] ?? null,
            'document_type_code' => $data['codTipoDocumento'] ?? null,
            'document_date' => $data['dataDocumento'] ?: null,
            'document_number' => $data['numDocumento'] ?: null,
            'document_value' => $data['valorDocumento'] ?? 0,
            'net_value' => $data['valorLiquido'] ?? 0,
            'disallowance_value' => $data['valorGlosa'] ?? 0,
            'supplier_name' => (string) $data['nomeFornecedor'],
            'supplier_tax_id' => $data['cnpjCpfFornecedor'] ?: null,
            'document_url' => $data['urlDocumento'] ?: null,
            'reimbursement_number' => $data['numRessarcimento'] ?: null,
            'batch_code' => $data['codLote'] ?? null,
            'installment' => $data['parcela'] ?? null,
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $expenses);

        DB::transaction(function () use ($deputy, $records, $timestamp): void {
            foreach (array_chunk($records, 500) as $chunk) {
                Expense::query()->upsert(
                    $chunk,
                    ['deputy_id', 'external_key'],
                    [
                        'year', 'month', 'expense_type', 'document_code', 'document_type',
                        'document_type_code', 'document_date', 'document_number',
                        'document_value', 'net_value', 'disallowance_value', 'supplier_name',
                        'supplier_tax_id', 'document_url', 'reimbursement_number', 'batch_code',
                        'installment', 'updated_at',
                    ],
                );
            }

            $deputy->update(['expenses_synced_at' => $timestamp]);
        });

        $this->syncRun()?->recordSuccess(count($records));
    }

    public function failed(?Throwable $exception): void
    {
        $this->syncRun()?->recordFailure();
    }

    /** @param array<string, mixed> $data */
    private function externalKey(array $data): string
    {
        return hash('sha256', json_encode([
            $data['codDocumento'] ?? null,
            $data['codLote'] ?? null,
            $data['numDocumento'] ?? null,
            $data['parcela'] ?? null,
            $data['ano'] ?? null,
            $data['mes'] ?? null,
            $data['cnpjCpfFornecedor'] ?? null,
            $data['tipoDespesa'] ?? null,
        ], JSON_THROW_ON_ERROR));
    }

    private function syncRun(): ?SyncRun
    {
        return $this->syncRunId === null ? null : SyncRun::query()->find($this->syncRunId);
    }
}
