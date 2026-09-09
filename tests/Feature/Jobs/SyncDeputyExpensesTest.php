<?php

namespace Tests\Feature\Jobs;

use App\Jobs\SyncDeputyExpenses;
use App\Models\Deputy;
use App\Services\Camara\CamaraApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncDeputyExpensesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_expenses_idempotently_and_marks_the_deputy_as_synced(): void
    {
        $deputy = Deputy::factory()->create(['camara_id' => 204554, 'legislature_id' => 57]);

        Http::fake([
            '*' => Http::sequence()
                ->push(['dados' => [$this->expensePayload(100.50)], 'links' => []])
                ->push(['dados' => [$this->expensePayload(90.25)], 'links' => []]),
        ]);

        $job = new SyncDeputyExpenses($deputy->id, 2026);
        $job->handle(app(CamaraApiClient::class));
        $job->handle(app(CamaraApiClient::class));

        $this->assertDatabaseCount('expenses', 1);
        $this->assertDatabaseHas('expenses', [
            'deputy_id' => $deputy->id,
            'document_code' => 987654,
            'net_value' => 90.25,
        ]);
        $this->assertNotNull($deputy->refresh()->expenses_synced_at);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'idLegislatura=57'));
    }

    /** @return array<string, mixed> */
    private function expensePayload(float $netValue): array
    {
        return [
            'ano' => 2026,
            'mes' => 8,
            'tipoDespesa' => 'COMBUSTÍVEIS E LUBRIFICANTES',
            'codDocumento' => 987654,
            'tipoDocumento' => 'Nota Fiscal',
            'codTipoDocumento' => 0,
            'dataDocumento' => '2026-08-15',
            'numDocumento' => 'NF-123',
            'valorDocumento' => 100.50,
            'valorLiquido' => $netValue,
            'valorGlosa' => 10.25,
            'nomeFornecedor' => 'Fornecedor Teste Ltda.',
            'cnpjCpfFornecedor' => '12345678000199',
            'urlDocumento' => 'https://www.camara.leg.br/cota-parlamentar/documento/987654',
            'numRessarcimento' => '',
            'codLote' => 456789,
            'parcela' => 0,
        ];
    }
}
