<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->boolean('tva_active')->default(true)->after('mode_paiement');
            $table->boolean('centimes_additionnels_actif')->default(false)->after('tva_active');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['tva_active', 'centimes_additionnels_actif']);
        });
    }
};
