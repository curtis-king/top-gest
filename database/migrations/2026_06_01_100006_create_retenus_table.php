<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retenus', function (Blueprint $table) {
            $table->id();
            $table->date('date_retenu');
            $table->string('motif');
            $table->decimal('montant', 12, 2)->default(0);
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retenus');
    }
};
