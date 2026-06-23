<?php

namespace App\Models;

use App\Enums\TypeMouvement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementStock extends Model
{
    protected $table = 'mouvements_stocks';

    protected $fillable = [
        'type_mouvement',
        'quantite',
        'date_mouvement',
        'motif',
        'produit_id',
        'depot_id',
        'depot_destination_id',
        'contact_id',
        'facture_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type_mouvement' => TypeMouvement::class,
            'date_mouvement' => 'date',
        ];
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(Produit::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function depotDestination(): BelongsTo
    {
        return $this->belongsTo(Depot::class, 'depot_destination_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
