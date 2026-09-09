<?php

namespace App\Models;

use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'deputy_id',
    'external_key',
    'year',
    'month',
    'expense_type',
    'document_code',
    'document_type',
    'document_type_code',
    'document_date',
    'document_number',
    'document_value',
    'net_value',
    'disallowance_value',
    'supplier_name',
    'supplier_tax_id',
    'document_url',
    'reimbursement_number',
    'batch_code',
    'installment',
])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    /** @return BelongsTo<Deputy, $this> */
    public function deputy(): BelongsTo
    {
        return $this->belongsTo(Deputy::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'document_value' => 'decimal:2',
            'net_value' => 'decimal:2',
            'disallowance_value' => 'decimal:2',
        ];
    }
}
