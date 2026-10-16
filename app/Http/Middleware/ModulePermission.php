<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage : ->middleware('module:finance')
 *
 * Exige "<module>.view" pour tout accès, et la permission fine correspondant à l'action
 * du contrôleur : create*|store*|add* → create, edit|update*|save*|toggle* → update,
 * destroy*|delete* → delete. Les autres actions (index, show, search, pdf…) sont en lecture.
 *
 * Le module "contacts" est transverse : n'importe quel domaine métier donnant l'action suffit.
 */
class ModulePermission
{
    private const DOMAINES_METIER = ['rh', 'projets', 'finance', 'stock', 'archives'];

    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();
        abort_unless($user, 403);

        $action = self::actionFor($request->route()?->getActionMethod() ?? '');
        $domaines = $module === 'contacts' ? self::DOMAINES_METIER : [$module];

        $autorise = collect($domaines)->contains(function (string $domaine) use ($user, $action) {
            return $user->can("{$domaine}.view") && ($action === 'view' || $user->can("{$domaine}.{$action}"));
        });

        abort_unless($autorise, 403, 'Vous n\'avez pas la permission d\'effectuer cette action.');

        return $next($request);
    }

    public static function actionFor(string $method): string
    {
        return match (true) {
            str_starts_with($method, 'create'), str_starts_with($method, 'store'), str_starts_with($method, 'add') => 'create',
            $method === 'edit', str_starts_with($method, 'update'), str_starts_with($method, 'save'),
            str_starts_with($method, 'toggle') => 'update',
            str_starts_with($method, 'destroy'), str_starts_with($method, 'delete') => 'delete',
            default => 'view',
        };
    }
}
