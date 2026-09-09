<?php

namespace Tests\Feature\Services;

use App\Services\Camara\CamaraApiClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\TestCase;
use UnexpectedValueException;

class CamaraApiClientTest extends TestCase
{
    public function test_it_fetches_every_page_of_deputies(): void
    {
        Http::fake([
            'dadosabertos.camara.leg.br/api/v2/deputados*' => Http::sequence()
                ->push([
                    'dados' => [['id' => 1, 'nome' => 'Deputada Um']],
                    'links' => [[
                        'rel' => 'next',
                        'href' => 'https://dadosabertos.camara.leg.br/api/v2/deputados?pagina=2&itens=100',
                    ]],
                ])
                ->push([
                    'dados' => [['id' => 2, 'nome' => 'Deputado Dois']],
                    'links' => [],
                ]),
        ]);

        $deputies = app(CamaraApiClient::class)->deputies();

        $this->assertSame([1, 2], array_column($deputies, 'id'));
        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://dadosabertos.camara.leg.br/api/v2/deputados?itens=100&ordem=ASC&ordenarPor=nome'
        );
    }

    public function test_it_fetches_expenses_for_a_deputy_and_year(): void
    {
        Http::fake([
            '*' => Http::response([
                'dados' => [['codDocumento' => 123, 'valorLiquido' => 250.75]],
                'links' => [],
            ]),
        ]);

        $expenses = app(CamaraApiClient::class)->expenses(204554, 2026, 57);

        $this->assertSame(123, $expenses[0]['codDocumento']);
        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://dadosabertos.camara.leg.br/api/v2/deputados/204554/despesas?itens=100&ordem=ASC&ordenarPor=dataDocumento&ano=2026&idLegislatura=57'
        );
    }

    public function test_it_rejects_an_invalid_legislature_id(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(CamaraApiClient::class)->expenses(204554, 2026, 0);
    }

    public function test_it_rejects_an_invalid_page_size(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(CamaraApiClient::class)->deputies(101);
    }

    public function test_it_rejects_a_malformed_api_response(): void
    {
        Http::fake(['*' => Http::response(['links' => []])]);

        $this->expectException(UnexpectedValueException::class);

        app(CamaraApiClient::class)->deputies();
    }
}
