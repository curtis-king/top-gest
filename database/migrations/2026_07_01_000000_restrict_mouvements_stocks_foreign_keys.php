<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mouvements_stocks', function (Blueprint $table) {
            $table->dropForeign(['produit_id']);
            $table->dropForeign(['depot_id']);

            $table->foreign('produit_id')->references('id')->on('produits')->restrictOnDelete();
            $table->foreign('depot_id')->references('id')->on('depots')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mouvements_stocks', function (Blueprint $table) {
            $table->dropForeign(['produit_id']);
            $table->dropForeign(['depot_id']);

            $table->foreign('produit_id')->references('id')->on('produits')->cascadeOnDelete();
            $table->foreign('depot_id')->references('id')->on('depots')->cascadeOnDelete();
        });
    }
};
