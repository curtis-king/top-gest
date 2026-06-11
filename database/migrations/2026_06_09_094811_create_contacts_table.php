<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('raison_social')->nullable();
            $table->string('nom_complet')->nullable();
            $table->string('adresse_email');
            $table->string('telephone');
            $table->string('type_contact');
            $table->text('adresse')->nullable();
            $table->string('secteur_activites')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
