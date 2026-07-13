<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieDepense extends Model
{
    protected $table = 'categories_depenses';

    protected $fillable = [
        'libelle',
        'compte_comptable_id',
    ];

    public function compte(): BelongsTo
    {
        return $this->belongsTo(CompteComptable::class, 'compte_comptable_id');
    }

    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class);
    }
}
