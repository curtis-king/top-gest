<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compagnie extends Model
{
    protected $fillable = [
        'name',
        'slogan',
        'forme_juridique',
        'nui',
        'rccm',
        'logo',
    ];

    public function agences(): HasMany
    {
        return $this->hasMany(Agence::class);
    }
}
