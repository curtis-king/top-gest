<?php

namespace App\Models;

use App\Enums\StatusTache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TacheProject extends Model
{
    protected $table = 'taches_projects';

    protected $fillable = [
        'nom_tache',
        'description_tache',
        'cout_tache',
        'status',
        'agence_id',
        'project_id',
    ];

    protected function casts(): array
    {
        return [
            'cout_tache' => 'decimal:0',
            'status' => StatusTache::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function affectations(): HasMany
    {
        return $this->hasMany(AffectationTache::class, 'tache_project_id');
    }
}
