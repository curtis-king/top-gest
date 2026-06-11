<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agence extends Model
{
    protected $fillable = [
        'name_agence',
        'adresse',
        'numero_telephone',
        'adresse_email',
        'ville',
        'compagnie_id',
    ];

    public function compagnie(): BelongsTo
    {
        return $this->belongsTo(Compagnie::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function tachesProjets(): HasMany
    {
        return $this->hasMany(TacheProject::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function livretsBancaires(): HasMany
    {
        return $this->hasMany(LivretBancaire::class);
    }
}
