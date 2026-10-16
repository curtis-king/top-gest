<?php

namespace App\Enums;

enum TypeContrat: string
{
    case CDI = 'cdi';
    case CDD = 'cdd';
    case Stage = 'stage';
    case Prestation = 'prestation';
}
