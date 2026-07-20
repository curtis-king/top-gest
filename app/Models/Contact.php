<?php

namespace App\Models;

use App\Enums\TypeContact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = [
        'raison_social',
        'nom_complet',
        'adresse_email',
        'telephone',
        'type_contact',
        'adresse',
        'secteur_activites',
        'agence_id',
        'niu',
        'type_client_sfec',
    ];

    protected function casts(): array
    {
        return [
            'type_contact' => TypeContact::class,
        ];
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function livretsBancaires(): HasMany
    {
        return $this->hasMany(LivretBancaire::class);
    }
}
