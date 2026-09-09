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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deputy_id')->constrained()->cascadeOnDelete();
            $table->char('external_key', 64);
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedTinyInteger('month')->index();
            $table->string('expense_type')->index();
            $table->unsignedBigInteger('document_code')->nullable()->index();
            $table->string('document_type')->nullable();
            $table->unsignedInteger('document_type_code')->nullable();
            $table->date('document_date')->nullable()->index();
            $table->string('document_number')->nullable();
            $table->decimal('document_value', 15, 2)->default(0);
            $table->decimal('net_value', 15, 2)->default(0);
            $table->decimal('disallowance_value', 15, 2)->default(0);
            $table->string('supplier_name');
            $table->string('supplier_tax_id', 20)->nullable()->index();
            $table->text('document_url')->nullable();
            $table->string('reimbursement_number')->nullable();
            $table->unsignedBigInteger('batch_code')->nullable();
            $table->unsignedInteger('installment')->nullable();
            $table->timestamps();

            $table->unique(['deputy_id', 'external_key']);
            $table->index(['deputy_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
