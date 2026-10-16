<?php

namespace App\Models;

use App\Models\Concerns\CloisonneParAgence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prime extends Model
{
    use CloisonneParAgence;

    protected string $agenceParent = 'employee';

    protected $fillable = [
        'employee_id',
        'motif',
        'montant',
        'mois',
        'annee',
    ];

    protected function casts(): array
    {
        return [
            'montant' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
