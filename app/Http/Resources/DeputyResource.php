<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeputyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->camara_id,
            'nome' => $this->name,
            'partido' => $this->party_acronym,
            'uf' => $this->state_acronym,
            'legislatura' => $this->legislature_id,
            'email' => $this->email,
            'foto' => $this->photo_url,
            'despesas' => [
                'quantidade' => (int) ($this->expenses_count ?? 0),
                'valor_liquido' => (float) ($this->expenses_sum_net_value ?? 0),
            ],
            'sincronizado_em' => $this->expenses_synced_at?->toISOString(),
            'links' => [
                'detalhes' => route('api.v1.deputies.show', $this->camara_id),
                'despesas' => route('api.v1.deputies.expenses.index', $this->camara_id),
            ],
        ];
    }
}
