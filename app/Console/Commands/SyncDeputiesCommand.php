<?php

namespace App\Console\Commands;

use App\Jobs\SyncDeputyExpenses;
use App\Models\Deputy;
use App\Models\SyncRun;
use App\Services\Camara\CamaraApiClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('camara:sync-deputies {--year= : Ano das despesas que serão sincronizadas}')]
#[Description('Sincroniza os deputados da Câmara e enfileira a importação de suas despesas')]
class SyncDeputiesCommand extends Command
{
    public function handle(CamaraApiClient $client): int
    {
        $year = $this->option('year') === null ? now()->year : (int) $this->option('year');

        if ($year < 2008 || $year > now()->year) {
            $this->error('Informe um ano entre 2008 e o ano atual.');

            return self::FAILURE;
        }

        if (SyncRun::query()->where('year', $year)->where('status', 'processing')->exists()) {
            $this->warn("Já existe uma sincronização de {$year} em andamento.");

            return self::SUCCESS;
        }

        $this->info('Buscando deputados na API da Câmara...');
        $deputies = collect($client->deputies())
            ->unique(fn (array $deputy): int => (int) $deputy['id'])
            ->values();

        $syncRun = SyncRun::query()->create([
            'year' => $year,
            'status' => 'processing',
            'total_deputies' => $deputies->count(),
            'started_at' => now(),
        ]);

        $this->withProgressBar($deputies, function (array $data) use ($year, $syncRun): void {
            $deputy = Deputy::query()->updateOrCreate(
                ['camara_id' => $data['id']],
                [
                    'name' => $data['nome'],
                    'party_acronym' => $data['siglaPartido'] ?? null,
                    'state_acronym' => $data['siglaUf'] ?? null,
                    'legislature_id' => $data['idLegislatura'] ?? null,
                    'email' => $data['email'] ?? null,
                    'photo_url' => $data['urlFoto'] ?? null,
                    'api_url' => $data['uri'],
                    'party_api_url' => $data['uriPartido'] ?? null,
                ],
            );

            SyncDeputyExpenses::dispatch($deputy->id, $year, $syncRun->id)->onQueue('expenses');
        });

        $this->newLine(2);
        $this->info($deputies->count().' deputados sincronizados; despesas de '.$year.' adicionadas à fila.');

        return self::SUCCESS;
    }
}
