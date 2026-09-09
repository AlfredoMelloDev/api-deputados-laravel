<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'year', 'status', 'total_deputies', 'processed_deputies', 'successful_jobs',
    'failed_jobs', 'expenses_received', 'started_at', 'finished_at',
])]
class SyncRun extends Model
{
    public function recordSuccess(int $expensesReceived): void
    {
        $this->recordResult(true, $expensesReceived);
    }

    public function recordFailure(): void
    {
        $this->recordResult(false, 0);
    }

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    private function recordResult(bool $successful, int $expensesReceived): void
    {
        DB::transaction(function () use ($successful, $expensesReceived): void {
            $run = self::query()->lockForUpdate()->find($this->id);

            if ($run === null || $run->status !== 'processing') {
                return;
            }

            $run->processed_deputies++;
            $successful ? $run->successful_jobs++ : $run->failed_jobs++;
            $run->expenses_received += $expensesReceived;

            if ($run->processed_deputies >= $run->total_deputies) {
                $run->status = $run->failed_jobs > 0 ? 'completed_with_errors' : 'completed';
                $run->finished_at = now();
            }

            $run->save();
        });
    }
}
