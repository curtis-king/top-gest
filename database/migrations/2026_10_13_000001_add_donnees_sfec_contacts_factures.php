<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('rccm', 50)->nullable()->after('niu');
            // null = non renseigné : on retombe sur la déduction par type de client.
            $table->boolean('assujetti_tva')->nullable()->after('rccm');
        });

        Schema::table('factures', function (Blueprint $table) {
            // Données client telles qu'envoyées au SFEC lors de la certification (figées).
            $table->json('client_certifie')->nullable()->after('sfec_identifier');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['rccm', 'assujetti_tva']);
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn('client_certifie');
        });
    }
};
