<?php

namespace App\Enums;

enum ModePaiementDepense: string
{
    case Banque = 'banque';
    case Caisse = 'caisse';
    case ACrediter = 'a_crediter';
}
