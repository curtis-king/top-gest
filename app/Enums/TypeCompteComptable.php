<?php

namespace App\Enums;

enum TypeCompteComptable: string
{
    case Actif = 'actif';
    case Passif = 'passif';
    case Tresorerie = 'tresorerie';
    case Charge = 'charge';
    case Produit = 'produit';
    case Capitaux = 'capitaux';
}
