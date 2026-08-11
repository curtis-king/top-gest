<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_factures', function (Blueprint $table) {
            $table->foreignId('produit_id')->nullable()->after('type_article')
                ->constrained('produits')->nullOnDelete();
            $table->foreignId('depot_id')->nullable()->after('produit_id')
                ->constrained('depots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('item_factures', function (Blueprint $table) {
            $table->dropConstrainedForeignId('produit_id');
            $table->dropConstrainedForeignId('depot_id');
        });
    }
};
