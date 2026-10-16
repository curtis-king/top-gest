<?php

namespace App\Http\Controllers;

use App\Enums\StatutCertification;
use App\Enums\StatutFacture;
use App\Enums\TypeFacture;
use App\Models\Facture;
use App\Models\ItemFacture;
use App\Services\FactureWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Création d'un avoir à partir de la facture qu'il corrige :
 *   1. on saisit le numéro de la facture de vente ;
 *   2. ses lignes sont reprises (quantités et prix d'origine), on retire ou ajuste ce qui doit l'être ;
 *   3. l'avoir est créé en brouillon, client / agence / TVA repris de la facture d'origine.
 */
class FactureAvoirController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $numero = trim((string) $request->input('numero'));

        if ($numero !== '') {
            $facture = Facture::where('numero_facture', $numero)->first();

            if (!$facture) {
                return back()->withInput()->withErrors(['numero' => "Aucune facture n° {$numero} trouvée."]);
            }

            if ($motif = $facture->motifRefusAvoir()) {
                return back()->withInput()->withErrors(['numero' => $motif]);
            }

            return redirect()->route('factures.avoirs.preparer', $facture);
        }

        // Suggestions : ventes validées sans avoir, les plus récentes d'abord.
        $suggestions = Facture::with('contact:id,raison_social,nom_complet')
            ->where('type_facture', TypeFacture::Vente->value)
            ->where(fn ($q) => $q
                ->whereNotIn('statut_facture', [StatutFacture::Brouillon->value, StatutFacture::Annulee->value])
                ->orWhere('statut_certification', StatutCertification::Certifiee->value))
            ->whereDoesntHave('avoirs', fn ($q) => $q->where('type_facture', TypeFacture::Avoir->value))
            ->orderByDesc('id')
            ->limit(200)
            ->get(['id', 'numero_facture', 'raison_social', 'contact_id', 'date_facture']);

        return view('factures.avoirs.create', compact('suggestions'));
    }

    public function createDepuis(Facture $facture): View|RedirectResponse
    {
        if ($motif = $facture->motifRefusAvoir()) {
            return redirect()->route('factures.avoirs.create')->withErrors(['numero' => $motif]);
        }

        $facture->load(['items', 'contact', 'agence']);

        return view('factures.avoirs.preparer', [
            'facture' => $facture,
            'numero' => Facture::prochainNumero(),
        ]);
    }

    public function store(Request $request, Facture $facture): RedirectResponse
    {
        if ($motif = $facture->motifRefusAvoir()) {
            return redirect()->route('factures.avoirs.create')->withErrors(['numero' => $motif]);
        }

        $facture->load('items');

        // Seules les lignes cochées comptent : les autres ne sont ni validées ni reprises.
        $request->merge([
            'lignes' => array_filter((array) $request->input('lignes', []), fn ($l) => !empty($l['inclure'])),
        ]);

        $validated = $request->validate([
            'numero_facture' => ['required', 'string', 'max:255', 'unique:factures,numero_facture'],
            'date_facture' => ['required', 'date'],
            'objet' => ['nullable', 'string', 'max:500'],
            'lignes' => ['required', 'array'],
            'lignes.*.inclure' => ['nullable', 'boolean'],
            'lignes.*.description' => ['required', 'string', 'max:255'],
            'lignes.*.quantite' => ['required', 'integer', 'min:1'],
            'lignes.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
        ], [
            'lignes.required' => 'Cochez au moins une ligne à reprendre dans l\'avoir.',
            'lignes.*.description.required' => 'La désignation est obligatoire.',
            'lignes.*.quantite.min' => 'La quantité doit être au moins 1.',
        ]);

        $lignes = $this->lignesRetenues($facture, $validated['lignes']);

        $avoir = DB::transaction(function () use ($facture, $validated, $lignes) {
            // Verrou sur la facture d'origine : deux avoirs simultanés ne doivent pas passer.
            Facture::whereKey($facture->id)->lockForUpdate()->first();
            if ($motif = $facture->fresh()->motifRefusAvoir()) {
                throw ValidationException::withMessages(['lignes' => $motif]);
            }

            $avoir = Facture::create([
                'type_facture' => TypeFacture::Avoir->value,
                'numero_facture' => $validated['numero_facture'],
                'date_facture' => $validated['date_facture'],
                'statut_facture' => StatutFacture::Brouillon->value,
                'facture_origine_id' => $facture->id,
                'contact_id' => $facture->contact_id,
                'raison_social' => $facture->raison_social,
                'interlocuteur' => $facture->interlocuteur,
                'agence_id' => $facture->agence_id,
                'mode_paiement' => $facture->mode_paiement,
                'tva_active' => $facture->tva_active,
                'centimes_additionnels_actif' => $facture->centimes_additionnels_actif,
                'objet' => $validated['objet'] ?: "Avoir sur facture {$facture->numero_facture}",
            ]);

            foreach ($lignes as [$origine, $ligne]) {
                ItemFacture::create([
                    'facture_id' => $avoir->id,
                    'description' => $ligne['description'],
                    'quantite' => (int) $ligne['quantite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                    'taux_tva' => $origine->taux_tva,
                    'type_article' => $origine->type_article,
                    'produit_id' => $origine->produit_id,
                    'depot_id' => $origine->depot_id,
                ]);
            }

            // Garde-fou : les plafonds par ligne l'empêchent déjà.
            if ($avoir->depasseFactureOrigine()) {
                throw ValidationException::withMessages(['lignes' => 'Le montant de l\'avoir dépasse celui de la facture d\'origine.']);
            }

            $workflow = app(FactureWorkflow::class);
            $workflow->journaliser($avoir, 'creation', null, 'brouillon', "Avoir sur la facture {$facture->numero_facture}");
            $workflow->journaliser($facture, 'avoir', null, null, "Avoir {$avoir->numero_facture} créé");

            return $avoir;
        });

        return redirect()->route('factures.manage', $avoir)->with('success',
            "Avoir {$avoir->numero_facture} créé en brouillon sur la facture {$facture->numero_facture}. "
            . 'Vérifiez-le puis validez-le : le retour en stock se fera à la validation.'
        );
    }

    /**
     * Lignes cochées, rapprochées de la ligne d'origine, avec les plafonds :
     * on ne rend pas plus que ce qui a été facturé, ni à un prix supérieur.
     *
     * @return list<array{0: ItemFacture, 1: array}>
     */
    private function lignesRetenues(Facture $facture, array $saisies): array
    {
        $origines = $facture->items->keyBy('id');
        $retenues = [];
        $erreurs = [];

        foreach ($saisies as $itemId => $ligne) {
            if (empty($ligne['inclure'])) {
                continue;
            }

            $origine = $origines->get($itemId);
            if (!$origine) {
                $erreurs["lignes.{$itemId}.quantite"] = 'Cette ligne n\'appartient pas à la facture d\'origine.';
                continue;
            }

            if ($ligne['quantite'] > $origine->quantite) {
                $erreurs["lignes.{$itemId}.quantite"] = "Au plus {$origine->quantite} (quantité facturée).";
            }

            if ((float) $ligne['prix_unitaire'] > (float) $origine->prix_unitaire) {
                $erreurs["lignes.{$itemId}.prix_unitaire"] = 'Au plus ' . number_format($origine->prix_unitaire, 0, ',', ' ') . ' (prix facturé).';
            }

            $retenues[] = [$origine, $ligne];
        }

        if (!$retenues && !$erreurs) {
            $erreurs['lignes'] = 'Cochez au moins une ligne à reprendre dans l\'avoir.';
        }

        if ($erreurs) {
            throw ValidationException::withMessages($erreurs);
        }

        return $retenues;
    }
}
