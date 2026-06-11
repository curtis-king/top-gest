<?php

namespace App\Enums;

enum TypeConge: string
{
    case Annuel = 'annuel';
    case Maladie = 'maladie';
    case Maternite = 'maternite';
    case Exceptionnel = 'exceptionnel';
    case SansSolde = 'sans_solde';
}
