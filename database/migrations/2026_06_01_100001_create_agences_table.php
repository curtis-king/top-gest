<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agences', function (Blueprint $table) {
            $table->id();
            $table->string('name_agence');
            $table->string('adresse')->nullable();
            $table->string('numero_telephone')->nullable();
            $table->string('adresse_email')->nullable();
            $table->string('ville')->nullable();
            $table->foreignId('compagnie_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agences');
    }
};
