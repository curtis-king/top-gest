<?php

namespace App\Models;

use App\Enums\ModePaiementDepense;
use App\Enums\StatutDepense;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Depense extends Model
{
    protected $table = 'depenses';

    protected $fillable = [
        'numero_depense',
        'date_depense',
        'objet',
        'categorie_depense_id',
        'montant',
        'mode_paiement',
        'banque_id',
        'contact_id',
        'statut',
        'agence_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_depense' => 'date',
            'mode_paiement' => ModePaiementDepense::class,
            'statut' => StatutDepense::class,
        ];
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieDepense::class, 'categorie_depense_id');
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

    public function createur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
