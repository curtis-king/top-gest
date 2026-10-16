<?php

namespace App\Models;

use App\Models\Concerns\CloisonneParAgence;
use App\Enums\TypeFacture;
use App\Enums\StatutFacture;
use App\Enums\StatutCertification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    use CloisonneParAgence;

    protected array $relationsCloisonnees = ['contact', 'factureOrigine'];

    protected $fillable = [
        'type_facture',
        'numero_facture',
        'date_facture',
        'statut_facture',
        'raison_social',
        'interlocuteur',
        'contact_id',
        'facture_origine_id',
        'agence_id',
        'objet',
        'statut_certification',
        'mode_paiement',
        'tva_active',
        'centimes_additionnels_actif',
        'certification_number',
        'certification_signature',
        'certification_short_signature',
        'certification_qr_code',
        'certification_date',
        'sfec_identifier',
        'client_certifie',
        'certification_error',
    ];

    protected function casts(): array
    {
        return [
            'date_facture' => 'date',
            'type_facture' => TypeFacture::class,
            'statut_facture' => StatutFacture::class,
            'statut_certification' => StatutCertification::class,
            'certification_date' => 'datetime',
            'client_certifie' => 'array',
            'tva_active' => 'boolean',
            'centimes_additionnels_actif' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemFacture::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function factureOrigine(): BelongsTo
    {
        return $this->belongsTo(Facture::class, 'facture_origine_id');
    }

    public function avoirs(): HasMany
    {
        return $this->hasMany(Facture::class, 'facture_origine_id');
    }

    /**
     * Une facture certifiée par le SFEC est figée : son contenu est couvert par la signature.
     * Seule une facture d'avoir peut la corriger ou l'annuler.
     */
    public function estCertifiee(): bool
    {
        return $this->statut_certification === StatutCertification::Certifiee;
    }

    /** Statuts d'une facture émise : elle compte (stock, chiffre d'affaires, paiements). */
    public const STATUTS_ACTIFS = ['impayee', 'partielle', 'payee'];

    public function paiements(): HasMany
    {
        return $this->hasMany(PaiementFacture::class)->orderBy('date_paiement')->orderBy('id');
    }

    public function historique(): HasMany
    {
        return $this->hasMany(HistoriqueFacture::class)->orderByDesc('id');
    }

    public function estActive(): bool
    {
        return in_array($this->statut_facture?->value, self::STATUTS_ACTIFS, true);
    }

    public function montantPaye(): float
    {
        return round((float) $this->paiements()->sum('montant'), 2);
    }

    /**
     * TTC des avoirs validés émis sur cette facture de vente.
     */
    public function montantAvoirsValides(): float
    {
        return round($this->avoirs()
            ->where('type_facture', TypeFacture::Avoir->value)
            ->whereIn('statut_facture', self::STATUTS_ACTIFS)
            ->with('items')->get()
            ->sum(fn (Facture $a) => $a->total_ttc), 2);
    }

    /**
     * Ce qui doit être réglé au total :
     *  - vente : TTC diminué des avoirs validés (l'avoir s'impute d'abord sur ce qui reste dû) ;
     *  - avoir : seulement le trop-perçu à rembourser au client, s'il avait déjà payé ;
     *  - achat : TTC.
     */
    public function montantDu(): float
    {
        if ($this->type_facture === TypeFacture::Avoir) {
            $origine = $this->factureOrigine;
            if (!$origine) {
                return 0.0;
            }
            $dejaPaye = $origine->montantPaye();
            $resteDuApresAvoirs = $origine->total_ttc - $origine->montantAvoirsValides();

            return max(0.0, round($dejaPaye - $resteDuApresAvoirs, 2));
        }

        if ($this->type_facture === TypeFacture::Vente) {
            return max(0.0, round($this->total_ttc - $this->montantAvoirsValides(), 2));
        }

        return round($this->total_ttc, 2);
    }

    public function resteAPayer(): float
    {
        return max(0.0, round($this->montantDu() - $this->montantPaye(), 2));
    }

    /**
     * Statut de paiement déduit des règlements (et des avoirs pour une vente).
     */
    public function statutSelonPaiements(): StatutFacture
    {
        return match (true) {
            $this->resteAPayer() <= 0.01 => StatutFacture::Payee,
            $this->montantPaye() > 0 => StatutFacture::Partielle,
            default => StatutFacture::Impayee,
        };
    }

    public function accepteLesPaiements(): bool
    {
        return $this->estActive() && $this->type_facture !== TypeFacture::Proforma;
    }

    /**
     * Changements de statut manuels possibles, avec leur libellé. Les statuts de paiement
     * (impayée / partielle / payée) découlent des encaissements, pas d'un choix manuel.
     *
     * @return array<string, string> statut cible => libellé de l'action
     */
    public function transitionsPossibles(): array
    {
        $sansPaiement = !$this->paiements()->exists();
        $modifiable = !$this->estCertifiee() && $sansPaiement;

        return match ($this->statut_facture) {
            StatutFacture::Brouillon => array_filter([
                'impayee' => 'Valider la facture',
                'annulee' => !$this->estCertifiee() ? 'Annuler' : null,
            ]),
            StatutFacture::Annulee => !$this->estCertifiee() ? ['brouillon' => 'Rouvrir en brouillon'] : [],
            default => $modifiable ? [
                'brouillon' => 'Remettre en brouillon',
                'annulee' => 'Annuler la facture',
            ] : [],
        };
    }

    /**
     * Numérotation unique toutes agences confondues (FAC-00001, FAC-00002…).
     */
    public static function prochainNumero(): string
    {
        $last = static::withoutGlobalScope('agence')->orderBy('id', 'desc')->first();
        $num = $last ? intval(substr($last->numero_facture, 4)) + 1 : 1;

        return 'FAC-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Pourquoi on ne peut pas émettre d'avoir sur cette facture (null = possible).
     */
    public function motifRefusAvoir(): ?string
    {
        return match (true) {
            $this->type_facture !== TypeFacture::Vente => 'Un avoir ne peut porter que sur une facture de vente.',
            // Une facture certifiée est figée : seul l'avoir peut la corriger, quel que soit son statut.
            $this->statut_facture === StatutFacture::Brouillon && !$this->estCertifiee()
                => 'Cette facture est encore en brouillon : modifiez-la directement plutôt que de faire un avoir.',
            $this->statut_facture === StatutFacture::Annulee => 'Cette facture est annulée.',
            $this->avoirEmis() !== null => 'Cette facture possède déjà un avoir (' . $this->avoirEmis()->numero_facture . ') : le SFEC n\'en accepte qu\'un.',
            default => null,
        };
    }

    /**
     * Un avoir ne peut pas rembourser plus que la facture qu'il corrige (TTC).
     */
    public function depasseFactureOrigine(): bool
    {
        if ($this->type_facture !== TypeFacture::Avoir || !$this->factureOrigine) {
            return false;
        }

        $this->load('items');

        return round($this->total_ttc, 2) > round($this->factureOrigine->total_ttc, 2);
    }

    /**
     * Quantité d'un produit encore retournable sur cet avoir : vendue sur la facture d'origine,
     * moins ce qui figure déjà sur l'avoir.
     */
    public function quantiteRetournable(int $produitId): int
    {
        $vendue = (int) $this->factureOrigine?->items()->where('produit_id', $produitId)->sum('quantite');
        $dejaRetournee = (int) $this->items()->where('produit_id', $produitId)->sum('quantite');

        return max(0, $vendue - $dejaRetournee);
    }

    /**
     * Pour une facture de vente : l'avoir émis sur elle (le SFEC n'en autorise qu'un).
     */
    public function avoirEmis(): ?Facture
    {
        return $this->avoirs()->where('type_facture', TypeFacture::Avoir->value)->with('items')->first();
    }

    public function estAnnuleeParAvoir(): bool
    {
        $avoir = $this->avoirEmis();

        return $avoir && round($avoir->total_ttc, 2) >= round($this->total_ttc, 2);
    }

    /**
     * Montant signé pour les cumuls : un avoir vient en déduction.
     */
    public function getMontantSigneAttribute(): float
    {
        return $this->type_facture === TypeFacture::Avoir ? -$this->total : $this->total;
    }

    public function getTotalAttribute(): float
    {
        return $this->items->sum(fn($i) => $i->quantite * $i->prix_unitaire);
    }

    public function getMontantTvaAttribute(): float
    {
        return (float) $this->items->sum(fn($i) => $i->quantite * $i->prix_unitaire * ((float) $i->taux_tva) / 100);
    }

    public function getMontantCentimesAdditionnelsAttribute(): float
    {
        return $this->centimes_additionnels_actif ? round($this->montant_tva * 0.05) : 0.0;
    }

    public function getTotalTtcAttribute(): float
    {
        return $this->total + $this->montant_tva + $this->montant_centimes_additionnels;
    }
}
