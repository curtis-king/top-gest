@php
$cr = Route::currentRouteName() ?? '';

// Domaine → liste des préfixes de route qui lui appartiennent
$domainPrefixes = [
    'rh'      => ['employees','dossiers-employees','conges','retenus','primes',
                  'payements-employees','compagnies','agences','fonctions'],
    'projets' => ['projects','taches-projects','affectation-taches'],
    'finance' => ['factures','contacts','banques','livrets-bancaires','depenses','categories-depenses',
                  'comptes-comptables','journaux-comptables','ecritures-comptables','comptabilite'],
    'stock'   => ['stocks','produits','mouvements-stocks','depots','categories-produits'],
    'archives'=> ['documents','categories-documents'],
    'systeme' => ['users'],
];

$currentDomain = null;
foreach ($domainPrefixes as $domain => $prefixes) {
    foreach ($prefixes as $p) {
        if (str_starts_with($cr, $p)) { $currentDomain = $domain; break 2; }
    }
}

// Tabs par domaine  [label, route, préfixe actif]
$tabs = [
    'rh' => [
        ['Employés',   'employees.index',           'employees'],
        ['Dossiers',   'dossiers-employees.index',  'dossiers-employees'],
        ['Congés',     'conges.index',              'conges'],
        ['Retenus',    'retenus.index',             'retenus'],
        ['Primes',     'primes.index',              'primes'],
        ['Paiements',  'payements-employees.index', 'payements-employees'],
        ['Compagnies', 'compagnies.index',          'compagnies'],
        ['Agences',    'agences.index',             'agences'],
        ['Fonctions',  'fonctions.index',           'fonctions'],
    ],
    'projets' => [
        ['Projets',      'projects.index',           'projects'],
        ['Tâches',       'taches-projects.index',    'taches-projects'],
        ['Affectations', 'affectation-taches.index', 'affectation-taches'],
    ],
    'finance' => [
        ['Factures',    'factures.index',              'factures'],
        ['Contacts',    'contacts.index',              'contacts'],
        ['Banques',     'banques.index',               'banques'],
        ['Livrets',     'livrets-bancaires.index',      'livrets-bancaires'],
        ['Dépenses',    'depenses.index',               'depenses'],
        ['Catégories dépenses', 'categories-depenses.index', 'categories-depenses'],
        ['Plan comptable', 'comptes-comptables.index',  'comptes-comptables'],
        ['Journaux',    'journaux-comptables.index',    'journaux-comptables'],
        ['Écritures',   'ecritures-comptables.index',   'ecritures-comptables'],
        ['Grand livre', 'comptabilite.grand-livre',     'comptabilite.grand-livre'],
        ['Balance',     'comptabilite.balance',         'comptabilite.balance'],
        ['Bilan',       'comptabilite.bilan',           'comptabilite.bilan'],
        ['Résultat',    'comptabilite.resultat',        'comptabilite.resultat'],
    ],
    'stock' => [
        ['Tableau de bord',  'stocks.dashboard',          'stocks.dashboard'],
        ['État des stocks',  'stocks.index',              'stocks.index'],
        ['Produits',         'produits.index',            'produits'],
        ['Mouvements',       'mouvements-stocks.index',   'mouvements-stocks'],
        ['Dépôts',           'depots.index',              'depots'],
        ['Catégories',       'categories-produits.index', 'categories-produits'],
    ],
    'archives' => [
        ['Documents',  'documents.index',           'documents'],
        ['Catégories', 'categories-documents.index','categories-documents'],
    ],
    'systeme' => [
        ['Utilisateurs', 'users.index', 'users'],
    ],
];
@endphp

@if($currentDomain && isset($tabs[$currentDomain]))
<div style="display:flex;gap:0;border-bottom:1px solid rgba(255,255,255,.06);margin-bottom:28px;overflow-x:auto;scrollbar-width:none;">
    @foreach($tabs[$currentDomain] as [$label, $routeName, $prefix])
    @php $isActive = str_starts_with($cr, $prefix); @endphp
    <a href="{{ route($routeName) }}"
       style="display:inline-flex;align-items:center;padding:10px 16px;font-size:13px;font-weight:500;white-space:nowrap;text-decoration:none;border-bottom:2px solid {{ $isActive ? '#6366f1' : 'transparent' }};color:{{ $isActive ? '#818cf8' : 'rgba(255,255,255,.4)' }};transition:all .15s;flex-shrink:0;"
       onmouseover="if(!{{ $isActive ? 'true' : 'false' }}) { this.style.color='rgba(255,255,255,.75)'; this.style.borderBottomColor='rgba(255,255,255,.15)'; }"
       onmouseout="if(!{{ $isActive ? 'true' : 'false' }}) { this.style.color='rgba(255,255,255,.4)'; this.style.borderBottomColor='transparent'; }">
        {{ $label }}
    </a>
    @endforeach
</div>
@endif
