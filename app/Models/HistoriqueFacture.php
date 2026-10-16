<?php

namespace App\Models;

use App\Models\Concerns\CloisonneParAgence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriqueFacture extends Model
{
    use CloisonneParAgence;

    protected string $agenceParent = 'facture';

    protected $table = 'historique_factures';

    public const UPDATED_AT = null;

    protected $fillable = [
        'facture_id',
        'user_id',
        'action',
        'ancien_statut',
        'nouveau_statut',
        'details',
    ];

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
