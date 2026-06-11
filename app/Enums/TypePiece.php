<?php

namespace App\Enums;

enum TypePiece: string
{
    case CNI = 'cni';
    case Passeport = 'passeport';
    case Permis = 'permis';
    case Autre = 'autre';
}
