<?php

namespace App\Models;

use App\Api\SfecPayloadValidator;
use App\Models\Concerns\CloisonneParAgence;
use App\Enums\TypeContact;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use CloisonneParAgence;

    protected $fillable = [
        'raison_social',
        'nom_complet',
        'adresse_email',
        'telephone',
        'type_contact',
        'adresse',
        'secteur_activites',
        'agence_id',
        'niu',
        'rccm',
        'assujetti_tva',
        'type_client_sfec',
    ];

    protected function casts(): array
    {
        return [
            'type_contact' => TypeContact::class,
            'assujetti_tva' => 'boolean',
        ];
    }

    /**
     * Type de client SFEC (clé de config sfec.recipient_types). Sans choix explicite,
     * on le déduit : raison sociale → entreprise, sinon particulier.
     */
    public function typeSfec(): string
    {
        return $this->type_client_sfec ?: ($this->raison_social ? 'entreprise' : 'particulier');
    }

    public function estAssujettiTva(): bool
    {
        return $this->assujetti_tva ?? in_array($this->typeSfec(), ['entreprise', 'etat'], true);
    }

    /**
     * Champs recipient_* envoyés au SFEC (et figés sur la facture à la certification).
     */
    public function donneesSfec(): array
    {
        $typeKey = $this->typeSfec();

        return [
            'recipient_type' => config("sfec.recipient_types.{$typeKey}", config('sfec.recipient_types.particulier')),
            'is_recipient_taxable' => $this->estAssujettiTva(),
            'recipient_name' => $this->raison_social ?: $this->nom_complet,
            'recipient_niu' => $this->niu ?: null,
            'recipient_rccm' => $this->rccm ?: null,
            'recipient_address' => $this->adresse,
            'recipient_phone' => $this->telephone,
            'recipient_email' => $this->adresse_email,
        ];
    }

    /**
     * Ce qui manque pour qu'une facture à ce client puisse être certifiée (vide = prêt).
     *
     * @return list<string>
     */
    public function manquesSfec(): array
    {
        return app(SfecPayloadValidator::class)->erreursDestinataire($this->donneesSfec());
    }

    public function estPretSfec(): bool
    {
        return $this->manquesSfec() === [];
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function livretsBancaires(): HasMany
    {
        return $this->hasMany(LivretBancaire::class);
    }
}
