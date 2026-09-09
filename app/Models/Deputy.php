<?php

namespace App\Models;

use Database\Factories\DeputyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'camara_id',
    'name',
    'party_acronym',
    'state_acronym',
    'legislature_id',
    'email',
    'photo_url',
    'api_url',
    'party_api_url',
    'expenses_synced_at',
])]
class Deputy extends Model
{
    /** @use HasFactory<DeputyFactory> */
    use HasFactory;

    /** @return HasMany<Expense, $this> */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'expenses_synced_at' => 'datetime',
        ];
    }
}
