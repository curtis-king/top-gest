<?php

namespace App\Models;

use App\Enums\StatusDossier;
use App\Enums\TypeContrat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DossierEmployee extends Model
{
    protected $table = 'dossiers_employees';

    protected $fillable = [
        'date_engagement',
        'date_fin',
        'type_contrat',
        'status',
        'employee_id',
    ];

    protected function casts(): array
    {
        return [
            'date_engagement' => 'date',
            'date_fin' => 'date',
            'type_contrat' => TypeContrat::class,
            'status' => StatusDossier::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
