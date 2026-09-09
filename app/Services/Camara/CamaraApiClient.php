<?php

namespace App\Services\Camara;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use UnexpectedValueException;

class CamaraApiClient
{
    /** @return list<array<string, mixed>> */
    public function deputies(int $itemsPerPage = 100): array
    {
        return $this->paginate('/deputados', [
            'itens' => $this->validItemsPerPage($itemsPerPage),
            'ordem' => 'ASC',
            'ordenarPor' => 'nome',
        ]);
    }

    /** @return list<array<string, mixed>> */
    public function expenses(int $deputyId, ?int $year = null, int $itemsPerPage = 100): array
    {
        if ($deputyId <= 0) {
            throw new InvalidArgumentException('O identificador do deputado deve ser positivo.');
        }

        $query = [
            'itens' => $this->validItemsPerPage($itemsPerPage),
            'ordem' => 'ASC',
            'ordenarPor' => 'dataDocumento',
        ];

        if ($year !== null) {
            $query['ano'] = $year;
        }

        return $this->paginate("/deputados/{$deputyId}/despesas", $query);
    }

    /**
     * @param  array<string, int|string>  $query
     * @return list<array<string, mixed>>
     */
    private function paginate(string $endpoint, array $query): array
    {
        $records = [];
        $url = $endpoint;
        $visitedUrls = [];

        while ($url !== null) {
            if (isset($visitedUrls[$url])) {
                throw new UnexpectedValueException('A API da Câmara retornou uma paginação circular.');
            }

            $visitedUrls[$url] = true;
            $payload = $this->request()->get($url, $query)->throw()->json();

            if (! is_array($payload) || ! isset($payload['dados']) || ! is_array($payload['dados'])) {
                throw new UnexpectedValueException('A API da Câmara retornou uma resposta inválida.');
            }

            foreach ($payload['dados'] as $record) {
                if (is_array($record)) {
                    $records[] = $record;
                }
            }

            $url = $this->nextPageUrl($payload['links'] ?? []);
            $query = [];
        }

        return $records;
    }

    private function request(): PendingRequest
    {
        $baseUrl = rtrim((string) config('services.camara.base_url'), '/');
        $timeout = (int) config('services.camara.timeout', 15);

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->timeout($timeout)
            ->retry(3, 250);
    }

    private function nextPageUrl(mixed $links): ?string
    {
        if (! is_array($links)) {
            return null;
        }

        foreach ($links as $link) {
            if (is_array($link) && ($link['rel'] ?? null) === 'next') {
                return isset($link['href']) && is_string($link['href']) ? $link['href'] : null;
            }
        }

        return null;
    }

    private function validItemsPerPage(int $itemsPerPage): int
    {
        if ($itemsPerPage < 1 || $itemsPerPage > 100) {
            throw new InvalidArgumentException('A quantidade por página deve estar entre 1 e 100.');
        }

        return $itemsPerPage;
    }
}
