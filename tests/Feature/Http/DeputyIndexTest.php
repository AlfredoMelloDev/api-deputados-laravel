<?php

namespace Tests\Feature\Http;

use App\Models\Deputy;
use App\Models\Expense;
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
        $deputy = Deputy::factory()->create();
        Expense::factory()->for($deputy)->create(['year' => 2025, 'expense_type' => 'PASSAGENS', 'net_value' => 1200.50]);
        Expense::factory()->for($deputy)->create(['year' => 2024, 'expense_type' => 'COMBUSTÍVEIS', 'net_value' => 300]);

        $this->get('/?expense_year=2025')
            ->assertOk()
            ->assertSee('Visão geral de 2025')
            ->assertSee('R$ 1.200,50')
            ->assertSee('PASSAGENS')
            ->assertDontSee('COMBUSTÍVEIS');
    }
}
