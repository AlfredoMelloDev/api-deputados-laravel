<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'year', 'status', 'total_deputies', 'processed_deputies', 'successful_jobs',
    'failed_jobs', 'expenses_received', 'last_error', 'started_at', 'finished_at',
])]
class SyncRun extends Model
{
    public function recordSuccess(int $expensesReceived): void
    {
        $this->recordResult(true, $expensesReceived);
    }

    public function recordFailure(?string $error = null): void
    {
        $this->recordResult(false, 0, $error);
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    private function recordResult(bool $successful, int $expensesReceived, ?string $error = null): void
    {
        DB::transaction(function () use ($successful, $expensesReceived, $error): void {
            $run = self::query()->lockForUpdate()->find($this->id);

            if ($run === null || $run->status !== 'processing') {
                return;
            }

            $run->processed_deputies++;
            $successful ? $run->successful_jobs++ : $run->failed_jobs++;
            $run->expenses_received += $expensesReceived;

            if ($error !== null) {
                $run->last_error = mb_substr($error, 0, 2000);
            }

            if ($run->processed_deputies >= $run->total_deputies) {
                $run->status = $run->failed_jobs > 0 ? 'completed_with_errors' : 'completed';
                $run->finished_at = now();
            }

            $run->save();
        });
    }
}
