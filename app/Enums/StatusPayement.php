<?php

namespace App\Enums;

enum StatusPayement: string
{
    case EnAttente = 'en_attente';
    case Valide = 'valide';
    case Paye = 'paye';
    case Annule = 'annule';
}
