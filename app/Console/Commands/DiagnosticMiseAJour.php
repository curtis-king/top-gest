<?php

namespace App\Console\Commands;

use App\Enums\TypeContact;
use App\Models\Contact;
use App\Models\Facture;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Diagnostic en lecture seule, à lancer après la mise à jour : repère les comptes et les
 * données que les nouvelles règles (permissions fines, cloisonnement par agence, cycle de vie
 * des factures, exigences SFEC) vont affecter. Ne modifie rien.
 */
class DiagnosticMiseAJour extends Command
{
    protected $signature = 'mygest:diagnostic';

    protected $description = 'Repère les comptes et données à ajuster après la mise à jour (lecture seule)';

    private int $alertes = 0;

    public function handle(): int
    {
        $this->info('Diagnostic MyGest — aucune donnée n\'est modifiée.');

        // 1. Administration des comptes
        $admins = Role::where('name', 'admin-systeme')->exists() ? User::role('admin-systeme')->count() : 0;
        $this->verifier($admins > 0, "Administrateurs système : {$admins}",
            'Aucun compte n\'a le rôle admin-systeme : plus personne ne pourra créer ni modifier les utilisateurs.');

        // 2. Comptes sans rôle : plus aucun accès (les permissions fines sont désormais appliquées)
        $sansRole = User::doesntHave('roles')->get(['id', 'name', 'email']);
        $this->verifier($sansRole->isEmpty(), 'Tous les comptes ont au moins un rôle',
            "{$sansRole->count()} compte(s) sans rôle : ils ne peuvent plus rien consulter. Attribuez-leur un rôle (Système → Utilisateurs).",
            $sansRole->map(fn ($u) => [$u->id, $u->name, $u->email])->all(), ['#', 'Nom', 'Email']);

        // 3. Comptes non-DG sans agence : n'accèdent plus aux données cloisonnées
        $avecAgence = DB::table('employees')->whereNotNull('agence_id')->whereNotNull('user_id')->pluck('user_id');
        $sansAgence = User::where('scope', '!=', 'dg')->whereNull('agence_id')->whereNotIn('id', $avecAgence)->get(['id', 'name', 'email', 'scope']);
        $this->verifier($sansAgence->isEmpty(), 'Tous les comptes non-DG sont rattachés à une agence',
            "{$sansAgence->count()} compte(s) non-DG sans agence : ils ne voient plus factures, contacts, stocks, employés… "
            . 'Rattachez-les à une agence (fiche utilisateur ou fiche employé), ou passez-les en scope DG.',
            $sansAgence->map(fn ($u) => [$u->id, $u->name, $u->email, $u->scope])->all(), ['#', 'Nom', 'Email', 'Scope']);

        // 4. Factures sans agence : visibles uniquement par les comptes DG
        $facturesSansAgence = Facture::whereNull('agence_id')->count();
        $this->verifier($facturesSansAgence === 0, 'Toutes les factures ont une agence',
            "{$facturesSansAgence} facture(s) sans agence : seuls les comptes DG les voient.");

        // 5. Factures certifiées restées en brouillon (état devenu impossible)
        $certifieesBrouillon = Facture::where('statut_certification', 'certifiee')->where('statut_facture', 'brouillon')->pluck('numero_facture');
        $this->verifier($certifieesBrouillon->isEmpty(), 'Aucune facture certifiée en brouillon',
            'Factures certifiées mais encore en brouillon (à valider depuis leur page de gestion) : ' . $certifieesBrouillon->implode(', '));

        // 6. Factures déclarées payées sans paiement enregistré (historique antérieur)
        $payeesSansPaiement = Facture::whereIn('statut_facture', ['payee', 'partielle'])->doesntHave('paiements')->count();
        $this->verifier($payeesSansPaiement === 0, 'Les factures payées ont leurs paiements',
            "{$payeesSansPaiement} facture(s) « payée » ou « partielle » sans paiement enregistré (antérieures au suivi des paiements). "
            . 'Leur statut reste tel quel ; il sera recalculé au premier paiement saisi.', [], [], 'info');

        // 7. Clients incomplets pour le SFEC
        $clientsIncomplets = Contact::where('type_contact', TypeContact::Client->value)->get()->reject->estPretSfec()->count();
        $this->verifier($clientsIncomplets === 0, 'Fiches clients complètes pour le SFEC',
            "{$clientsIncomplets} client(s) incomplet(s) pour le SFEC : détail avec « php artisan sfec:verifier-contacts ».");

        $this->newLine();
        $this->alertes === 0
            ? $this->info('Rien à ajuster.')
            : $this->warn("{$this->alertes} point(s) à regarder ci-dessus.");

        return self::SUCCESS;
    }

    private function verifier(bool $ok, string $messageOk, string $messageKo, array $lignes = [], array $entetes = [], string $niveau = 'warn'): void
    {
        if ($ok) {
            $this->line("  <fg=green>✓</> {$messageOk}");
            return;
        }

        if ($niveau === 'warn') {
            $this->alertes++;
            $this->line("  <fg=yellow>⚠</> {$messageKo}");
        } else {
            $this->line("  <fg=cyan>ℹ</> {$messageKo}");
        }

        if ($lignes) {
            $this->table($entetes, $lignes);
        }
    }
}
