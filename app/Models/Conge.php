<?php

namespace App\Models;

use App\Enums\TypeConge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conge extends Model
{
    protected $fillable = [
        'date_debut',
        'date_fin',
        'type_conge',
        'employee_id',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'type_conge' => TypeConge::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
