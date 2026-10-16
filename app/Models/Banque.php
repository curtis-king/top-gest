<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banque extends Model
{
    protected $fillable = [
        'nom',
        'numero_compte',
        'compte_comptable_id',
    ];

    public function livretsBancaires(): HasMany
    {
        return $this->hasMany(LivretBancaire::class);
    }

    public function compteComptable(): BelongsTo
    {
        return $this->belongsTo(CompteComptable::class);
    }
}
