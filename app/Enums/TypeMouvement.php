<?php

namespace App\Enums;

enum TypeMouvement: string
{
    case Entree = 'entree';
    case Sortie = 'sortie';
    case Transfert = 'transfert';
    case Ajustement = 'ajustement';
}
