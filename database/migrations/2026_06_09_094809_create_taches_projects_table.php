<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('taches_projects', function (Blueprint $table) {
            $table->id();
            $table->string('nom_tache');
            $table->text('description_tache')->nullable();
            $table->decimal('cout_tache', 12, 0)->nullable();
            $table->string('status')->default('a_faire');
            $table->foreignId('agence_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taches_projects');
    }
};
