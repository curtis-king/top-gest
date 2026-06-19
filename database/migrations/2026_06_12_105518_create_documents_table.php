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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->string('fichier_path');
            $table->string('type_fichier', 100)->nullable();
            $table->unsignedBigInteger('taille')->nullable();
            $table->date('date_document')->nullable();
            $table->foreignId('categorie_document_id')->nullable()->constrained('categories_documents')->nullOnDelete();
            $table->foreignId('agence_id')->nullable()->constrained('agences')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
