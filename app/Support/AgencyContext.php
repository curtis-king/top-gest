<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Détermine le périmètre "agence" de l'utilisateur connecté.
 *
 * - Pas d'utilisateur (console, queue, seeders) ou scope "dg" : accès à toutes les agences.
 * - Sinon : l'agence de sa fiche employé, à défaut celle de son compte utilisateur.
 * - Utilisateur non-DG sans agence : aucun accès aux données cloisonnées (fail closed).
 */
class AgencyContext
{
    /** @var array<int, int|null> */
    private static array $cache = [];

    public static function isRestricted(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->scope !== 'dg';
    }

    public static function agenceId(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if (!array_key_exists($user->id, self::$cache)) {
            // Requête brute : Employee est lui-même cloisonné, passer par Eloquent bouclerait.
            $agenceId = DB::table('employees')->where('user_id', $user->id)->value('agence_id')
                ?? $user->agence_id;

            self::$cache[$user->id] = $agenceId !== null ? (int) $agenceId : null;
        }

        return self::$cache[$user->id];
    }

    public static function flush(): void
    {
        self::$cache = [];
    }
}
