<?php

namespace App\Models;

use App\Models\Concerns\CloisonneParAgence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use CloisonneParAgence;

    protected string $agenceParent = 'depot';

    protected $fillable = ['quantite', 'produit_id', 'depot_id'];

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }
}
