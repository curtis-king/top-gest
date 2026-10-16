<?php

namespace App\Models;

use App\Models\Concerns\CloisonneParAgence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemFacture extends Model
{
    use CloisonneParAgence;

    protected string $agenceParent = 'facture';

    protected array $relationsCloisonnees = ['depot'];

    protected $table = 'item_factures';

    protected $fillable = [
        'description',
        'quantite',
        'prix_unitaire',
        'taux_tva',
        'type_article',
        'produit_id',
        'depot_id',
        'facture_id',
    ];

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function getSousTotalAttribute(): float
    {
        return $this->quantite * $this->prix_unitaire;
    }
}
