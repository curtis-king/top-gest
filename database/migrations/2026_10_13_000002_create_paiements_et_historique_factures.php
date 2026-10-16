<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Encaissements (ou décaissements pour un achat / remboursements pour un avoir).
        Schema::create('paiements_factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->cascadeOnDelete();
            $table->date('date_paiement');
            $table->decimal('montant', 15, 2);
            $table->string('mode_paiement')->nullable();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Journal des événements d'une facture (statuts, paiements, certification).
        Schema::create('historique_factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('ancien_statut')->nullable();
            $table->string('nouveau_statut')->nullable();
            $table->string('details')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        // Compte des centimes additionnels, utilisé par les écritures proposées depuis une facture.
        if (Schema::hasTable('comptes_comptables')
            && !DB::table('comptes_comptables')->where('numero_compte', '443400')->exists()) {
            DB::table('comptes_comptables')->insert([
                'numero_compte' => '443400',
                'libelle' => 'État, centimes additionnels',
                'classe' => 4,
                'type_compte' => 'passif',
                'sens_normal' => 'credit',
                'is_systeme' => true,
                'actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_factures');
        Schema::dropIfExists('paiements_factures');
        DB::table('comptes_comptables')->where('numero_compte', '443400')->delete();
    }
};
