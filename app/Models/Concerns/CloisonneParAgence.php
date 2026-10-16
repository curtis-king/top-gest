<?php

namespace App\Models\Concerns;

use App\Support\AgencyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Cloisonne un modèle par agence pour les utilisateurs non-DG.
 *
 * Le modèle porte soit une colonne agence_id, soit hérite de l'agence d'un parent :
 *   protected string $agenceParent = 'employee';
 *
 * Les clés étrangères qui doivent pointer vers des données de la même agence :
 *   protected array $relationsCloisonnees = ['contact', 'depot'];
 *
 * Effets : lectures filtrées (route model binding compris → 404 hors périmètre),
 * agence_id imposé à l'écriture, refus (403) de lier une donnée d'une autre agence.
 */
trait CloisonneParAgence
{
    public static function bootCloisonneParAgence(): void
    {
        static::addGlobalScope('agence', function (Builder $query) {
            if (!AgencyContext::isRestricted()) {
                return;
            }

            $agenceId = AgencyContext::agenceId();
            if ($agenceId === null) {
                $query->whereRaw('1 = 0');
                return;
            }

            $model = $query->getModel();
            if ($parent = $model->agenceParent()) {
                $query->whereHas($parent);
            } else {
                $query->where($model->qualifyColumn('agence_id'), $agenceId);
            }
        });

        static::saving(function (Model $model) {
            if (!AgencyContext::isRestricted()) {
                return;
            }

            $agenceId = AgencyContext::agenceId();
            abort_if($agenceId === null, 403, 'Votre compte n\'est rattaché à aucune agence.');

            $relations = $model->relationsCloisonnees();
            if ($parent = $model->agenceParent()) {
                $relations[] = $parent;
            } else {
                $model->agence_id = $agenceId;
            }

            foreach (array_unique($relations) as $relation) {
                $foreignKey = $model->{$relation}()->getForeignKeyName();
                $changed = !$model->exists || $model->isDirty($foreignKey);

                if ($model->{$foreignKey} !== null && $changed && !$model->{$relation}()->exists()) {
                    abort(403, 'Cette donnée appartient à une autre agence.');
                }
            }
        });
    }

    public function agenceParent(): ?string
    {
        return property_exists($this, 'agenceParent') ? $this->agenceParent : null;
    }

    public function relationsCloisonnees(): array
    {
        return property_exists($this, 'relationsCloisonnees') ? $this->relationsCloisonnees : [];
    }
}
