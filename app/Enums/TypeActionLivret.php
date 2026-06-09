<?php

namespace App\Enums;

enum TypeActionLivret: string
{
    case Depot = 'depot';
    case Retrait = 'retrait';
    case Virement = 'virement';
    case Frais = 'frais';
    case Interet = 'interet';
    case Autre = 'autre';
}
