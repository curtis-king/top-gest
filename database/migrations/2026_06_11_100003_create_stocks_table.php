<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('quantite')->default(0);
            $table->foreignId('produit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depot_id')->constrained()->cascadeOnDelete();
            $table->unique(['produit_id', 'depot_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
