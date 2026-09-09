<?php

namespace Tests\Feature\Http;

use App\Models\Deputy;
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
}
