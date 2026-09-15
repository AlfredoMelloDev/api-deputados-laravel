<?php

namespace Tests\Feature\Http;

use App\Models\Deputy;
use App\Models\Expense;
use App\Models\SyncRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ranks_deputies_by_an_expense_category_alias(): void
    {
        $leader = Deputy::factory()->create(['name' => 'Deputada Líder']);
        $second = Deputy::factory()->create(['name' => 'Deputado Segundo']);
        Expense::factory()->for($leader)->create(['year' => 2025, 'expense_type' => 'COMBUSTÍVEIS E LUBRIFICANTES.', 'net_value' => 500]);
        Expense::factory()->for($leader)->create(['year' => 2025, 'expense_type' => 'COMBUSTÍVEIS E LUBRIFICANTES.', 'net_value' => 250]);
        Expense::factory()->for($second)->create(['year' => 2025, 'expense_type' => 'COMBUSTÍVEIS E LUBRIFICANTES.', 'net_value' => 600]);
        Expense::factory()->for($second)->create(['year' => 2025, 'expense_type' => 'DIVULGAÇÃO DA ATIVIDADE PARLAMENTAR.', 'net_value' => 9000]);

        $this->postJson(route('assistant.ask'), ['question' => 'Qual candidato gastou mais com combustível em 2025?'])
            ->assertOk()
            ->assertJsonPath('year', 2025)
            ->assertJsonPath('category', 'COMBUSTÍVEIS E LUBRIFICANTES.')
            ->assertJsonPath('items.0.label', 'Deputada Líder')
            ->assertJsonPath('items.0.value', 'R$ 750,00')
            ->assertJsonCount(2, 'items');
    }

    public function test_it_understands_propaganda_as_parliamentary_publicity(): void
    {
        $deputy = Deputy::factory()->create(['name' => 'Deputado Divulgação']);
        Expense::factory()->for($deputy)->create(['year' => 2025, 'expense_type' => 'DIVULGAÇÃO DA ATIVIDADE PARLAMENTAR.', 'net_value' => 1200]);

        $this->postJson(route('assistant.ask'), ['question' => 'Quem gastou mais com propaganda?', 'year' => 2025])
            ->assertOk()
            ->assertJsonPath('category', 'DIVULGAÇÃO DA ATIVIDADE PARLAMENTAR.')
            ->assertJsonPath('items.0.label', 'Deputado Divulgação');
    }

    public function test_it_aggregates_the_largest_expense_categories(): void
    {
        Expense::factory()->create(['year' => 2025, 'expense_type' => 'PASSAGENS', 'net_value' => 500]);
        Expense::factory()->create(['year' => 2025, 'expense_type' => 'PASSAGENS', 'net_value' => 700]);
        Expense::factory()->create(['year' => 2025, 'expense_type' => 'COMBUSTÍVEIS', 'net_value' => 900]);

        $this->postJson(route('assistant.ask'), ['question' => 'Quais são as maiores categorias?', 'year' => 2025])
            ->assertOk()
            ->assertJsonPath('items.0.label', 'PASSAGENS')
            ->assertJsonPath('items.0.value', 'R$ 1.200,00')
            ->assertJsonPath('items.1.label', 'COMBUSTÍVEIS');
    }

    public function test_it_does_not_use_the_year_as_the_ranking_limit(): void
    {
        Expense::factory()
            ->count(12)
            ->sequence(fn ($sequence): array => ['expense_type' => 'CATEGORIA '.$sequence->index])
            ->create(['year' => 2025]);

        $this->postJson(route('assistant.ask'), ['question' => 'Quais são as maiores categorias em 2025?'])
            ->assertOk()
            ->assertJsonCount(3, 'items');
    }

    public function test_it_returns_a_top_three_by_default_for_a_singular_question(): void
    {
        $deputies = Deputy::factory()->count(4)->create();

        foreach ($deputies as $index => $deputy) {
            Expense::factory()->for($deputy)->create([
                'year' => 2025,
                'expense_type' => 'COMBUSTÍVEIS E LUBRIFICANTES.',
                'net_value' => 1000 - ($index * 100),
            ]);
        }

        $this->postJson(route('assistant.ask'), ['question' => 'Quem gastou mais com combustível?', 'year' => 2025])
            ->assertOk()
            ->assertJsonCount(3, 'items');
    }

    public function test_it_aggregates_expenses_by_party(): void
    {
        $first = Deputy::factory()->create(['party_acronym' => 'ABC']);
        $second = Deputy::factory()->create(['party_acronym' => 'XYZ']);
        Expense::factory()->for($first)->create(['year' => 2025, 'net_value' => 1000]);
        Expense::factory()->for($second)->create(['year' => 2025, 'net_value' => 500]);

        $this->postJson(route('assistant.ask'), ['question' => 'Qual partido gastou mais?', 'year' => 2025])
            ->assertOk()
            ->assertJsonPath('items.0.label', 'ABC')
            ->assertJsonPath('items.0.value', 'R$ 1.000,00');
    }

    public function test_it_reports_incomplete_data_coverage(): void
    {
        Expense::factory()->create(['year' => 2026]);
        SyncRun::query()->create(['year' => 2026, 'status' => 'processing', 'total_deputies' => 513, 'processed_deputies' => 10, 'started_at' => now()]);

        $this->postJson(route('assistant.ask'), ['question' => 'Quem gastou mais?', 'year' => 2026])
            ->assertOk()
            ->assertJsonPath('coverage.status', 'partial')
            ->assertJsonPath('coverage.message', 'A sincronização de 2026 está em andamento; o resultado é parcial.');
    }

    public function test_it_validates_the_question(): void
    {
        $this->postJson(route('assistant.ask'), ['question' => 'oi'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('question');
    }
}
