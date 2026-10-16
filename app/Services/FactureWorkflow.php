<?php

namespace App\Services;

use App\Enums\StatutFacture;
use App\Enums\TypeActionLivret;
use App\Enums\TypeFacture;
use App\Models\Facture;
use App\Models\HistoriqueFacture;
use App\Models\LivretBancaire;
use App\Models\PaiementFacture;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Cycle de vie d'une facture :
 *
 *   brouillon ──valider──► impayée ⇄ partielle ⇄ payée   (selon les paiements enregistrés)
 *       ▲  │                   │
 *       │  └──annuler──► annulée ◄──annuler── (non certifiée et sans paiement)
 *       └──── rouvrir ─────────┘ / remettre en brouillon
 *
 * Le stock suit : une facture active (impayée/partielle/payée) a ses mouvements appliqués,
 * une facture en brouillon ou annulée n'en a aucun. Chaque événement est historisé.
 */
class FactureWorkflow
{
    public function __construct(private StockMovementService $stock)
    {
    }

    public function changerStatut(Facture $facture, string $cible): void
    {
        $libelle = $facture->transitionsPossibles()[$cible] ?? null;
        if (!$libelle) {
            throw new DomainException($this->motifRefus($facture, $cible));
        }

        DB::transaction(function () use ($facture, $cible, $libelle) {
            $ancien = $facture->statut_facture?->value;
            $etaitActive = $facture->estActive();

            $facture->update(['statut_facture' => $cible]);

            if (!$etaitActive && $facture->estActive()) {
                $this->stock->appliquerPourFacture($facture);
            } elseif ($etaitActive && !$facture->estActive()) {
                $this->stock->annulerPourFacture($facture, $cible === 'annulee' ? 'annulation' : 'retour en brouillon');
            }

            $this->journaliser($facture, 'statut', $ancien, $cible, $libelle);

            $this->recalculerLies($facture);
        });
    }

    public function enregistrerPaiement(Facture $facture, array $donnees): PaiementFacture
    {
        if (!$facture->accepteLesPaiements()) {
            throw new DomainException('Les paiements ne s\'enregistrent que sur une facture validée (hors proforma).');
        }

        $reste = $facture->resteAPayer();
        if ((float) $donnees['montant'] > $reste + 0.01) {
            throw new DomainException('Le montant dépasse le reste à payer (' . number_format($reste, 0, ',', ' ') . ' FCFA).');
        }

        return DB::transaction(function () use ($facture, $donnees) {
            $paiement = $facture->paiements()->create($donnees + ['user_id' => auth()->id()]);

            if ($paiement->banque_id) {
                $paiement->update(['livret_bancaire_id' => $this->creerLigneLivret($facture, $paiement)->id]);
            }

            $this->journaliser($facture, 'paiement', null, null,
                'Paiement de ' . number_format($paiement->montant, 0, ',', ' ') . ' FCFA'
                . ($paiement->mode_paiement ? " ({$paiement->mode_paiement})" : '')
                . ($paiement->reference ? ", réf. {$paiement->reference}" : ''));

            $this->recalculerLies($facture);

            return $paiement;
        });
    }

    public function supprimerPaiement(PaiementFacture $paiement): void
    {
        if ($ecriture = $paiement->ecriture()) {
            throw new DomainException("Ce paiement est comptabilisé (écriture {$ecriture->numero_ecriture}) : supprimez ou contre-passez d'abord l'écriture.");
        }

        DB::transaction(function () use ($paiement) {
            $facture = $paiement->facture;
            $livret = $paiement->livretBancaire;
            $paiement->delete();
            $livret?->delete();

            $this->journaliser($facture, 'paiement', null, null,
                'Suppression du paiement de ' . number_format($paiement->montant, 0, ',', ' ') . ' FCFA du ' . $paiement->date_paiement?->format('d/m/Y'));

            $this->recalculerLies($facture);
        });
    }

    /**
     * Après un paiement : impayée / partielle / payée selon ce qui a été encaissé.
     */
    public function recalculerStatut(Facture $facture): void
    {
        if (!$facture->estActive()) {
            return;
        }

        $facture->load('items');
        $nouveau = $facture->statutSelonPaiements();

        if ($nouveau !== $facture->statut_facture) {
            $ancien = $facture->statut_facture?->value;
            $facture->update(['statut_facture' => $nouveau->value]);
            $this->journaliser($facture, 'statut', $ancien, $nouveau->value, 'Mis à jour selon les paiements');
        }
    }

    /**
     * Une vente et ses avoirs dépendent l'un de l'autre (l'avoir réduit ce qui est dû sur la
     * vente ; ce qui a été payé sur la vente fixe ce que l'avoir doit rembourser).
     */
    public function recalculerLies(Facture $facture): void
    {
        $this->recalculerStatut($facture);

        if ($facture->type_facture === TypeFacture::Avoir && $facture->factureOrigine) {
            $origine = $facture->factureOrigine;
            $this->recalculerStatut($origine);
            $autres = $origine->avoirs()->whereKeyNot($facture->id)->get();
        } else {
            $autres = $facture->avoirs()->get();
        }

        foreach ($autres as $avoir) {
            $this->recalculerStatut($avoir);
        }
    }

    /**
     * Ligne du livret de la banque choisie : dépôt pour un encaissement client,
     * retrait pour un remboursement (avoir) ou un règlement fournisseur (achat).
     */
    private function creerLigneLivret(Facture $facture, PaiementFacture $paiement): LivretBancaire
    {
        $motif = match ($facture->type_facture) {
            TypeFacture::Avoir => "Remboursement avoir {$facture->numero_facture}",
            TypeFacture::Achat => "Règlement facture fournisseur {$facture->numero_facture}",
            default => "Encaissement facture {$facture->numero_facture}",
        };

        return LivretBancaire::create([
            'date_action' => $paiement->date_paiement,
            'type_action' => $facture->type_facture === TypeFacture::Vente ? TypeActionLivret::Depot->value : TypeActionLivret::Retrait->value,
            'motif' => $motif . ($paiement->reference ? " — réf. {$paiement->reference}" : ''),
            'montant' => $paiement->montant,
            'raison_social' => $facture->contact?->raison_social ?: $facture->contact?->nom_complet ?: $facture->raison_social,
            'contact_id' => $facture->contact_id,
            'banque_id' => $paiement->banque_id,
            'agence_id' => $facture->agence_id,
        ]);
    }

    public function journaliser(Facture $facture, string $action, ?string $ancien, ?string $nouveau, ?string $details = null): void
    {
        HistoriqueFacture::create([
            'facture_id' => $facture->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'ancien_statut' => $ancien,
            'nouveau_statut' => $nouveau,
            'details' => $details ? mb_substr($details, 0, 255) : null,
        ]);
    }

    private function motifRefus(Facture $facture, string $cible): string
    {
        return match (true) {
            in_array($cible, ['partielle', 'payee'], true)
                => 'Le statut de paiement se met à jour automatiquement : enregistrez un paiement.',
            $facture->estCertifiee()
                => 'Cette facture est certifiée auprès du SFEC : seule une facture d\'avoir peut l\'annuler ou la corriger.',
            $facture->paiements()->exists()
                => 'Des paiements sont enregistrés sur cette facture : supprimez-les d\'abord.',
            default => 'Ce changement de statut n\'est pas possible depuis « ' . $facture->statut_facture?->value . ' ».',
        };
    }
}
