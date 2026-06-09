<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dossiers_employees', function (Blueprint $table) {
            $table->id();
            $table->date('date_engagement');
            $table->date('date_fin')->nullable();
            $table->string('type_contrat');
            $table->string('status')->default('actif');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dossiers_employees');
    }
};
