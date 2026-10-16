<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mouvements_stocks', function (Blueprint $table) {
            $table->foreignId('item_facture_id')->nullable()->after('facture_id')
                ->constrained('item_factures')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mouvements_stocks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('item_facture_id');
        });
    }
};
