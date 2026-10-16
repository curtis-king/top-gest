<?php

namespace App\Models;

use App\Enums\StatusProject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'nom_project',
        'description',
        'date_echeance',
        'status_project',
    ];

    protected function casts(): array
    {
        return [
            'date_echeance' => 'date',
            'status_project' => StatusProject::class,
        ];
    }

    public function taches(): HasMany
    {
        return $this->hasMany(TacheProject::class);
    }
}
