<?php

namespace App\Services;

use App\Enums\TypeFacture;
use App\Enums\TypeMouvement;
use App\Models\Facture;
use App\Models\ItemFacture;
use App\Models\MouvementStock;
use App\Models\Stock;

/**
 * Mouvements de stock déclenchés par les articles "produit" des factures.
 *
 * Le sens dépend du type de facture :
 *   vente    → sortie (la marchandise part chez le client)
 *   avoir    → entrée (retour de marchandise annulant tout ou partie d'une vente)
 *   achat    → entrée (réception fournisseur)
 *   proforma → aucun mouvement (simple devis)
 */
class StockMovementService
{
    public function appliquerPourArticle(ItemFacture $item): ?MouvementStock
    {
        if (!$item->produit_id || !$item->depot_id) {
            return null;
        }

        // Déjà appliqué (et non contre-passé) : rien à refaire.
        if ($this->effetNet($item) !== 0) {
            return null;
        }

        $facture = $item->facture;
        $type = self::typeMouvementPour($facture);
        if (!$type) {
            return null;
        }

        $mouvement = MouvementStock::create([
            'type_mouvement' => $type->value,
            'quantite' => $item->quantite,
            'date_mouvement' => now(),
            'motif' => self::motif($facture),
            'produit_id' => $item->produit_id,
            'depot_id' => $item->depot_id,
            'contact_id' => $facture->contact_id,
            'facture_id' => $facture->id,
            'item_facture_id' => $item->id,
            'user_id' => auth()->id(),
        ]);

        $delta = $type === TypeMouvement::Entree ? $item->quantite : -$item->quantite;
        $this->ajusterStock($item->produit_id, $item->depot_id, $delta);

        return $mouvement;
    }

    public function appliquerPourFacture(Facture $facture): void
    {
        $facture->loadMissing('items');

        foreach ($facture->items as $item) {
            $this->appliquerPourArticle($item);
        }
    }

    public function annulerPourFacture(Facture $facture, string $raison): void
    {
        $facture->loadMissing('items');

        foreach ($facture->items as $item) {
            $this->annulerPourArticle($item, $raison);
        }
    }

    /**
     * Contre-passe l'effet d'un article sur le stock (entrée ↔ sortie), s'il en a un.
     * La contre-passation reste rattachée à l'article : l'effet net redevient nul et
     * l'article pourra être réappliqué si la facture est revalidée.
     */
    public function annulerPourArticle(ItemFacture $item, string $raison = 'suppression d\'article'): void
    {
        $net = $this->effetNet($item);
        if ($net === 0) {
            return;
        }

        $original = MouvementStock::where('item_facture_id', $item->id)->orderBy('id')->first();
        $type = $net < 0 ? TypeMouvement::Entree : TypeMouvement::Sortie;

        MouvementStock::create([
            'type_mouvement' => $type->value,
            'quantite' => abs($net),
            'date_mouvement' => now(),
            'motif' => "Contre-passation ({$raison}) — facture {$original->facture?->numero_facture}",
            'produit_id' => $original->produit_id,
            'depot_id' => $original->depot_id,
            'contact_id' => $original->contact_id,
            'facture_id' => $original->facture_id,
            'item_facture_id' => $item->id,
            'user_id' => auth()->id(),
        ]);

        $this->ajusterStock($original->produit_id, $original->depot_id, -$net);
    }

    /**
     * Effet cumulé des mouvements d'un article sur le stock (+ entrées, − sorties).
     */
    private function effetNet(ItemFacture $item): int
    {
        return (int) MouvementStock::where('item_facture_id', $item->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN type_mouvement = 'entree' THEN quantite WHEN type_mouvement = 'sortie' THEN -quantite ELSE 0 END), 0) AS net")
            ->toBase()
            ->first()
            ->net;
    }

    public static function typeMouvementPour(Facture $facture): ?TypeMouvement
    {
        return match ($facture->type_facture) {
            TypeFacture::Vente => TypeMouvement::Sortie,
            TypeFacture::Avoir, TypeFacture::Achat => TypeMouvement::Entree,
            default => null,
        };
    }

    private static function motif(Facture $facture): string
    {
        return match ($facture->type_facture) {
            TypeFacture::Avoir => "Retour marchandise avoir {$facture->numero_facture}"
                . ($facture->factureOrigine ? " (facture {$facture->factureOrigine->numero_facture})" : ''),
            TypeFacture::Achat => "Réception facture d'achat {$facture->numero_facture}",
            default => "Vente facture {$facture->numero_facture}",
        };
    }

    private function ajusterStock(int $produitId, int $depotId, int $delta): void
    {
        $stock = Stock::firstOrCreate(
            ['produit_id' => $produitId, 'depot_id' => $depotId],
            ['quantite' => 0]
        );
        $stock->increment('quantite', $delta);
    }
}
