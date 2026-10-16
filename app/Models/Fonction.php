<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fonction extends Model
{
    protected $fillable = [
        'name',
        'description',
        'salaire',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
