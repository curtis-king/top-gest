<?php

namespace App\Models;

use App\Enums\TypeFacture;
use App\Enums\StatutFacture;
use App\Enums\StatutCertification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    protected $fillable = [
        'type_facture',
        'numero_facture',
        'date_facture',
        'statut_facture',
        'raison_social',
        'contact_id',
        'agence_id',
        'objet',
        'statut_certification',
        'mode_paiement',
        'certification_number',
        'certification_signature',
        'certification_short_signature',
        'certification_qr_code',
        'certification_date',
        'sfec_identifier',
        'certification_error',
    ];

    protected function casts(): array
    {
        return [
            'date_facture' => 'date',
            'type_facture' => TypeFacture::class,
            'statut_facture' => StatutFacture::class,
            'statut_certification' => StatutCertification::class,
            'certification_date' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemFacture::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantite * $i->prix_unitaire);
    }
}
