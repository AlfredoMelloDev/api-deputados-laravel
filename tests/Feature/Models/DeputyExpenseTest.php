<?php

namespace Tests\Feature\Models;

use App\Models\Deputy;
use App\Models\Expense;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeputyExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_deputy_has_expenses(): void
    {
        $deputy = Deputy::factory()->create();
        $expenses = Expense::factory()->count(2)->for($deputy)->create();

        $this->assertCount(2, $deputy->expenses);
        $this->assertTrue($deputy->expenses->contains($expenses->first()));
    }

    public function test_an_expense_belongs_to_a_deputy_and_casts_values(): void
    {
        $deputy = Deputy::factory()->create();
        $expense = Expense::factory()->for($deputy)->create([
            'document_date' => '2026-08-15',
            'net_value' => 1234.5,
        ]);

        $this->assertTrue($expense->deputy->is($deputy));
        $this->assertSame('2026-08-15', $expense->document_date->toDateString());
        $this->assertSame('1234.50', $expense->net_value);
    }

    public function test_the_same_external_expense_cannot_be_stored_twice_for_a_deputy(): void
    {
        $deputy = Deputy::factory()->create();
        $expense = Expense::factory()->for($deputy)->create();

        $this->expectException(UniqueConstraintViolationException::class);

        Expense::factory()->for($deputy)->create([
            'external_key' => $expense->external_key,
        ]);
    }
}
