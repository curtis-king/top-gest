<?php

namespace App\Models;

use App\Models\Concerns\CloisonneParAgence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaiementFacture extends Model
{
    use CloisonneParAgence;

    protected string $agenceParent = 'facture';

    protected $table = 'paiements_factures';

    protected $fillable = [
        'facture_id',
        'date_paiement',
        'montant',
        'mode_paiement',
        'banque_id',
        'livret_bancaire_id',
        'reference',
        'note',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_paiement' => 'date',
            'montant' => 'decimal:2',
        ];
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function banque(): BelongsTo
    {
        return $this->belongsTo(Banque::class);
    }

    public function livretBancaire(): BelongsTo
    {
        return $this->belongsTo(LivretBancaire::class);
    }

    /** Moyens de paiement qui passent obligatoirement par une banque. */
    public const MODES_BANCAIRES = ['virement', 'cheque', 'carte'];

    public function estEnEspeces(): bool
    {
        return $this->mode_paiement === 'especes';
    }

    public function ecriture(): ?EcritureComptable
    {
        return EcritureComptable::dejaGenereePour('paiement_facture', $this->id);
    }
}
