<?php

namespace App\Enums;

enum StatusMatrimonial: string
{
    case Celibataire = 'celibataire';
    case Marie = 'marie';
    case Divorce = 'divorce';
    case Veuf = 'veuf';
}
