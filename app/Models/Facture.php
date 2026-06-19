<?php

namespace App\Models;

use App\Enums\TypeFacture;
use App\Enums\StatutFacture;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    protected $fillable = [
        'type_facture',
        'numero_facture',
        'date_facture',
        'statut_facture',
        'raison_social',
        'contact_id',
        'agence_id',
        'objet',
    ];

    protected function casts(): array
    {
        return [
            'date_facture' => 'date',
            'type_facture' => TypeFacture::class,
            'statut_facture' => StatutFacture::class,
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemFacture::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantite * $i->prix_unitaire);
    }
}
