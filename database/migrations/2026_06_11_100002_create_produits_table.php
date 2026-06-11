<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('unite_mesure');
            $table->decimal('prix_achat', 15, 2)->default(0);
            $table->decimal('prix_vente', 15, 2)->default(0);
            $table->integer('stock_min')->default(0);
            $table->foreignId('categorie_produit_id')->nullable()->constrained('categories_produits')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
