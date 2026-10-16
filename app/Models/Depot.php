<?php

namespace App\Models;

use App\Models\Concerns\CloisonneParAgence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Depot extends Model
{
    use CloisonneParAgence;

    protected $fillable = ['nom', 'description', 'agence_id'];

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementStock::class);
    }
}
