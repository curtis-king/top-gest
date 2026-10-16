<?php

namespace App\Models;

use App\Models\Concerns\CloisonneParAgence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Retenu extends Model
{
    use CloisonneParAgence;

    protected string $agenceParent = 'employee';

    protected $fillable = [
        'date_retenu',
        'motif',
        'montant',
        'employee_id',
    ];

    protected function casts(): array
    {
        return [
            'date_retenu' => 'date',
            'montant' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
