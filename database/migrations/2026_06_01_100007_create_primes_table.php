<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('primes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('motif');
            $table->decimal('montant', 12, 2)->default(0);
            $table->integer('mois');
            $table->integer('annee');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('primes');
    }
};
