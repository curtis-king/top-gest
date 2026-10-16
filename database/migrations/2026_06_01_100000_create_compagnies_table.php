<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compagnies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slogan')->nullable();
            $table->string('forme_juridique')->nullable();
            $table->string('nui')->nullable();
            $table->string('rccm')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compagnies');
    }
};
