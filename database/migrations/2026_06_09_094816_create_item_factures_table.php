<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_factures', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 12, 0);
            $table->foreignId('facture_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_factures');
    }
};
