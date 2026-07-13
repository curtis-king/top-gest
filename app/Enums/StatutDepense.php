<?php

namespace App\Enums;

enum StatutDepense: string
{
    case EnAttente = 'en_attente';
    case Payee = 'payee';
    case Annulee = 'annulee';
}
