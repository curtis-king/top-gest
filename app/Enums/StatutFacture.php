<?php

namespace App\Enums;

enum StatutFacture: string
{
    case Brouillon = 'brouillon';
    case Impayee = 'impayee';
    case Partielle = 'partielle';
    case Payee = 'payee';
    case Annulee = 'annulee';
}
