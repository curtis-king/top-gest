<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livrets_bancaires', function (Blueprint $table) {
            $table->id();
            $table->date('date_action');
            $table->string('type_action');
            $table->string('motif');
            $table->decimal('montant', 12, 0);
            $table->string('raison_social')->nullable();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('banque_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agence_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livrets_bancaires');
    }
};
