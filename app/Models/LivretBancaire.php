<?php

namespace App\Models;

use App\Enums\TypeActionLivret;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivretBancaire extends Model
{
    protected $table = 'livrets_bancaires';

    protected $fillable = [
        'date_action',
        'type_action',
        'motif',
        'montant',
        'raison_social',
        'contact_id',
        'banque_id',
        'agence_id',
    ];

    protected function casts(): array
    {
        return [
            'date_action' => 'date',
            'type_action' => TypeActionLivret::class,
        ];
    }

    public function banque(): BelongsTo
    {
        return $this->belongsTo(Banque::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }
}
