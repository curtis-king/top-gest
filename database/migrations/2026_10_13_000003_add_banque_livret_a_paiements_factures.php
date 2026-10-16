<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paiements_factures', function (Blueprint $table) {
            $table->foreignId('banque_id')->nullable()->after('mode_paiement')->constrained('banques')->nullOnDelete();
            // Ligne du livret bancaire créée avec le paiement (virement, chèque, carte…).
            $table->foreignId('livret_bancaire_id')->nullable()->after('banque_id')->constrained('livrets_bancaires')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('paiements_factures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('livret_bancaire_id');
            $table->dropConstrainedForeignId('banque_id');
        });
    }
};
