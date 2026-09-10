<?php

namespace Tests\Feature\Http;

use App\Models\Deputy;
use App\Models\Expense;
use App\Models\SyncRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeputyIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_deputies_in_alphabetical_order(): void
    {
        Deputy::factory()->create(['name' => 'Zenaide Silva']);
        Deputy::factory()->create(['name' => 'Ana Souza']);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Ana Souza', 'Zenaide Silva']);
    }

    public function test_it_filters_deputies_by_name_party_and_state(): void
    {
        Deputy::factory()->create(['name' => 'Ana Souza', 'party_acronym' => 'PT', 'state_acronym' => 'SP']);
        Deputy::factory()->create(['name' => 'Ana Lima', 'party_acronym' => 'PL', 'state_acronym' => 'RJ']);
        Deputy::factory()->create(['name' => 'Carlos Souza', 'party_acronym' => 'PT', 'state_acronym' => 'SP']);

        $this->get('/?search=Ana&party=PT&state=SP')
            ->assertOk()
            ->assertSee('Ana Souza')
            ->assertDontSee('Ana Lima')
            ->assertDontSee('Carlos Souza');
    }

    public function test_it_displays_analytics_for_the_selected_expense_year(): void
    {
        $deputy = Deputy::factory()->create(['name' => 'Deputada Principal']);
        $otherDeputy = Deputy::factory()->create(['name' => 'Deputado Secundário']);
        Expense::factory()->for($deputy)->create(['year' => 2025, 'month' => 2, 'expense_type' => 'PASSAGENS', 'net_value' => 1200.50]);
        Expense::factory()->for($otherDeputy)->create(['year' => 2025, 'month' => 3, 'expense_type' => 'PASSAGENS', 'net_value' => 100]);
        Expense::factory()->for($deputy)->create(['year' => 2024, 'expense_type' => 'COMBUSTÍVEIS', 'net_value' => 300]);

        $this->get('/?expense_year=2025')
            ->assertOk()
            ->assertSee('Visão geral de 2025')
            ->assertSee('R$ 1.300,50')
            ->assertSee('PASSAGENS')
            ->assertDontSee('COMBUSTÍVEIS')
            ->assertSee('Evolução mensal de 2025')
            ->assertSee('Deputados com maiores despesas')
            ->assertSeeInOrder(['Deputada Principal', 'Deputado Secundário']);
    }

    public function test_it_displays_the_latest_synchronization_runs(): void
    {
        SyncRun::query()->create([
            'year' => 2025,
            'status' => 'completed',
            'total_deputies' => 513,
            'processed_deputies' => 513,
            'successful_jobs' => 513,
            'expenses_received' => 42554,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
        ]);
        SyncRun::query()->create([
            'year' => 2026,
            'status' => 'failed',
            'last_error' => 'API indisponível',
            'started_at' => now()->addSecond(),
            'finished_at' => now()->addSecond(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Histórico de sincronizações')
            ->assertSee('42.554 despesas')
            ->assertSee('Concluída')
            ->assertSee('A última sincronização apresentou falhas')
            ->assertSee('API indisponível');
    }
}
