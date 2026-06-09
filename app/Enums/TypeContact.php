<?php

namespace App\Enums;

enum TypeContact: string
{
    case Fournisseur = 'fournisseur';
    case Client = 'client';
    case Autre = 'autre';
}
