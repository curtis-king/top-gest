<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->string('statut_certification')->default('non_certifiee')->after('objet');
            $table->string('mode_paiement')->nullable()->after('statut_certification');
            $table->string('certification_number')->nullable()->after('mode_paiement');
            $table->text('certification_signature')->nullable()->after('certification_number');
            $table->string('certification_short_signature')->nullable()->after('certification_signature');
            $table->longText('certification_qr_code')->nullable()->after('certification_short_signature');
            $table->timestamp('certification_date')->nullable()->after('certification_qr_code');
            $table->string('sfec_identifier')->nullable()->after('certification_date');
            $table->text('certification_error')->nullable()->after('sfec_identifier');
        });
    }

    public function down(): void
    {
        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn([
                'statut_certification',
                'mode_paiement',
                'certification_number',
                'certification_signature',
                'certification_short_signature',
                'certification_qr_code',
                'certification_date',
                'sfec_identifier',
                'certification_error',
            ]);
        });
    }
};
