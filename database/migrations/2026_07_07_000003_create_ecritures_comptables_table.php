<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ecritures_comptables', function (Blueprint $table) {
            $table->id();
            $table->string('numero_ecriture')->unique();
            $table->foreignId('journal_comptable_id')->constrained('journaux_comptables')->restrictOnDelete();
            $table->date('date_ecriture');
            $table->string('libelle');
            $table->string('reference')->nullable();
            $table->string('statut')->default('brouillon');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('agence_id')->nullable()->constrained('agences')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecritures_comptables');
    }
};
