<?php

namespace App\Models;

use App\Enums\StatusPayement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayementEmployee extends Model
{
    protected $fillable = [
        'employee_id',
        'mois',
        'annee',
        'salaire_base',
        'total_primes',
        'total_retenus',
        'net_a_payer',
        'status',
        'date_paiement',
    ];

    protected function casts(): array
    {
        return [
            'salaire_base' => 'decimal:2',
            'total_primes' => 'decimal:2',
            'total_retenus' => 'decimal:2',
            'net_a_payer' => 'decimal:2',
            'date_paiement' => 'datetime',
            'status' => StatusPayement::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
