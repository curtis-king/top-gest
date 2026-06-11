<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouvements_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('type_mouvement');
            $table->integer('quantite');
            $table->date('date_mouvement');
            $table->string('motif')->nullable();
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depot_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depot_destination_id')->nullable()->constrained('depots')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('facture_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouvements_stocks');
    }
};
