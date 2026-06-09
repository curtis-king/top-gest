<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffectationTache extends Model
{
    protected $table = 'affectation_taches';

    protected $fillable = [
        'date_affectation',
        'nom_complet',
        'employee_id',
        'tache_project_id',
    ];

    protected function casts(): array
    {
        return [
            'date_affectation' => 'date',
        ];
    }

    public function tache(): BelongsTo
    {
        return $this->belongsTo(TacheProject::class, 'tache_project_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
