<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $entete['titre'] }}</title>
    <style>
        @page { margin: 118px 34px 56px 34px; }
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 9.5px; margin: 0; }

        header { position: fixed; top: -98px; left: 0; right: 0; height: 86px; border-bottom: 2px solid #1e3a8a; }
        header table { width: 100%; border: none; }
        header td { border: none; padding: 0; vertical-align: top; }
        .logo { max-height: 52px; max-width: 120px; }
        .societe { font-size: 13px; font-weight: bold; color: #1e3a8a; }
        .societe-info { font-size: 8.5px; color: #555; line-height: 1.4; }
        .titre { font-size: 15px; font-weight: bold; text-align: right; color: #111; }
        .sous-titre { font-size: 9px; text-align: right; color: #444; line-height: 1.5; }

        footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 24px; border-top: 1px solid #ccc;
                 font-size: 8px; color: #666; padding-top: 5px; }

        h3 { font-size: 11px; margin: 14px 0 6px; color: #1e3a8a; text-transform: uppercase; letter-spacing: .3px; }
        table.donnees { border-collapse: collapse; width: 100%; margin-bottom: 8px; }
        table.donnees th, table.donnees td { border: 1px solid #bbb; padding: 4px 6px; }
        table.donnees th { background: #e8edf7; text-align: left; font-size: 8.5px; text-transform: uppercase; }
        table.donnees td.num, table.donnees th.num { text-align: right; white-space: nowrap; }
        table.donnees tfoot td { font-weight: bold; background: #f3f4f6; }
        table.donnees tr.sous-total td { font-weight: bold; background: #f9fafb; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }

        .cartes { width: 100%; border-collapse: separate; border-spacing: 6px 0; margin: 0 -6px 12px; }
        .cartes td { border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 8px; vertical-align: top; }
        .cartes .libelle { font-size: 8px; color: #555; text-transform: uppercase; }
        .cartes .valeur { font-size: 13px; font-weight: bold; }
        .avertissement { border: 1px solid #f59e0b; background: #fffbeb; color: #92400e; padding: 6px 8px; margin-bottom: 10px; }
        .muet { color: #666; }
        .positif { color: #15803d; }
        .negatif { color: #b91c1c; }
        @yield('styles')
    </style>
</head>
<body>
    <header>
        <table>
            <tr>
                <td style="width:55%;">
                    <table>
                        <tr>
                            @if($entete['logo'])
                                <td style="width:130px;"><img src="{{ $entete['logo'] }}" class="logo"></td>
                            @endif
                            <td>
                                <div class="societe">{{ $entete['compagnie']?->name ?? 'MyGest' }}</div>
                                <div class="societe-info">
                                    @if($entete['compagnie']?->forme_juridique){{ $entete['compagnie']->forme_juridique }}<br>@endif
                                    @if($entete['compagnie']?->nui)NIU : {{ $entete['compagnie']->nui }}<br>@endif
                                    @if($entete['compagnie']?->rccm)RCCM : {{ $entete['compagnie']->rccm }}@endif
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:45%;">
                    <div class="titre">{{ $entete['titre'] }}</div>
                    <div class="sous-titre">
                        @if($entete['periode']){{ $entete['periode'] }}<br>@endif
                        {{ $entete['agence'] ? 'Agence : ' . $entete['agence'] : 'Toutes agences' }}
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        Édité le {{ $entete['edition']->format('d/m/Y à H:i') }}@if($entete['auteur']) par {{ $entete['auteur'] }}@endif — Montants en FCFA
    </footer>

    <main>
        @yield('contenu')
    </main>
</body>
</html>
