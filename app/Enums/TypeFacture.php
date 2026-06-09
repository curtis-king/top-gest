<?php

namespace App\Enums;

enum TypeFacture: string
{
    case Vente = 'vente';
    case Achat = 'achat';
    case Avoir = 'avoir';
    case Proforma = 'proforma';
}
