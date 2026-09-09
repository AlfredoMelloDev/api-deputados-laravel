<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->index();
            $table->string('status', 20)->default('processing')->index();
            $table->unsignedInteger('total_deputies')->default(0);
            $table->unsignedInteger('processed_deputies')->default(0);
            $table->unsignedInteger('successful_jobs')->default(0);
            $table->unsignedInteger('failed_jobs')->default(0);
            $table->unsignedBigInteger('expenses_received')->default(0);
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
    }
};
