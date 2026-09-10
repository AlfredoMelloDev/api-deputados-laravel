<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'ano' => $this->year,
            'mes' => $this->month,
            'tipo' => $this->expense_type,
            'data_documento' => $this->document_date?->toDateString(),
            'numero_documento' => $this->document_number,
            'fornecedor' => [
                'nome' => $this->supplier_name,
                'cnpj_cpf' => $this->supplier_tax_id,
            ],
            'valores' => [
                'documento' => (float) $this->document_value,
                'liquido' => (float) $this->net_value,
                'glosa' => (float) $this->disallowance_value,
            ],
            'url_documento' => $this->document_url,
        ];
    }
}
