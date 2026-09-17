<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('document_code', 64)->nullable()->change();
        });

        Schema::table('sync_runs', function (Blueprint $table) {
            $table->string('status', 32)->default('processing')->change();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('document_code')->nullable()->change();
        });

        Schema::table('sync_runs', function (Blueprint $table) {
            $table->string('status', 20)->default('processing')->change();
        });
    }
};
