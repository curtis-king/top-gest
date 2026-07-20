<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemFacture extends Model
{
    protected $table = 'item_factures';

    protected $fillable = [
        'description',
        'quantite',
        'prix_unitaire',
        'taux_tva',
        'type_article',
        'facture_id',
    ];

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function getSousTotalAttribute(): float
    {
        return $this->quantite * $this->prix_unitaire;
    }
}
