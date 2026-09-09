<?php

namespace Database\Factories;

use App\Models\Deputy;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deputy_id' => Deputy::factory(),
            'external_key' => hash('sha256', fake()->uuid()),
            'year' => now()->year,
            'month' => fake()->numberBetween(1, 12),
            'expense_type' => fake()->randomElement([
                'COMBUSTÍVEIS E LUBRIFICANTES',
                'MANUTENÇÃO DE ESCRITÓRIO',
                'PASSAGEM AÉREA',
            ]),
            'document_code' => fake()->unique()->numberBetween(1000000, 9999999),
            'document_type' => 'Nota Fiscal',
            'document_type_code' => 0,
            'document_date' => fake()->dateTimeBetween('-1 year'),
            'document_number' => fake()->numerify('########'),
            'document_value' => fake()->randomFloat(2, 10, 5000),
            'net_value' => fake()->randomFloat(2, 10, 5000),
            'disallowance_value' => 0,
            'supplier_name' => fake()->company(),
            'supplier_tax_id' => fake()->numerify('##############'),
            'document_url' => fake()->url(),
            'batch_code' => fake()->numberBetween(100000, 999999),
            'installment' => 0,
        ];
    }
}
