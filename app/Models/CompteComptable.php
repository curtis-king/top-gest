<?php

namespace App\Models;

use App\Enums\SensCompte;
use App\Enums\TypeCompteComptable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompteComptable extends Model
{
    protected $table = 'comptes_comptables';

    protected $fillable = [
        'numero_compte',
        'libelle',
        'classe',
        'type_compte',
        'sens_normal',
        'compte_parent_id',
        'is_systeme',
        'actif',
    ];

    protected function casts(): array
    {
        return [
            'type_compte' => TypeCompteComptable::class,
            'sens_normal' => SensCompte::class,
            'is_systeme' => 'boolean',
            'actif' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CompteComptable::class, 'compte_parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CompteComptable::class, 'compte_parent_id');
    }

    public function lignesEcritures(): HasMany
    {
        return $this->hasMany(LigneEcriture::class);
    }
}
