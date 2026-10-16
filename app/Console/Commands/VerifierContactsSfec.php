<?php

namespace App\Console\Commands;

use App\Enums\TypeContact;
use App\Models\Contact;
use Illuminate\Console\Command;

class VerifierContactsSfec extends Command
{
    protected $signature = 'sfec:verifier-contacts';

    protected $description = 'Liste les contacts qui bloqueraient la certification SFEC d\'une facture';

    public function handle(): int
    {
        // Clients, plus tout contact déjà utilisé sur une vente ou un avoir.
        $contacts = Contact::query()
            ->where('type_contact', TypeContact::Client->value)
            ->orWhereIn('id', fn ($q) => $q->select('contact_id')->from('factures')
                ->whereIn('type_facture', ['vente', 'avoir'])->whereNotNull('contact_id'))
            ->orderBy('raison_social')
            ->get();

        $incomplets = $contacts->map(fn (Contact $c) => [$c, $c->manquesSfec()])->filter(fn ($l) => $l[1] !== []);

        $this->info("{$contacts->count()} contact(s) vérifié(s), {$incomplets->count()} incomplet(s) pour le SFEC.");

        if ($incomplets->isNotEmpty()) {
            $this->table(
                ['#', 'Contact', 'Type SFEC', 'À compléter'],
                $incomplets->map(fn ($l) => [
                    $l[0]->id,
                    $l[0]->raison_social ?: $l[0]->nom_complet ?: '—',
                    $l[0]->typeSfec() . ($l[0]->type_client_sfec ? '' : ' (déduit)'),
                    implode("\n", $l[1]),
                ])->all()
            );
        }

        return $incomplets->isEmpty() ? self::SUCCESS : self::FAILURE;
    }
}
