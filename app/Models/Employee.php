<?php

namespace App\Models;

use App\Enums\StatusMatrimonial;
use App\Enums\TypePiece;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $fillable = [
        'nom_complet',
        'adresse',
        'telephone',
        'email',
        'type_piece',
        'numero_piece',
        'status_matrimonial',
        'agence_id',
        'fonction_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type_piece' => TypePiece::class,
            'status_matrimonial' => StatusMatrimonial::class,
        ];
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function fonction(): BelongsTo
    {
        return $this->belongsTo(Fonction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dossier(): HasOne
    {
        return $this->hasOne(DossierEmployee::class);
    }

    public function conges(): HasMany
    {
        return $this->hasMany(Conge::class);
    }

    public function retenus(): HasMany
    {
        return $this->hasMany(Retenu::class);
    }

    public function primes(): HasMany
    {
        return $this->hasMany(Prime::class);
    }

    public function payements(): HasMany
    {
        return $this->hasMany(PayementEmployee::class);
    }

    public function affectationsTaches(): HasMany
    {
        return $this->hasMany(AffectationTache::class);
    }
}
