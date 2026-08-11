<?php

namespace App\Services;

use App\Enums\TypeMouvement;
use App\Models\ItemFacture;
use App\Models\MouvementStock;
use App\Models\Stock;

class StockMovementService
{
    public function sortirPourArticle(ItemFacture $item): ?MouvementStock
    {
        if (!$item->produit_id || !$item->depot_id) {
            return null;
        }

        if (MouvementStock::where('item_facture_id', $item->id)->exists()) {
            return null;
        }

        $facture = $item->facture;

        $mouvement = MouvementStock::create([
            'type_mouvement' => TypeMouvement::Sortie->value,
            'quantite' => $item->quantite,
            'date_mouvement' => now(),
            'motif' => "Vente facture {$facture->numero_facture}",
            'produit_id' => $item->produit_id,
            'depot_id' => $item->depot_id,
            'contact_id' => $facture->contact_id,
            'facture_id' => $facture->id,
            'item_facture_id' => $item->id,
            'user_id' => auth()->id(),
        ]);

        $this->ajusterStock($item->produit_id, $item->depot_id, -$item->quantite);

        return $mouvement;
    }

    public function sortirPourFacture(\App\Models\Facture $facture): void
    {
        $facture->loadMissing('items');

        foreach ($facture->items as $item) {
            $this->sortirPourArticle($item);
        }
    }

    public function annulerPourArticle(ItemFacture $item): void
    {
        $original = MouvementStock::where('item_facture_id', $item->id)
            ->where('type_mouvement', TypeMouvement::Sortie->value)
            ->first();

        if (!$original) {
            return;
        }

        MouvementStock::create([
            'type_mouvement' => TypeMouvement::Entree->value,
            'quantite' => $original->quantite,
            'date_mouvement' => now(),
            'motif' => "Annulation sortie suite suppression d'article (facture {$original->facture?->numero_facture})",
            'produit_id' => $original->produit_id,
            'depot_id' => $original->depot_id,
            'contact_id' => $original->contact_id,
            'facture_id' => $original->facture_id,
            'user_id' => auth()->id(),
        ]);

        $this->ajusterStock($original->produit_id, $original->depot_id, $original->quantite);
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
