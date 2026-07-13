<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comptes_comptables', function (Blueprint $table) {
            $table->id();
            $table->string('numero_compte')->unique();
            $table->string('libelle');
            $table->unsignedTinyInteger('classe');
            $table->string('type_compte');
            $table->string('sens_normal');
            $table->foreignId('compte_parent_id')->nullable()->constrained('comptes_comptables')->nullOnDelete();
            $table->boolean('is_systeme')->default(false);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes_comptables');
    }
};
