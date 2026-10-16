<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banques', function (Blueprint $table) {
            $table->foreignId('compte_comptable_id')->nullable()->after('numero_compte')
                ->constrained('comptes_comptables')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('banques', function (Blueprint $table) {
            $table->dropConstrainedForeignId('compte_comptable_id');
        });
    }
};
