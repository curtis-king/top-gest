<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneEcriture extends Model
{
    protected $table = 'lignes_ecritures';

    protected $fillable = [
        'ecriture_comptable_id',
        'compte_comptable_id',
        'contact_id',
        'libelle',
        'debit',
        'credit',
        'ordre',
    ];

    public function ecriture(): BelongsTo
    {
        return $this->belongsTo(EcritureComptable::class, 'ecriture_comptable_id');
    }

    public function compte(): BelongsTo
    {
        return $this->belongsTo(CompteComptable::class, 'compte_comptable_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }
}
