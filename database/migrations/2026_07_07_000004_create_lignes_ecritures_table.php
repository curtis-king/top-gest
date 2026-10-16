<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_ecritures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecriture_comptable_id')->constrained('ecritures_comptables')->cascadeOnDelete();
            $table->foreignId('compte_comptable_id')->constrained('comptes_comptables')->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('libelle')->nullable();
            $table->decimal('debit', 14, 0)->default(0);
            $table->decimal('credit', 14, 0)->default(0);
            $table->unsignedInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_ecritures');
    }
};
