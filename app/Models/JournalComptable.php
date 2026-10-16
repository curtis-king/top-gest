<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalComptable extends Model
{
    protected $table = 'journaux_comptables';

    protected $fillable = [
        'code',
        'libelle',
    ];

    public function ecritures(): HasMany
    {
        return $this->hasMany(EcritureComptable::class);
    }
}
