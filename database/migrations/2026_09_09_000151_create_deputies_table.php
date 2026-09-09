<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deputies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('camara_id')->unique();
            $table->string('name');
            $table->string('party_acronym', 20)->nullable()->index();
            $table->char('state_acronym', 2)->nullable()->index();
            $table->unsignedInteger('legislature_id')->nullable()->index();
            $table->string('email')->nullable();
            $table->text('photo_url')->nullable();
            $table->text('api_url');
            $table->text('party_api_url')->nullable();
            $table->timestamp('expenses_synced_at')->nullable();
            $table->timestamps();

            $table->index(['state_acronym', 'party_acronym']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deputies');
    }
};
