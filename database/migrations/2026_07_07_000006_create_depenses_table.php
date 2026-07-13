<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();
            $table->string('numero_depense')->unique();
            $table->date('date_depense');
            $table->string('objet');
            $table->foreignId('categorie_depense_id')->nullable()->constrained('categories_depenses')->nullOnDelete();
            $table->decimal('montant', 12, 0);
            $table->string('mode_paiement');
            $table->foreignId('banque_id')->nullable()->constrained('banques')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('statut')->default('en_attente');
            $table->foreignId('agence_id')->nullable()->constrained('agences')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
