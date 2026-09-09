<?php

namespace Tests\Feature\Http;

use App\Models\Deputy;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeputyShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_the_deputy_and_expenses(): void
    {
        $deputy = Deputy::factory()->create(['name' => 'Ana Souza']);
        Expense::factory()->for($deputy)->create([
            'expense_type' => 'PASSAGEM AÉREA',
            'supplier_name' => 'Companhia Brasileira',
            'net_value' => 1250.50,
        ]);

        $this->get(route('deputies.show', $deputy))
            ->assertOk()
            ->assertSee('Ana Souza')
            ->assertSee('PASSAGEM AÉREA')
            ->assertSee('Companhia Brasileira')
            ->assertSee('R$ 1.250,50');
    }

    public function test_it_filters_expenses_and_recalculates_the_summary(): void
    {
        $deputy = Deputy::factory()->create();
        Expense::factory()->for($deputy)->create(['year' => 2025, 'month' => 3, 'expense_type' => 'COMBUSTÍVEIS', 'supplier_name' => 'Posto Central', 'net_value' => 100]);
        Expense::factory()->for($deputy)->create(['year' => 2026, 'month' => 4, 'expense_type' => 'PASSAGEM', 'supplier_name' => 'Companhia Aérea', 'net_value' => 900]);

        $this->get(route('deputies.show', [$deputy, 'year' => 2025, 'month' => 3, 'type' => 'COMBUSTÍVEIS']))
            ->assertOk()
            ->assertSee('Posto Central')
            ->assertSee('R$ 100,00')
            ->assertDontSee('Companhia Aérea');
    }

    public function test_it_filters_expenses_by_supplier_and_date_range(): void
    {
        $deputy = Deputy::factory()->create();
        Expense::factory()->for($deputy)->create(['supplier_name' => 'Posto Central', 'document_date' => '2025-03-15']);
        Expense::factory()->for($deputy)->create(['supplier_name' => 'Posto Avenida', 'document_date' => '2025-05-20']);
        Expense::factory()->for($deputy)->create(['supplier_name' => 'Companhia Aérea', 'document_date' => '2025-03-15']);

        $this->get(route('deputies.show', [
            $deputy,
            'supplier' => 'Posto',
            'date_from' => '2025-03-01',
            'date_to' => '2025-03-31',
        ]))
            ->assertOk()
            ->assertSee('Posto Central')
            ->assertDontSee('Posto Avenida')
            ->assertDontSee('Companhia Aérea');
    }
}
