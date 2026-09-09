<?php

namespace Tests\Feature\Console;

use App\Jobs\SyncDeputyExpenses;
use App\Models\SyncRun;
use App\Services\Camara\CamaraApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use Tests\TestCase;

class SyncDeputiesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_deputies_and_dispatches_one_expense_job_for_each_one(): void
    {
        Queue::fake();

        $this->mock(CamaraApiClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('deputies')->once()->andReturn([
                $this->deputyPayload(101, 'Deputada Um'),
                $this->deputyPayload(202, 'Deputado Dois'),
                $this->deputyPayload(101, 'Deputada Um'),
            ]);
        });

        $this->artisan('camara:sync-deputies', ['--year' => 2026])
            ->expectsOutputToContain('2 deputados sincronizados')
            ->assertSuccessful();

        $this->assertDatabaseHas('deputies', ['camara_id' => 101, 'name' => 'Deputada Um']);
        $this->assertDatabaseCount('deputies', 2);
        $this->assertDatabaseHas('sync_runs', [
            'year' => 2026,
            'status' => 'processing',
            'total_deputies' => 2,
        ]);
        Queue::assertPushed(SyncDeputyExpenses::class, 2);
        Queue::assertPushed(fn (SyncDeputyExpenses $job): bool => $job->year === 2026 && $job->syncRunId !== null);
    }

    public function test_it_rejects_a_year_before_expense_data_exists(): void
    {
        Queue::fake();

        $this->artisan('camara:sync-deputies', ['--year' => 2007])
            ->expectsOutput('Informe um ano entre 2008 e o ano atual.')
            ->assertFailed();

        Queue::assertNothingPushed();
    }

    public function test_it_does_not_start_a_duplicate_synchronization(): void
    {
        Queue::fake();
        SyncRun::query()->create([
            'year' => 2026,
            'status' => 'processing',
            'total_deputies' => 513,
            'started_at' => now(),
        ]);

        $this->artisan('camara:sync-deputies', ['--year' => 2026])
            ->expectsOutput('Já existe uma sincronização de 2026 em andamento.')
            ->assertSuccessful();

        Queue::assertNothingPushed();
        $this->assertDatabaseCount('sync_runs', 1);
    }

    public function test_the_deputies_synchronization_is_scheduled(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('camara:sync-deputies')
            ->assertSuccessful();
    }

    /** @return array<string, mixed> */
    private function deputyPayload(int $id, string $name): array
    {
        return [
            'id' => $id,
            'uri' => "https://dadosabertos.camara.leg.br/api/v2/deputados/{$id}",
            'nome' => $name,
            'siglaPartido' => 'ABC',
            'uriPartido' => 'https://dadosabertos.camara.leg.br/api/v2/partidos/1',
            'siglaUf' => 'RS',
            'idLegislatura' => 57,
            'urlFoto' => 'https://www.camara.leg.br/internet/deputado/bandep/'.$id.'.jpg',
            'email' => 'deputado@example.com',
        ];
    }
}
