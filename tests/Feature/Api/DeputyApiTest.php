<?php

namespace Tests\Feature\Api;

use App\Models\Deputy;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeputyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_and_filters_deputies_with_pagination(): void
    {
        $selected = Deputy::factory()->create([
            'camara_id' => 123456,
            'name' => 'Ana Souza',
            'party_acronym' => 'PT',
            'state_acronym' => 'SP',
        ]);
        Deputy::factory()->create(['name' => 'Carlos Lima', 'party_acronym' => 'PL', 'state_acronym' => 'RJ']);
        Expense::factory()->for($selected)->create(['year' => 2025, 'net_value' => 250.75]);
        Expense::factory()->for($selected)->create(['year' => 2024, 'net_value' => 100]);

        $this->getJson('/api/v1/deputados?nome=Ana&partido=pt&uf=sp&ano_despesas=2025&por_pagina=10')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 123456)
            ->assertJsonPath('data.0.nome', 'Ana Souza')
            ->assertJsonPath('data.0.despesas.quantidade', 1)
            ->assertJsonPath('data.0.despesas.valor_liquido', 250.75)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_it_returns_a_deputy_by_the_official_camara_id(): void
    {
        $deputy = Deputy::factory()->create(['camara_id' => 654321, 'name' => 'Deputado Oficial']);
        Expense::factory()->for($deputy)->count(2)->create(['net_value' => 50]);

        $this->getJson('/api/v1/deputados/654321')
            ->assertOk()
            ->assertJsonPath('data.id', 654321)
            ->assertJsonPath('data.nome', 'Deputado Oficial')
            ->assertJsonPath('data.despesas.quantidade', 2)
            ->assertJsonPath('data.despesas.valor_liquido', 100);

        $this->getJson('/api/v1/deputados/999999')->assertNotFound();
    }

    public function test_it_filters_a_deputys_expenses(): void
    {
        $deputy = Deputy::factory()->create(['camara_id' => 111222]);
        Expense::factory()->for($deputy)->create([
            'year' => 2025,
            'month' => 3,
            'supplier_name' => 'Posto Central',
            'expense_type' => 'COMBUSTÍVEIS',
            'document_date' => '2025-03-15',
            'net_value' => 180.50,
        ]);
        Expense::factory()->for($deputy)->create(['year' => 2024, 'supplier_name' => 'Outro Fornecedor']);

        $this->getJson('/api/v1/deputados/111222/despesas?ano=2025&mes=3&tipo=COMBUSTÍVEIS&fornecedor=Central&data_inicial=2025-03-01&data_final=2025-03-31')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.fornecedor.nome', 'Posto Central')
            ->assertJsonPath('data.0.valores.liquido', 180.50)
            ->assertJsonPath('meta.total', 1);
    }

    public function test_it_validates_api_filters_as_json(): void
    {
        $this->getJson('/api/v1/deputados?uf=INVALIDA&por_pagina=500')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['uf', 'por_pagina']);
    }
}
