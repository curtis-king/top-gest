<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banque extends Model
{
    protected $fillable = [
        'nom',
        'numero_compte',
    ];

    public function livretsBancaires(): HasMany
    {
        return $this->hasMany(LivretBancaire::class);
    }
}
