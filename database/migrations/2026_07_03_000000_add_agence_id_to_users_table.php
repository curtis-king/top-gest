<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('agence_id')
                  ->nullable()
                  ->after('scope')
                  ->constrained('agences')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Agence::class);
            $table->dropColumn('agence_id');
        });
    }
};
